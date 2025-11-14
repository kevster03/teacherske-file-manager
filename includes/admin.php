<?php
/**
 * Admin Functions
 * 
 * Handles admin-specific functionality like quick edit, row actions,
 * dashboard widget, and keyboard shortcuts
 * 
 * NOTE: Admin columns are now in cpt.php to avoid conflicts
 */

if (!defined('ABSPATH')) exit;

/**
 * Add Quick Edit Fields
 */
function tkm_quick_edit_fields($column_name, $post_type) {
    if ($post_type !== 'teacher_document') return;
    
    if ($column_name === 'tkm_grade') {
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php _e('Level', 'teacherske'); ?></span>
                    <select name="tkm_level" id="tkm_quick_level">
                        <option value=""><?php _e('— No Change —', 'teacherske'); ?></option>
                        <?php
                        $levels = tkm_get_levels();
                        foreach ($levels as $key => $data) {
                            echo '<option value="' . esc_attr($key) . '">' . esc_html($data['label']) . '</option>';
                        }
                        ?>
                    </select>
                </label>
                
                <label>
                    <span class="title"><?php _e('Grade', 'teacherske'); ?></span>
                    <input type="text" name="tkm_grade" value="" placeholder="<?php esc_attr_e('e.g., Grade 5', 'teacherske'); ?>" />
                </label>
                
                <label>
                    <span class="title"><?php _e('Version', 'teacherske'); ?></span>
                    <input type="text" name="tkm_version" value="" placeholder="<?php esc_attr_e('e.g., 2026 Edition', 'teacherske'); ?>" />
                </label>
                
                <label>
                    <span class="title"><?php _e('Subject', 'teacherske'); ?></span>
                    <input type="text" name="tkm_subject" value="" placeholder="<?php esc_attr_e('e.g., Mathematics', 'teacherske'); ?>" />
                </label>
            </div>
        </fieldset>
        <?php
    }
}
add_action('quick_edit_custom_box', 'tkm_quick_edit_fields', 10, 2);

/**
 * Save Quick Edit Data
 */
function tkm_save_quick_edit($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (get_post_type($post_id) !== 'teacher_document') return;
    
    if (isset($_POST['tkm_level']) && !empty($_POST['tkm_level'])) {
        update_post_meta($post_id, '_tkm_level', sanitize_text_field($_POST['tkm_level']));
    }
    
    if (isset($_POST['tkm_grade']) && !empty($_POST['tkm_grade'])) {
        update_post_meta($post_id, '_tkm_grade', sanitize_text_field($_POST['tkm_grade']));
    }
    
    if (isset($_POST['tkm_version']) && !empty($_POST['tkm_version'])) {
        update_post_meta($post_id, '_tkm_version', sanitize_text_field($_POST['tkm_version']));
    }
    
    if (isset($_POST['tkm_subject']) && !empty($_POST['tkm_subject'])) {
        update_post_meta($post_id, '_tkm_subject', sanitize_text_field($_POST['tkm_subject']));
    }
}
add_action('save_post', 'tkm_save_quick_edit');

/**
 * Add Row Actions
 */
function tkm_row_actions($actions, $post) {
    if ($post->post_type !== 'teacher_document') return $actions;

    // Add "Duplicate" link
    $duplicate_url = wp_nonce_url(
        add_query_arg(array(
            'action' => 'tkm_duplicate_document',
            'post' => $post->ID
        ), admin_url('admin.php')),
        'tkm_duplicate_' . $post->ID
    );

    $actions['duplicate'] = sprintf(
        '<a href="%s" style="color:#c92651;">📄 Duplicate</a>',
        esc_url($duplicate_url)
    );

    // Add "View Stats" link
    if (tkm_is_tracking_enabled()) {
        $downloads = get_post_meta($post->ID, '_tkm_download_count', true);
        $actions['stats'] = sprintf(
            '<span style="color:#2271b1;">📊 %d %s</span>',
            intval($downloads),
            _n('download', 'downloads', intval($downloads), 'teacherske')
        );
    }

    // Add "Copy Shortcode" link
    $actions['shortcode'] = sprintf(
        '<a href="#" onclick="navigator.clipboard.writeText(\'[teacher_document id=%d]\');alert(\'Shortcode copied!\');return false;" style="color:#c92651;">📋 Copy Shortcode</a>',
        $post->ID
    );

    return $actions;
}
add_filter('post_row_actions', 'tkm_row_actions', 10, 2);

