<?php
/**
 * Plugin Name: TeachersKE File Manager
 * Version: 7.0.0
 * Description: Ultra-optimized document management - Fast, SEO-ready, Ezoic-friendly, RankMath compatible with Content Blocks
 * Author: TeachersKE
 * Text Domain: teacherske
 */
if (!defined('ABSPATH')) exit;

define('TKM_VERSION', '7.0.0');
define('TKM_DIR', plugin_dir_path(__FILE__));
define('TKM_URL', plugin_dir_url(__FILE__));
define('TKM_BASENAME', plugin_basename(__FILE__));
define('TKM_FILE', __FILE__);

require_once TKM_DIR . 'includes/helpers.php';
require_once TKM_DIR . 'includes/cpt.php';
require_once TKM_DIR . 'includes/taxonomies.php';
require_once TKM_DIR . 'includes/meta-boxes.php';
require_once TKM_DIR . 'includes/admin.php';
require_once TKM_DIR . 'includes/frontend.php';
require_once TKM_DIR . 'includes/settings.php';
require_once TKM_DIR . 'includes/class-download-tracker.php';
require_once TKM_DIR . 'includes/class-view-tracker.php';
require_once TKM_DIR . 'includes/class-bulk-importer.php';
require_once TKM_DIR . 'includes/class-content-blocks.php';
require_once TKM_DIR . 'admin/csv-import-page.php';

register_activation_hook(__FILE__, 'tkm_activate_plugin');
function tkm_activate_plugin() {
    $defaults = array(
        'tkm_remove_on_uninstall' => 'no',
        'tkm_primary_color' => '#d30038',
        'tkm_secondary_color' => '#ff6100',
        'tkm_bg_color_1' => '#c6e0f2',
        'tkm_bg_color_2' => '#e0c8ff',
        'tkm_bg_color_3' => '#f2ffb2',
        'tkm_bg_color_4' => '#f2dec1',
        'tkm_border_color' => '#24011a',
        'tkm_border_size' => 3,
        'tkm_countdown_duration' => 10,
        'tkm_related_files_count' => 8,
        'tkm_version_start' => 2025,
        'tkm_version_end' => 2050,
        'tkm_enable_tracking' => 'yes',
        'tkm_enable_view_tracking' => 'yes',
        'tkm_track_by_ip' => 'yes',
        'tkm_enable_schema' => 'yes',
        'tkm_enable_sidebar' => 'yes',
        'tkm_show_description' => 'yes',
        'tkm_loader_type' => 'bar'
    );
    foreach ($defaults as $key => $value) {
        if (get_option($key) === false) {
            update_option($key, $value);
        }
    }
    $default_subjects = array(
        'early_years' => array('English Activities', 'Mathematics Activities', 'Kiswahili', 'Environmental Activities', 'Hygiene & Nutrition', 'Religious Education'),
        'lower_primary' => array('English', 'Kiswahili', 'Mathematics', 'Science & Technology', 'Social Studies', 'Religious Education', 'Creative Arts'),
        'upper_primary' => array('English', 'Kiswahili', 'Mathematics', 'Integrated Science', 'Social Studies', 'Religious Education', 'Creative Arts', 'Agriculture'),
        'junior_secondary' => array('English', 'Kiswahili', 'Mathematics', 'Integrated Science', 'Health Education', 'Pre-Technical Studies', 'Business Studies', 'Agriculture', 'Life Skills'),
        'senior_secondary' => array('English', 'Kiswahili', 'Mathematics', 'Biology', 'Physics', 'Chemistry', 'History', 'Geography', 'Business Studies', 'Computer Science', 'Agriculture')
    );
    if (get_option('tkm_subjects_by_level') === false) {
        update_option('tkm_subjects_by_level', $default_subjects);
    }

    // Create content blocks table for SEO enhancement
    global $wpdb;
    $table_name = $wpdb->prefix . 'tkm_content_blocks';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        block_title varchar(255) NOT NULL,
        block_type varchar(50) NOT NULL DEFAULT 'generic',
        block_content longtext NOT NULL,
        structured_data longtext DEFAULT NULL,
        subject varchar(100) DEFAULT NULL,
        grade varchar(50) DEFAULT NULL,
        level varchar(50) DEFAULT NULL,
        category varchar(100) DEFAULT NULL,
        has_schema tinyint(1) DEFAULT 0,
        schema_type varchar(50) DEFAULT NULL,
        bg_color varchar(7) DEFAULT '#c6e0f2',
        display_position varchar(20) DEFAULT 'after_description',
        display_order int(11) DEFAULT 0,
        active tinyint(1) DEFAULT 1,
        created_date datetime DEFAULT CURRENT_TIMESTAMP,
        updated_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY block_type (block_type),
        KEY active (active)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    flush_rewrite_rules();
    set_transient('tkm_activation_notice', true, 30);
}

register_deactivation_hook(__FILE__, 'tkm_deactivate_plugin');
function tkm_deactivate_plugin() {
    flush_rewrite_rules();
}

add_filter('plugin_action_links_' . TKM_BASENAME, 'tkm_plugin_action_links');
function tkm_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=tkm-settings') . '" style="color:#d30038;font-weight:600;">Settings</a>';
    $docs_link = '<a href="' . admin_url('edit.php?post_type=teacher_document') . '">Documents</a>';
    array_unshift($links, $settings_link, $docs_link);
    return $links;
}

add_action('admin_notices', 'tkm_activation_notice');
function tkm_activation_notice() {
    if (get_transient('tkm_activation_notice')) {
        ?>
        <div class="notice notice-success is-dismissible" style="border-left-color:#d30038;">
            <p><strong>🎉 TeachersKE File Manager Activated!</strong></p>
            <p>
                <a href="<?php echo admin_url('post-new.php?post_type=teacher_document'); ?>" class="button button-primary">Add Your First Document</a>
                <a href="<?php echo admin_url('options-general.php?page=tkm-settings'); ?>" class="button">Configure Settings</a>
            </p>
        </div>
        <?php
        delete_transient('tkm_activation_notice');
    }
}

add_action('plugins_loaded', 'tkm_load_textdomain');
function tkm_load_textdomain() {
    load_plugin_textdomain('teacherske', false, dirname(TKM_BASENAME) . '/languages');
}
