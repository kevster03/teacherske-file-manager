<?php
/**
 * Settings Registration
 * 
 * Registers the settings page and handles settings logic
 * Full UI is in admin/settings-page.php
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Settings Menu
 * Attached to Settings menu (not separate top-level menu)
 */
function tkm_register_settings_menu() {
    add_options_page(
        __('TeachersKE File Manager Settings', 'teacherske'),
        __('File Manager', 'teacherske'),
        'manage_options',
        'tkm-settings',
        'tkm_render_settings_page'
    );
}
add_action('admin_menu', 'tkm_register_settings_menu');

/**
 * Render Settings Page
 */
function tkm_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'teacherske'));
    }
    
    // Load the settings page template
    require_once TKM_DIR . 'admin/settings-page.php';
}

/**
 * Register All Settings
 */
function tkm_register_settings() {
    $settings = array(
        // Data Management
        'tkm_remove_on_uninstall',

        // Colors
        'tkm_primary_color',
        'tkm_secondary_color',
        'tkm_bg_color_1',
        'tkm_bg_color_2',

        // Download Settings
        'tkm_countdown_duration',
        'tkm_enable_tracking',
        'tkm_show_badge',
        'tkm_track_by_ip',
        'tkm_loader_type',

        // UI Options
        'tkm_layout_density',
        'tkm_featured_image_size',
        'tkm_show_description',
        'tkm_fallback_image',
        'tkm_fallback_featured_image', // NEW: Fallback featured image ID
        'tkm_related_files_count',
        'tkm_enable_sidebar', // NEW: Sidebar enable/disable

        // SEO
        'tkm_enable_schema',

        // Version Range
        'tkm_version_start',
        'tkm_version_end',

        // Subjects
        'tkm_subjects_by_level'
    );
    
    foreach ($settings as $setting) {
        register_setting('tkm_settings_group', $setting, array(
            'sanitize_callback' => 'tkm_sanitize_setting'
        ));
    }
}
add_action('admin_init', 'tkm_register_settings');

/**
 * Sanitize Setting Values
 */
function tkm_sanitize_setting($value) {
    $setting = str_replace('tkm_', '', current_filter());
    $setting = str_replace('sanitize_option_', '', $setting);
    
    // Color fields
    if (strpos($setting, '_color') !== false) {
        return tkm_sanitize_color($value);
    }
    
    // Boolean fields
    if (in_array($setting, array('remove_on_uninstall', 'enable_tracking', 'show_badge', 'track_by_ip', 'show_description', 'enable_schema', 'enable_sidebar'))) {
        return $value === 'yes' ? 'yes' : 'no';
    }
    
    // Integer fields
    if (in_array($setting, array('countdown_duration', 'featured_image_size', 'related_files_count', 'version_start', 'version_end', 'fallback_featured_image'))) {
        return intval($value);
    }

    // Array fields
    if ($setting === 'subjects_by_level') {
        return is_array($value) ? $value : array();
    }

    // URL fields
    if ($setting === 'fallback_image') {
        return esc_url_raw($value);
    }
    
    // Text fields
    return sanitize_text_field($value);
}

/**
 * Export Documents to JSON
 */
function tkm_export_documents() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied', 'teacherske'));
    }
    
    check_admin_referer('tkm_export_documents');
    
    $documents = get_posts(array(
        'post_type' => 'teacher_document',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    $export_data = array();
    
    foreach ($documents as $doc) {
        $categories = wp_get_post_terms($doc->ID, 'file_category', array('fields' => 'names'));
        
        $export_data[] = array(
            'title' => $doc->post_title,
            'content' => $doc->post_content,
            'status' => $doc->post_status,
            'author' => get_userdata($doc->post_author)->user_login,
            'date' => $doc->post_date,
            'meta' => array(
                'file' => get_post_meta($doc->ID, '_tkm_file', true),
                'level' => get_post_meta($doc->ID, '_tkm_level', true),
                'grade' => get_post_meta($doc->ID, '_tkm_grade', true),
                'subject' => get_post_meta($doc->ID, '_tkm_subject', true), // Subject as meta
                'version' => get_post_meta($doc->ID, '_tkm_version', true),
                'description' => get_post_meta($doc->ID, '_tkm_description', true),
                'download_count' => get_post_meta($doc->ID, '_tkm_download_count', true)
            ),
            'categories' => $categories,
            'thumbnail' => get_the_post_thumbnail_url($doc->ID, 'full')
        );
    }
    
    $filename = 'teacherske-documents-' . date('Y-m-d-His') . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
add_action('admin_post_tkm_export_documents', 'tkm_export_documents');

/**
 * Download CSV Template
 */
function tkm_download_csv_template() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied', 'teacherske'));
    }
    
    $filename = 'teacherske-import-template-' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Headers
    fputcsv($output, array('title', 'description', 'file_url', 'level', 'grade', 'subject', 'version', 'category', 'featured_image_url'));
    
    // Sample rows
    fputcsv($output, array(
        'Grade 7 Mathematics - Algebra Notes',
        'Comprehensive notes covering algebra topics for CBC Grade 7',
        'https://example.com/files/grade7-math-algebra.pdf',
        'junior_secondary',
        'Grade 7',
        'Mathematics',
        '2025 Edition',
        'Notes',
        'https://example.com/images/math-cover.jpg'
    ));
    
    fputcsv($output, array(
        'PP2 English Activities Term 1',
        'Complete English activities for PP2 learners',
        'https://example.com/files/pp2-english-term1.docx',
        'early_years',
        'PP2',
        'English Activities',
        '2025 Edition',
        'Schemes',
        'https://example.com/images/pp2-cover.jpg'
    ));
    
    fclose($output);
    exit;
}
add_action('admin_post_tkm_download_template', 'tkm_download_csv_template');