/**
 * Handle Document Duplication
 */
function tkm_duplicate_document() {
    // Verify we have a post ID
    if (empty($_GET['post'])) {
        wp_die(__('No document to duplicate has been specified.', 'teacherske'));
    }

    $post_id = absint($_GET['post']);

    // Verify nonce
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'tkm_duplicate_' . $post_id)) {
        wp_die(__('Security check failed.', 'teacherske'));
    }

    // Check permissions
    if (!current_user_can('edit_posts')) {
        wp_die(__('You do not have permission to duplicate documents.', 'teacherske'));
    }

    // Get original post
    $original_post = get_post($post_id);

    if (!$original_post || $original_post->post_type !== 'teacher_document') {
        wp_die(__('Document not found or invalid type.', 'teacherske'));
    }

    // Create duplicate post
    $new_post = array(
        'post_title' => $original_post->post_title . ' (Copy)',
        'post_content' => $original_post->post_content,
        'post_excerpt' => $original_post->post_excerpt,
        'post_status' => 'draft', // Always create as draft
        'post_type' => 'teacher_document',
        'post_author' => get_current_user_id(),
        'menu_order' => $original_post->menu_order,
        'comment_status' => $original_post->comment_status,
        'ping_status' => $original_post->ping_status
    );

    // Insert the new post
    $new_post_id = wp_insert_post($new_post);

    if (is_wp_error($new_post_id)) {
        wp_die(__('Failed to duplicate document.', 'teacherske'));
    }

    // Copy all meta data
    $meta_keys = array(
        '_tkm_file',
        '_tkm_level',
        '_tkm_grade',
        '_tkm_subject',
        '_tkm_version',
        '_tkm_description',
        '_tkm_file_ext',
        '_tkm_file_size'
    );

    foreach ($meta_keys as $meta_key) {
        $meta_value = get_post_meta($post_id, $meta_key, true);
        if (!empty($meta_value)) {
            update_post_meta($new_post_id, $meta_key, $meta_value);
        }
    }

    // Reset download count to 0 (new document)
    update_post_meta($new_post_id, '_tkm_download_count', 0);

    // Copy taxonomies (categories, subjects, etc.)
    $taxonomies = get_object_taxonomies('teacher_document');
    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));
        if (!empty($terms) && !is_wp_error($terms)) {
            wp_set_object_terms($new_post_id, $terms, $taxonomy);
        }
    }

    // Copy featured image
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        set_post_thumbnail($new_post_id, $thumbnail_id);
    }

    // Redirect to edit the new document
    wp_redirect(add_query_arg(array(
        'post' => $new_post_id,
        'action' => 'edit',
        'duplicated' => 1
    ), admin_url('post.php')));
    exit;
}
add_action('admin_action_tkm_duplicate_document', 'tkm_duplicate_document');

/**
 * Show success notice after duplication
 */
