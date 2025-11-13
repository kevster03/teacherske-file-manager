<?php
/**
 * Uninstall Script
 * 
 * Safely removes all plugin data when deleted from WordPress
 * Only runs if user has selected "Remove data on uninstall" in settings
 */

// Exit if uninstall not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user wants to keep data
$remove_data = get_option('tkm_remove_on_uninstall', 'no');

if ($remove_data === 'yes') {
    
    // 1. Delete all plugin options
    $options = array(
        'tkm_remove_on_uninstall',
        'tkm_primary_color',
        'tkm_secondary_color',
        'tkm_bg_color_1',
        'tkm_bg_color_2',
        'tkm_countdown_duration',
        'tkm_enable_tracking',
        'tkm_show_badge',
        'tkm_track_by_ip',
        'tkm_layout_density',
        'tkm_featured_image_size',
        'tkm_show_description',
        'tkm_fallback_image',
        'tkm_loader_type',
        'tkm_related_files_count',
        'tkm_enable_schema',
        'tkm_subjects_by_level',
        'tkm_version_start',
        'tkm_version_end'
    );
    
    foreach ($options as $option) {
        delete_option($option);
    }
    
    // 2. Delete all documents and their metadata
    $documents = get_posts(array(
        'post_type' => 'teacher_document',
        'numberposts' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($documents as $doc) {
        // Delete all post meta
        $meta_keys = array(
            '_tkm_file',
            '_tkm_file_ext',
            '_tkm_file_size',
            '_tkm_level',
            '_tkm_grade',
            '_tkm_version',
            '_tkm_description',
            '_tkm_download_count',
            '_tkm_download_ips'
        );
        
        foreach ($meta_keys as $meta_key) {
            delete_post_meta($doc->ID, $meta_key);
        }
        
        // Force delete post and its revisions
        wp_delete_post($doc->ID, true);
    }
    
    // 3. Delete subject taxonomy terms
    $subjects = get_terms(array(
        'taxonomy' => 'subject',
        'hide_empty' => false,
        'fields' => 'ids'
    ));
    
    if (!empty($subjects) && !is_wp_error($subjects)) {
        foreach ($subjects as $term_id) {
            wp_delete_term($term_id, 'subject');
        }
    }
    
    // 4. Clear any cached data
    wp_cache_flush();
    
} else {
    // User wants to keep data - just delete the removal preference option
    delete_option('tkm_remove_on_uninstall');
}

// 5. Clear rewrite rules
flush_rewrite_rules();