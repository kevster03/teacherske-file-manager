<?php
/**
 * Frontend Functions
 * 
 * Handles template loading, asset enqueuing, sidebar registration, and AJAX for downloads
 */

if (!defined('ABSPATH')) exit;

/**
 * Disable Block Editor for Documents (Speed Optimization)
 */
add_filter('use_block_editor_for_post_type', function($use, $post_type) {
    if ($post_type === 'teacher_document') {
        return false;
    }
    return $use;
}, 10, 2);

/**
 * Load Custom Template
 */
function tkm_load_template($single) {
    global $post;
    
    if ($post && $post->post_type === 'teacher_document') {
        $template = TKM_DIR . 'templates/single-teacher_document.php';
        
        if (file_exists($template)) {
            return $template;
        }
    }
    
    return $single;
}
add_filter('single_template', 'tkm_load_template');

/**
 * Register Sidebar Widget Area
 */
function tkm_register_sidebars() {
    register_sidebar(array(
        'name' => __('Document Sidebar', 'teacherske'),
        'id' => 'tkm_document_sidebar',
        'description' => __('Appears on the right side of single document pages. Add widgets for tips, events, or sponsored content.', 'teacherske'),
        'before_widget' => '<div class="tkm-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'tkm_register_sidebars');

/**
 * Track Page View
 */
function tkm_track_page_view() {
    if (!is_singular('teacher_document')) return;

    $post_id = get_the_ID();
    if (!$post_id) return;

    // Initialize view tracker
    $tracker = new TKM_View_Tracker();
    $tracker->track_view($post_id);
}
add_action('wp_head', 'tkm_track_page_view', 1);

/**
 * Enqueue Frontend Assets
 */
function tkm_frontend_assets() {
    if (!is_singular('teacher_document')) return;
    
    // Frontend CSS (minimal - most CSS is inline for speed)
    wp_enqueue_style(
        'tkm-frontend-css',
        TKM_URL . 'assets/css/frontend.css',
        array(),
        TKM_VERSION
    );
    
    // Frontend JavaScript
    wp_enqueue_script(
        'tkm-frontend-js',
        TKM_URL . 'assets/js/frontend.js',
        array(),
        TKM_VERSION,
        true
    );
    
    // Pass settings to JavaScript
    $settings = array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'postId' => get_the_ID(),
        'countdown' => intval(tkm_get_setting('countdown_duration', 10)),
        'tracking' => tkm_get_setting('enable_tracking', 'yes'),
        'loaderType' => tkm_get_setting('loader_type', 'bar'),
        'nonce' => wp_create_nonce('tkm_download_' . get_the_ID()),
        'primaryColor' => tkm_get_setting('primary_color', '#c92651'),
        'successColor' => '#28a745',
    );
    
    wp_localize_script('tkm-frontend-js', 'tkmSettings', $settings);
}
add_action('wp_enqueue_scripts', 'tkm_frontend_assets');

/**
 * AJAX: Track Download
 */
function tkm_ajax_track_download() {
    // Verify request
    if (!isset($_POST['post_id'])) {
        wp_send_json_error(array('message' => __('Invalid request', 'teacherske')));
    }

    $post_id = intval($_POST['post_id']);

    // Verify nonce (more lenient for logged-out users)
    if (isset($_POST['nonce'])) {
        $nonce_verified = wp_verify_nonce($_POST['nonce'], 'tkm_download_' . $post_id);

        // For logged-in users, require valid nonce
        if (is_user_logged_in() && !$nonce_verified) {
            wp_send_json_error(array('message' => __('Security check failed', 'teacherske')));
        }

        // For logged-out users, allow if nonce check fails (less strict)
        // This prevents downloads from being blocked for guests
    }

    // Initialize tracker
    $tracker = new TKM_Download_Tracker();

    // Check if tracking is enabled
    if (tkm_get_setting('enable_tracking', 'yes') !== 'yes') {
        wp_send_json_success(array(
            'counted' => false,
            'total' => $tracker->get_download_count($post_id),
            'message' => __('Tracking disabled', 'teacherske')
        ));
        return;
    }

    // Track the download
    $result = $tracker->track_download($post_id);

    // Always return current count
    $current_count = $tracker->get_download_count($post_id);

    if ($result) {
        wp_send_json_success(array(
            'counted' => true,
            'total' => $current_count,
            'message' => __('Download tracked', 'teacherske')
        ));
    } else {
        wp_send_json_success(array(
            'counted' => false,
            'total' => $current_count,
            'message' => __('Already counted from this IP recently', 'teacherske')
        ));
    }
}
add_action('wp_ajax_tkm_track_download', 'tkm_ajax_track_download');
add_action('wp_ajax_nopriv_tkm_track_download', 'tkm_ajax_track_download');

/**
 * Add Schema.org Markup to Head
 */
function tkm_add_schema_markup() {
    if (!is_singular('teacher_document')) return;
    if (tkm_get_setting('enable_schema', 'yes') !== 'yes') return;
    
    global $post;
    
    $file_url = get_post_meta($post->ID, '_tkm_file', true);
    $file_ext = get_post_meta($post->ID, '_tkm_file_ext', true);
    $description = get_post_meta($post->ID, '_tkm_description', true);
    $grade = get_post_meta($post->ID, '_tkm_grade', true);
    $level = get_post_meta($post->ID, '_tkm_level', true);
    $version = get_post_meta($post->ID, '_tkm_version', true);
    $subject = get_post_meta($post->ID, '_tkm_subject', true);
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'DigitalDocument',
        'name' => get_the_title(),
        'description' => $description ?: get_the_excerpt(),
        'author' => array(
            '@type' => 'Person',
            'name' => get_the_author()
        ),
        'datePublished' => get_the_date('c'),
        'dateModified' => get_the_modified_date('c'),
        'publisher' => array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url()
        ),
        'about' => $subject,
        'educationalLevel' => $grade,
        'inLanguage' => get_bloginfo('language'),
        'fileFormat' => $file_ext ? 'application/' . $file_ext : '',
        'contentUrl' => $file_url,
        'url' => get_permalink(),
        'keywords' => array_filter(array($grade, $level, $version, $subject))
    );
    
    // Add featured image
    if (has_post_thumbnail()) {
        $schema['image'] = get_the_post_thumbnail_url($post->ID, 'full');
    }
    
    // Add encoding format
    if ($file_ext) {
        $schema['encodingFormat'] = $file_ext;
    }
    
    ?>
    <script type="application/ld+json">
    <?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
    <?php
}
add_action('wp_head', 'tkm_add_schema_markup');

