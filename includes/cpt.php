<?php
/**
 * Custom Post Type Registration
 * 
 * Registers the teacher_document post type
 * Optimized for performance and WP Tables Pro compatibility
 * Subject is now a meta field (not taxonomy)
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Teacher Document Post Type
 */
function tkm_register_teacher_document_cpt() {
    $labels = array(
        'name' => __('Documents', 'teacherske'),
        'singular_name' => __('Document', 'teacherske'),
        'menu_name' => __('Documents', 'teacherske'),
        'name_admin_bar' => __('Document', 'teacherske'),
        'add_new' => __('Add Document', 'teacherske'),
        'add_new_item' => __('Add New Document', 'teacherske'),
        'new_item' => __('New Document', 'teacherske'),
        'edit_item' => __('Edit Document', 'teacherske'),
        'view_item' => __('View Document', 'teacherske'),
        'view_items' => __('View Documents', 'teacherske'),
        'all_items' => __('All Documents', 'teacherske'),
        'search_items' => __('Search Documents', 'teacherske'),
        'not_found' => __('No documents found.', 'teacherske'),
        'not_found_in_trash' => __('No documents found in Trash.', 'teacherske'),
        'featured_image' => __('Document Cover Image', 'teacherske'),
        'set_featured_image' => __('Set cover image', 'teacherske'),
        'remove_featured_image' => __('Remove cover image', 'teacherske'),
        'use_featured_image' => __('Use as cover image', 'teacherske'),
    );
    
    $args = array(
        'labels' => $labels,
        'description' => __('Educational documents and resources', 'teacherske'),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'documents',
            'with_front' => false,
            'feeds' => false,
            'pages' => true
        ),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-media-document',
        'supports' => array(
            'title',
            'author',
            'thumbnail',
            'revisions',
            'custom-fields' // Required for WP Tables Pro
        ),
        'taxonomies' => array('file_category'), // ONLY file_category taxonomy
        'show_in_rest' => false, // Disabled for faster classic editor
        'show_in_nav_menus' => true,
        'can_export' => true,
        'delete_with_user' => false,
        'map_meta_cap' => true
    );
    
    register_post_type('teacher_document', $args);
}
add_action('init', 'tkm_register_teacher_document_cpt', 0);

/**
 * Register Document Template Post Type
 */
function tkm_register_template_cpt() {
    $labels = array(
        'name' => __('Document Templates', 'teacherske'),
        'singular_name' => __('Template', 'teacherske'),
        'menu_name' => __('Templates', 'teacherske'),
        'add_new' => __('Add Template', 'teacherske'),
        'add_new_item' => __('Add New Template', 'teacherske'),
        'edit_item' => __('Edit Template', 'teacherske'),
        'new_item' => __('New Template', 'teacherske'),
        'view_item' => __('View Template', 'teacherske'),
        'all_items' => __('Templates', 'teacherske'),
        'search_items' => __('Search Templates', 'teacherske'),
        'not_found' => __('No templates found', 'teacherske'),
    );

    $args = array(
        'labels' => $labels,
        'description' => __('Reusable document templates with predefined metadata', 'teacherske'),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=teacher_document',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => array('title', 'thumbnail'),
        'show_in_rest' => false,
        'can_export' => true
    );

    register_post_type('teacher_template', $args);
}
add_action('init', 'tkm_register_template_cpt', 0);

/**
 * Customize Post Type Messages
 */
function tkm_custom_post_messages($messages) {
    $post = get_post();
    $post_type = get_post_type($post);
    
    if ($post_type !== 'teacher_document') {
        return $messages;
    }
    
    $messages['teacher_document'] = array(
        0  => '', // Unused
        1  => sprintf(__('Document updated. <a href="%s">View document</a>', 'teacherske'), esc_url(get_permalink($post->ID))),
        4  => __('Document updated.', 'teacherske'),
        6  => sprintf(__('Document published. <a href="%s">View document</a>', 'teacherske'), esc_url(get_permalink($post->ID))),
        7  => __('Document saved.', 'teacherske'),
        8  => sprintf(__('Document submitted. <a target="_blank" href="%s">Preview document</a>', 'teacherske'), esc_url(add_query_arg('preview', 'true', get_permalink($post->ID)))),
        10 => sprintf(__('Document draft updated. <a target="_blank" href="%s">Preview document</a>', 'teacherske'), esc_url(add_query_arg('preview', 'true', get_permalink($post->ID)))),
    );
    
    return $messages;
}
add_filter('post_updated_messages', 'tkm_custom_post_messages');