function tkm_duplicate_success_notice() {
    if (isset($_GET['duplicated']) && $_GET['duplicated'] == 1) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Document duplicated successfully. You are now editing the copy.', 'teacherske'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'tkm_duplicate_success_notice');

/**
 * Admin List Table CSS
 */
function tkm_admin_list_css() {
    $screen = get_current_screen();
    
    if ($screen && $screen->post_type === 'teacher_document' && $screen->base === 'edit') {
        ?>
        <style>
            .wp-list-table .column-tkm_file_type { width: 80px; text-align: center; }
            .wp-list-table .column-tkm_level { width: 150px; }
            .wp-list-table .column-tkm_grade { width: 100px; }
            .wp-list-table .column-tkm_subject { width: 120px; }
            .wp-list-table .column-tkm_downloads { width: 90px; text-align: center; }
            .tablenav .actions select { max-width: 180px; }
        </style>
        <?php
    }
}
add_action('admin_head', 'tkm_admin_list_css');

/**
 * Dashboard Widget - Quick Stats
 */
function tkm_dashboard_widget() {
    wp_add_dashboard_widget(
        'tkm_stats_widget',
        '<span class="dashicons dashicons-media-document" style="color:#c92651;"></span> ' . __('TeachersKE Documents', 'teacherske'),
        'tkm_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'tkm_dashboard_widget');

function tkm_dashboard_widget_content() {
    $total = wp_count_posts('teacher_document')->publish;
    
    global $wpdb;
    $total_downloads = $wpdb->get_var(
        "SELECT SUM(CAST(meta_value AS UNSIGNED)) 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_tkm_download_count'"
    );
    $total_downloads = $total_downloads ? intval($total_downloads) : 0;
    
    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
        <div style="text-align:center;padding:15px;background:#f0f0f1;border-radius:5px;">
            <div style="font-size:32px;font-weight:bold;color:#c92651;"><?php echo number_format($total); ?></div>
            <div style="font-size:12px;color:#666;margin-top:5px;"><?php _e('Total Documents', 'teacherske'); ?></div>
        </div>
        
        <div style="text-align:center;padding:15px;background:#f0f0f1;border-radius:5px;">
            <div style="font-size:32px;font-weight:bold;color:#2271b1;"><?php echo number_format($total_downloads); ?></div>
            <div style="font-size:12px;color:#666;margin-top:5px;"><?php _e('Total Downloads', 'teacherske'); ?></div>
        </div>
    </div>
    
    <p>
        <a href="<?php echo admin_url('post-new.php?post_type=teacher_document'); ?>" class="button button-primary">
            <?php _e('Add New Document', 'teacherske'); ?>
        </a>
        <a href="<?php echo admin_url('edit.php?post_type=teacher_document'); ?>" class="button">
            <?php _e('View All Documents', 'teacherske'); ?>
        </a>
    </p>
    <?php
}

/**
 * Admin Notice for Keyboard Shortcuts
 */
function tkm_keyboard_shortcuts_notice() {
    $screen = get_current_screen();
    
    if ($screen && $screen->post_type === 'teacher_document' && in_array($screen->base, array('post', 'post-new'))) {
        // Only show once per user
        $user_id = get_current_user_id();
        $dismissed = get_user_meta($user_id, 'tkm_shortcuts_dismissed', true);
        
        if (!$dismissed) {
            ?>
            <div class="notice notice-info is-dismissible" style="border-left-color:#c92651;" data-dismissible="tkm-shortcuts">
                <p>
                    <strong>⚡ <?php _e('Ultra-Fast Editor Activated!', 'teacherske'); ?></strong> 
                    <?php _e('Use', 'teacherske'); ?> <kbd style="background:#f0f0f0;padding:2px 6px;border-radius:3px;font-family:monospace;">Ctrl+S</kbd> / <kbd style="background:#f0f0f0;padding:2px 6px;border-radius:3px;font-family:monospace;">Cmd+S</kbd> <?php _e('to quick save,', 'teacherske'); ?> 
                    <kbd style="background:#f0f0f0;padding:2px 6px;border-radius:3px;font-family:monospace;">Ctrl+Enter</kbd> <?php _e('to publish instantly.', 'teacherske'); ?>
                </p>
            </div>
            <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '[data-dismissible="tkm-shortcuts"] .notice-dismiss', function() {
                    $.post(ajaxurl, {
                        action: 'tkm_dismiss_shortcuts',
                        nonce: '<?php echo wp_create_nonce('tkm_dismiss'); ?>'
                    });
                });
            });
            </script>
            <?php
        }
    }
}
add_action('admin_notices', 'tkm_keyboard_shortcuts_notice');

/**
 * AJAX: Dismiss Shortcuts Notice
 */
function tkm_ajax_dismiss_shortcuts() {
    check_ajax_referer('tkm_dismiss', 'nonce');
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'tkm_shortcuts_dismissed', true);
    wp_send_json_success();
}
add_action('wp_ajax_tkm_dismiss_shortcuts', 'tkm_ajax_dismiss_shortcuts');

/**
 * Enqueue Admin CSS for Documents
 */
function tkm_admin_enqueue_styles($hook) {
    if (!in_array($hook, array('edit.php', 'post.php', 'post-new.php'))) return;
    
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'teacher_document') return;
    
    wp_enqueue_style(
        'tkm-admin-css',
        TKM_URL . 'assets/css/admin.css',
        array(),
        TKM_VERSION
    );
}
add_action('admin_enqueue_scripts', 'tkm_admin_enqueue_styles');
