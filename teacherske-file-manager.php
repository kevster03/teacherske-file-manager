<?php
/**
 * Plugin Name: TeachersKE File Manager
 * Version: 5.3.0
 * Description: Ultra-optimized document management - Fast, SEO-ready, Ezoic-friendly
 * Author: TeachersKE
 * Text Domain: teacherske
 */
if (!defined('ABSPATH')) exit;

define('TKM_VERSION', '5.3.0');
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
require_once TKM_DIR . 'includes/class-bulk-importer.php';
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