/**
 * Add Custom Admin Columns
 */
function tkm_admin_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        if ($key === 'title') {
            $new_columns['tkm_file_type'] = __('File', 'teacherske');
            $new_columns['tkm_level'] = __('Level', 'teacherske');
            $new_columns['tkm_grade'] = __('Grade', 'teacherske');
            $new_columns['tkm_subject'] = __('Subject', 'teacherske');
            $new_columns['tkm_views'] = __('Views', 'teacherske');
        }
    }
    
    return $new_columns;
}
add_filter('manage_teacher_document_posts_columns', 'tkm_admin_columns');

/**
 * Populate Custom Admin Columns
 */
function tkm_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'tkm_file_type':
            $ext = get_post_meta($post_id, '_tkm_file_ext', true);
            if ($ext) {
                echo '<span style="background:#c92651;color:#fff;padding:4px 8px;border-radius:4px;font-weight:600;font-size:11px">' . esc_html(strtoupper($ext)) . '</span>';
            } else {
                echo '—';
            }
            break;
            
        case 'tkm_level':
            $level = get_post_meta($post_id, '_tkm_level', true);
            $levels = tkm_get_levels();
            echo isset($levels[$level]) ? esc_html($levels[$level]['label']) : '—';
            break;
            
        case 'tkm_grade':
            $grade = get_post_meta($post_id, '_tkm_grade', true);
            echo $grade ? '<strong>' . esc_html($grade) . '</strong>' : '—';
            break;
            
        case 'tkm_subject':
            $subject = get_post_meta($post_id, '_tkm_subject', true);
            echo $subject ? esc_html($subject) : '—';
            break;
            
        case 'tkm_views':
            $count = intval(get_post_meta($post_id, '_tkm_view_count', true));
            echo '<strong style="color:#2271b1">👁️ ' . number_format($count) . '</strong>';
            break;
    }
}
add_action('manage_teacher_document_posts_custom_column', 'tkm_admin_column_content', 10, 2);

/**
 * Make Columns Sortable
 */
function tkm_sortable_columns($columns) {
    $columns['tkm_grade'] = 'tkm_grade';
    $columns['tkm_level'] = 'tkm_level';
    $columns['tkm_views'] = 'tkm_views';
    return $columns;
}
add_filter('manage_edit-teacher_document_sortable_columns', 'tkm_sortable_columns');

/**
 * Handle Custom Column Sorting
 */
function tkm_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    
    $orderby = $query->get('orderby');
    
    if ($orderby === 'tkm_grade') {
        $query->set('meta_key', '_tkm_grade');
        $query->set('orderby', 'meta_value');
    } elseif ($orderby === 'tkm_level') {
        $query->set('meta_key', '_tkm_level');
        $query->set('orderby', 'meta_value');
    } elseif ($orderby === 'tkm_views') {
        $query->set('meta_key', '_tkm_view_count');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'tkm_column_orderby');

/**
 * Add Filters to Admin List
 */
function tkm_admin_filters() {
    global $typenow;
    
    if ($typenow !== 'teacher_document') return;
    
    // Level filter
    $levels = tkm_get_levels();
    $current_level = isset($_GET['tkm_level']) ? $_GET['tkm_level'] : '';
    
    echo '<select name="tkm_level">';
    echo '<option value="">All Levels</option>';
    foreach ($levels as $key => $data) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($key),
            selected($current_level, $key, false),
            esc_html($data['label'])
        );
    }
    echo '</select>';
}
add_action('restrict_manage_posts', 'tkm_admin_filters');

/**
 * Handle Admin Filters
 */
function tkm_handle_admin_filters($query) {
    global $pagenow, $typenow;
    
    if ($pagenow === 'edit.php' && $typenow === 'teacher_document' && is_admin()) {
        if (isset($_GET['tkm_level']) && $_GET['tkm_level'] !== '') {
            $query->set('meta_key', '_tkm_level');
            $query->set('meta_value', sanitize_text_field($_GET['tkm_level']));
        }
    }
}
add_filter('parse_query', 'tkm_handle_admin_filters');

/**
 * Add Title Placeholder
 */
function tkm_change_title_placeholder($title) {
    $screen = get_current_screen();
    
    if ($screen && $screen->post_type === 'teacher_document') {
        $title = __('Enter document title (e.g., "Grade 7 Mathematics - Algebra Notes")', 'teacherske');
    }
    
    return $title;
}
add_filter('enter_title_here', 'tkm_change_title_placeholder');