/**
 * Add Meta Tags for SEO
 */
function tkm_add_meta_tags() {
    if (!is_singular('teacher_document')) return;
    
    global $post;
    
    $description = get_post_meta($post->ID, '_tkm_description', true);
    $description = $description ?: get_the_excerpt();
    $description = wp_strip_all_tags($description);
    $description = tkm_truncate_text($description, 160);
    
    $grade = get_post_meta($post->ID, '_tkm_grade', true);
    $subject = get_post_meta($post->ID, '_tkm_subject', true);
    
    $keywords = array_filter(array(get_the_title(), $grade, $subject));
    
    ?>
    <meta name="description" content="<?php echo esc_attr($description); ?>" />
    <meta name="keywords" content="<?php echo esc_attr(implode(', ', $keywords)); ?>" />
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo esc_attr(get_the_title()); ?>" />
    <meta property="og:description" content="<?php echo esc_attr($description); ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>" />
    <?php if (has_post_thumbnail()): ?>
    <meta property="og:image" content="<?php echo esc_url(get_the_post_thumbnail_url($post->ID, 'large')); ?>" />
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo esc_attr(get_the_title()); ?>" />
    <meta name="twitter:description" content="<?php echo esc_attr($description); ?>" />
    <?php if (has_post_thumbnail()): ?>
    <meta name="twitter:image" content="<?php echo esc_url(get_the_post_thumbnail_url($post->ID, 'large')); ?>" />
    <?php endif; ?>
    <?php
}
add_action('wp_head', 'tkm_add_meta_tags');

/**
 * Shortcode: Display Single Document
 */
function tkm_document_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0
    ), $atts);
    
    $post_id = intval($atts['id']);
    
    if (!$post_id || get_post_type($post_id) !== 'teacher_document') {
        return '<p>' . __('Document not found', 'teacherske') . '</p>';
    }
    
    ob_start();
    
    $post = get_post($post_id);
    setup_postdata($post);
    
    ?>
    <div class="tkm-shortcode-document" style="border:1px solid #e0c8ff;border-radius:8px;padding:15px;margin:10px 0">
        <h3 style="margin-top:0"><a href="<?php echo esc_url(get_permalink($post_id)); ?>" style="color:#c92651;text-decoration:none"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
        <?php if (has_post_thumbnail($post_id)): ?>
            <img src="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'medium')); ?>" alt="" style="max-width:200px;border-radius:8px;margin:10px 0" />
        <?php endif; ?>
        <p><?php echo esc_html(tkm_truncate_text(get_post_meta($post_id, '_tkm_description', true), 100)); ?></p>
        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="button" style="background:#c92651;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;display:inline-block"><?php _e('View Document', 'teacherske'); ?></a>
    </div>
    <?php
    
    wp_reset_postdata();
    
    return ob_get_clean();
}
add_shortcode('teacher_document', 'tkm_document_shortcode');

/**
 * Custom Body Class
 */
function tkm_body_class($classes) {
    if (is_singular('teacher_document')) {
        $classes[] = 'tkm-document-page';
        $classes[] = 'tkm-layout-' . tkm_get_setting('layout_density', 'normal');
    }
    return $classes;
}
add_filter('body_class', 'tkm_body_class');
