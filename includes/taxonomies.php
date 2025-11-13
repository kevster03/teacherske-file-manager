<?php
/**
 * Taxonomy Registration
 * 
 * Registers ONLY the file_category taxonomy
 * Subjects are now stored as meta fields (not taxonomy)
 */

if (!defined('ABSPATH')) exit;

/**
 * Register File Category Taxonomy
 * This is the ONLY taxonomy - used for filtering in tables
 */
function tkm_register_file_category_taxonomy() {
    $labels = array(
        'name' => __('File Categories', 'teacherske'),
        'singular_name' => __('File Category', 'teacherske'),
        'menu_name' => __('File Categories', 'teacherske'),
        'all_items' => __('All Categories', 'teacherske'),
        'edit_item' => __('Edit Category', 'teacherske'),
        'view_item' => __('View Category', 'teacherske'),
        'update_item' => __('Update Category', 'teacherske'),
        'add_new_item' => __('Add New Category', 'teacherske'),
        'new_item_name' => __('New Category Name', 'teacherske'),
        'parent_item' => __('Parent Category', 'teacherske'),
        'parent_item_colon' => __('Parent Category:', 'teacherske'),
        'search_items' => __('Search Categories', 'teacherske'),
        'popular_items' => __('Popular Categories', 'teacherske'),
        'not_found' => __('No categories found.', 'teacherske'),
    );
    
    $args = array(
        'labels' => $labels,
        'description' => __('File categories for filtering (internal use only, not indexed)', 'teacherske'),
        'public' => true,
        'publicly_queryable' => false, // Not indexed by search engines
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false, // Don't show in navigation
        'show_in_rest' => false,
        'show_tagcloud' => false,
        'show_in_quick_edit' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => false, // No public URLs
        'capabilities' => array(
            'manage_terms' => 'manage_options',
            'edit_terms' => 'manage_options',
            'delete_terms' => 'manage_options',
            'assign_terms' => 'edit_posts',
        ),
    );
    
    register_taxonomy('file_category', 'teacher_document', $args);
}
add_action('init', 'tkm_register_file_category_taxonomy');

/**
 * Add Custom Column to File Category Taxonomy
 */
function tkm_category_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'name') {
            $new_columns['usage'] = __('Usage', 'teacherske');
        }
    }
    return $new_columns;
}
add_filter('manage_edit-file_category_columns', 'tkm_category_columns');

/**
 * Populate Custom Columns
 */
function tkm_category_column_content($content, $column_name, $term_id) {
    if ($column_name === 'usage') {
        $content = '<em>' . __('For filtering only', 'teacherske') . '</em>';
    }
    return $content;
}
add_filter('manage_file_category_custom_column', 'tkm_category_column_content', 10, 3);

/**
 * Add note to category edit screen
 */
function tkm_category_edit_form_note() {
    ?>
    <div class="notice notice-info inline" style="margin:15px 0;padding:12px;">
        <p><strong><?php _e('Note:', 'teacherske'); ?></strong> <?php _e('File categories are for internal filtering only (in tables, widgets, etc.). They are not indexed by search engines. RankMath or other SEO plugins can be configured to no-index these if needed.', 'teacherske'); ?></p>
    </div>
    <?php
}
add_action('file_category_edit_form', 'tkm_category_edit_form_note', 10, 1);
add_action('file_category_add_form', 'tkm_category_edit_form_note', 10, 1);