// NOTE: CSV Import page moved to admin/csv-import-page.php (full implementation)
// Old placeholder functions removed to prevent duplicate function definition errors

/**
 * Import Documents from JSON
 */
function tkm_import_documents() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied', 'teacherske'));
    }
    
    check_admin_referer('tkm_import_documents');
    
    if (empty($_FILES['tkm_import_file'])) {
        wp_redirect(add_query_arg(array('page' => 'tkm-settings', 'tab' => 'import', 'error' => 'nofile'), admin_url('options-general.php')));
        exit;
    }
    
    $file = $_FILES['tkm_import_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        wp_redirect(add_query_arg(array('page' => 'tkm-settings', 'tab' => 'import', 'error' => 'upload'), admin_url('options-general.php')));
        exit;
    }
    
    $json_data = file_get_contents($file['tmp_name']);
    $documents = json_decode($json_data, true);
    
    if (!is_array($documents)) {
        wp_redirect(add_query_arg(array('page' => 'tkm-settings', 'tab' => 'import', 'error' => 'invalid'), admin_url('options-general.php')));
        exit;
    }
    
    $imported = 0;
    
    foreach ($documents as $doc_data) {
        // Create post
        $post_data = array(
            'post_title' => sanitize_text_field($doc_data['title']),
            'post_content' => wp_kses_post($doc_data['content'] ?? ''),
            'post_status' => sanitize_key($doc_data['status'] ?? 'draft'),
            'post_type' => 'teacher_document',
            'post_date' => sanitize_text_field($doc_data['date'] ?? current_time('mysql'))
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Import meta
            if (isset($doc_data['meta'])) {
                foreach ($doc_data['meta'] as $key => $value) {
                    update_post_meta($post_id, '_tkm_' . $key, $value);
                }
            }
            
            // Import categories (new taxonomy)
            if (isset($doc_data['categories']) && is_array($doc_data['categories'])) {
                wp_set_post_terms($post_id, $doc_data['categories'], 'file_category');
            }
            
            $imported++;
        }
    }
    
    wp_redirect(add_query_arg(array('page' => 'tkm-settings', 'tab' => 'import', 'imported' => $imported), admin_url('options-general.php')));
    exit;
}
add_action('admin_post_tkm_import_documents', 'tkm_import_documents');

/**
 * Reset Download Counts
 */
function tkm_reset_all_downloads() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied', 'teacherske'));
    }
    
    check_admin_referer('tkm_reset_downloads');
    
    global $wpdb;
    
    // Reset all download counts
    $wpdb->query(
        "UPDATE {$wpdb->postmeta} 
        SET meta_value = '0' 
        WHERE meta_key = '_tkm_download_count'"
    );
    
    // Delete IP tracking data
    $wpdb->query(
        "DELETE FROM {$wpdb->postmeta} 
        WHERE meta_key = '_tkm_download_ips'"
    );
    
    wp_redirect(add_query_arg(array('page' => 'tkm-settings', 'tab' => 'data', 'reset' => 'success'), admin_url('options-general.php')));
    exit;
}
add_action('admin_post_tkm_reset_downloads', 'tkm_reset_all_downloads');

/**
 * Save Subject Management
 */
function tkm_save_subjects() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied', 'teacherske'));
    }
    
    check_admin_referer('tkm_save_subjects');
    
    if (isset($_POST['tkm_subjects'])) {
        $subjects_by_level = array();
        
        foreach ($_POST['tkm_subjects'] as $level => $subjects_string) {
            // Split by line breaks and clean
            $subjects = array_filter(array_map('trim', explode("\n", $subjects_string)));
            $subjects_by_level[sanitize_key($level)] = array_map('sanitize_text_field', $subjects);
        }
        
        update_option('tkm_subjects_by_level', $subjects_by_level);
    }
    
    wp_redirect(add_query_arg(array('page' => 'tkm-settings', 'tab' => 'subjects', 'saved' => 'success'), admin_url('options-general.php')));
    exit;
}
add_action('admin_post_tkm_save_subjects', 'tkm_save_subjects');

/**
 * Enqueue Settings Page Assets
 */
function tkm_settings_assets($hook) {
    if ($hook !== 'settings_page_tkm-settings') return;
    
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_media(); // For fallback image picker
    
    wp_enqueue_style(
        'tkm-settings-css',
        TKM_URL . 'assets/css/admin.css',
        array(),
        TKM_VERSION
    );
}
add_action('admin_enqueue_scripts', 'tkm_settings_assets');