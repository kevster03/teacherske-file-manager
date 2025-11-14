<?php
/**
 * Settings Page - Complete with All Options
 */
if (!defined('ABSPATH')) exit;

// Handle form submission
if (isset($_POST['tkm_save_settings']) && check_admin_referer('tkm_settings_save')) {
    $settings_to_save = array(
        'tkm_primary_color','tkm_secondary_color','tkm_bg_color_1','tkm_bg_color_2','tkm_bg_color_3','tkm_bg_color_4','tkm_border_color','tkm_border_size','tkm_countdown_duration','tkm_related_files_count','tkm_version_start','tkm_version_end','tkm_remove_on_uninstall','tkm_fallback_featured_image'
    );
    foreach ($settings_to_save as $setting) {
        if (isset($_POST[$setting])) {
            $value = $_POST[$setting];
            if (strpos($setting, '_color') !== false) {
                $value = tkm_sanitize_color($value);
            } elseif (in_array($setting, array('tkm_countdown_duration', 'tkm_related_files_count', 'tkm_version_start', 'tkm_version_end', 'tkm_border_size'))) {
                $value = intval($value);
            } elseif ($setting === 'tkm_remove_on_uninstall') {
                $value = $value === '1' ? 'yes' : 'no';
            } elseif ($setting === 'tkm_fallback_featured_image') {
                $value = intval($value); // Attachment ID
            } else {
                $value = sanitize_text_field($value);
            }
            update_option($setting, $value);
        } else {
            if ($setting === 'tkm_remove_on_uninstall') update_option($setting, 'no');
        }
    }
    echo '<div class="notice notice-success is-dismissible"><p><strong>✅ Settings saved successfully!</strong></p></div>';
}

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
$settings = array(
    'primary_color' => tkm_get_setting('primary_color', '#d30038'),
    'secondary_color' => tkm_get_setting('secondary_color', '#ff6100'),
    'bg_color_1' => tkm_get_setting('bg_color_1', '#c6e0f2'),
    'bg_color_2' => tkm_get_setting('bg_color_2', '#e0c8ff'),
    'bg_color_3' => tkm_get_setting('bg_color_3', '#f2ffb2'),
    'bg_color_4' => tkm_get_setting('bg_color_4', '#f2dec1'),
    'border_color' => tkm_get_setting('border_color', '#24011a'),
    'border_size' => tkm_get_setting('border_size', 3),
    'countdown_duration' => tkm_get_setting('countdown_duration', 10),
    'related_files_count' => tkm_get_setting('related_files_count', 8),
    'version_start' => tkm_get_setting('version_start', 2025),
    'version_end' => tkm_get_setting('version_end', 2050),
    'remove_on_uninstall' => tkm_get_setting('remove_on_uninstall', 'no'),
    'fallback_featured_image' => tkm_get_setting('fallback_featured_image', 0)
);
?>
<div class="wrap tkm-settings-wrap">
<h1><span class="dashicons dashicons-media-document" style="color:<?php echo esc_attr($settings['primary_color']); ?>;"></span> TeachersKE File Manager Settings</h1>
<h2 class="nav-tab-wrapper">
<a href="?page=tkm-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">General</a>
<a href="?page=tkm-settings&tab=colors" class="nav-tab <?php echo $active_tab === 'colors' ? 'nav-tab-active' : ''; ?>">Colors & Borders</a>
<a href="?page=tkm-settings&tab=subjects" class="nav-tab <?php echo $active_tab === 'subjects' ? 'nav-tab-active' : ''; ?>">Subjects</a>
<a href="?page=tkm-settings&tab=import" class="nav-tab <?php echo $active_tab === 'import' ? 'nav-tab-active' : ''; ?>">Import/Export</a>
<a href="?page=tkm-settings&tab=data" class="nav-tab <?php echo $active_tab === 'data' ? 'nav-tab-active' : ''; ?>">Data</a>
</h2>
<?php if ($active_tab !== 'subjects' && $active_tab !== 'import' && $active_tab !== 'data'): ?>
<form method="post" action=""><?php wp_nonce_field('tkm_settings_save'); ?>
<?php endif; ?>
<?php if ($active_tab === 'general'): ?>
<table class="form-table">
<tr><th scope="row">Version Range</th><td>
<label>Start Year: <input type="number" name="tkm_version_start" value="<?php echo esc_attr($settings['version_start']); ?>" min="2020" max="2099" style="width:100px"></label>&nbsp;&nbsp;
<label>End Year: <input type="number" name="tkm_version_end" value="<?php echo esc_attr($settings['version_end']); ?>" min="2020" max="2099" style="width:100px"></label>
<p class="description">Year range for document versions (e.g., 2025 Edition - 2050 Edition)</p>
</td></tr>
<tr><th scope="row">Related Files Count</th><td>
<input type="number" name="tkm_related_files_count" value="<?php echo esc_attr($settings['related_files_count']); ?>" min="4" max="12" style="width:80px">
<p class="description">Number of related files to display (default: 8)</p>
</td></tr>
<tr><th scope="row">Countdown Duration</th><td>
<input type="number" name="tkm_countdown_duration" value="<?php echo esc_attr($settings['countdown_duration']); ?>" min="0" max="60" style="width:80px"> seconds
<p class="description">Time before download starts (default: 10 seconds)</p>
</td></tr>
<tr><th scope="row">Fallback Featured Image</th><td>
<?php
$fallback_image_id = intval($settings['fallback_featured_image']);
// Validate that the attachment exists and is an image
if ($fallback_image_id) {
    $fallback_image_url = wp_get_attachment_url($fallback_image_id);
    // If attachment doesn't exist or is not an image, clear it
    if (!$fallback_image_url || !wp_attachment_is_image($fallback_image_id)) {
        $fallback_image_id = 0;
        $fallback_image_url = '';
        update_option('tkm_fallback_featured_image', 0);
        echo '<div class="notice notice-warning inline"><p>Previous fallback image was removed (invalid or deleted). Please select a new one.</p></div>';
    }
} else {
    $fallback_image_url = '';
}
?>
<div style="margin-bottom:10px;">
    <input type="hidden" name="tkm_fallback_featured_image" id="tkm_fallback_featured_image" value="<?php echo esc_attr($fallback_image_id); ?>">
    <?php if ($fallback_image_url): ?>
        <div id="tkm_fallback_preview" style="margin-bottom:10px;">
            <img src="<?php echo esc_url($fallback_image_url); ?>" style="max-width:200px;height:auto;border:2px solid #ddd;border-radius:4px;">
        </div>
    <?php else: ?>
        <div id="tkm_fallback_preview" style="display:none;margin-bottom:10px;">
            <img src="" style="max-width:200px;height:auto;border:2px solid #ddd;border-radius:4px;">
        </div>
    <?php endif; ?>
    <button type="button" class="button" id="tkm_select_fallback_image">
        <span class="dashicons dashicons-format-image" style="margin-top:3px;"></span> Select Fallback Image
    </button>
    <?php if ($fallback_image_url): ?>
        <button type="button" class="button" id="tkm_remove_fallback_image">
            <span class="dashicons dashicons-no" style="margin-top:3px;"></span> Remove
        </button>
    <?php else: ?>
        <button type="button" class="button" id="tkm_remove_fallback_image" style="display:none;">
            <span class="dashicons dashicons-no" style="margin-top:3px;"></span> Remove
        </button>
    <?php endif; ?>
</div>
<p class="description">Default featured image for documents that don't have one set</p>
</td></tr>
<tr><th></th><td>
<div style="background:#d4edda;border-left:4px solid #28a745;padding:15px;border-radius:4px;">
<strong>✅ Permanent Features (Always Enabled):</strong>
<ul style="margin:10px 0 0 20px;">
<li>Download tracking with IP protection</li>
<li>Schema.org DigitalDocument markup for SEO</li>
<li>Sticky sidebar widget area</li>
<li>Description display</li>
<li>Progress bar loader</li>
</ul>
</div>
</td></tr>
</table>
<?php elseif ($active_tab === 'colors'): ?>
<table class="form-table">
<tr><th colspan="2"><h3 style="margin:10px 0;">Primary Colors</h3></th></tr>
<tr><th scope="row">Primary Color</th><td>
<input type="text" name="tkm_primary_color" value="<?php echo esc_attr($settings['primary_color']); ?>" class="tkm-color-picker">
<p class="description">Main brand color (buttons, accents, links)</p>
</td></tr>
<tr><th scope="row">Secondary Color</th><td>
<input type="text" name="tkm_secondary_color" value="<?php echo esc_attr($settings['secondary_color']); ?>" class="tkm-color-picker">
<p class="description">Secondary accent color (gradients, highlights)</p>
</td></tr>
<tr><th colspan="2"><h3 style="margin:20px 0 10px;">Background Colors</h3></th></tr>
<tr><th scope="row">Background Color 1</th><td>
<input type="text" name="tkm_bg_color_1" value="<?php echo esc_attr($settings['bg_color_1']); ?>" class="tkm-color-picker">
<p class="description">First background color (light blue)</p>
</td></tr>
<tr><th scope="row">Background Color 2</th><td>
<input type="text" name="tkm_bg_color_2" value="<?php echo esc_attr($settings['bg_color_2']); ?>" class="tkm-color-picker">
<p class="description">Second background color (light purple)</p>
</td></tr>
<tr><th scope="row">Background Color 3</th><td>
<input type="text" name="tkm_bg_color_3" value="<?php echo esc_attr($settings['bg_color_3']); ?>" class="tkm-color-picker">
<p class="description">Third background color (light yellow)</p>
</td></tr>
<tr><th scope="row">Background Color 4</th><td>
<input type="text" name="tkm_bg_color_4" value="<?php echo esc_attr($settings['bg_color_4']); ?>" class="tkm-color-picker">
<p class="description">Fourth background color (light peach)</p>
</td></tr>
<tr><th colspan="2"><h3 style="margin:20px 0 10px;">Border Settings</h3></th></tr>
<tr><th scope="row">Border Color</th><td>
<input type="text" name="tkm_border_color" value="<?php echo esc_attr($settings['border_color']); ?>" class="tkm-color-picker">
<p class="description">Border color for all elements</p>
</td></tr>
<tr><th scope="row">Border Size</th><td>
<input type="number" name="tkm_border_size" value="<?php echo esc_attr($settings['border_size']); ?>" min="1" max="10" style="width:80px"> px
<p class="description">Border thickness (1-10px, default: 3px)</p>
</td></tr>
</table>
<?php elseif ($active_tab === 'subjects'): ?>
<div style="background:#fff;border:1px solid #ccc;border-radius:8px;padding:20px;margin:20px 0">
<h3>Subject Management by Level</h3>
<p>Add subjects for each education level. One subject per line.</p>
<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
<input type="hidden" name="action" value="tkm_save_subjects">
<?php wp_nonce_field('tkm_save_subjects'); ?>
<?php
$levels = tkm_get_levels();
$subjects_by_level = get_option('tkm_subjects_by_level', array());
foreach ($levels as $key => $data):
    $subjects = isset($subjects_by_level[$key]) ? $subjects_by_level[$key] : array();
    $subjects_text = implode("\n", $subjects);
?>
<div style="margin-bottom:25px">
<h4 style="margin-bottom:10px"><?php echo esc_html($data['label']); ?></h4>
<textarea name="tkm_subjects[<?php echo esc_attr($key); ?>]" rows="6" style="width:100%;max-width:600px;font-family:monospace" placeholder="Enter subjects, one per line..."><?php echo esc_textarea($subjects_text); ?></textarea>
</div>
<?php endforeach; ?>
<p><button type="submit" class="button button-primary button-large">Save All Subjects</button></p>
</form>
</div>
<?php elseif ($active_tab === 'import'): ?>
<div style="background:#fff;border:1px solid #ccc;border-radius:8px;padding:20px;margin:20px 0">
<h3>Export Documents</h3>
<p>Export all documents to JSON format for backup or migration.</p>
<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
<input type="hidden" name="action" value="tkm_export_documents">
<?php wp_nonce_field('tkm_export_documents'); ?>
<button type="submit" class="button button-primary"><span class="dashicons dashicons-download" style="margin-top:3px"></span> Export All Documents</button>
</form>
</div>

<div style="background:#fff;border:1px solid #ccc;border-radius:8px;padding:20px;margin:20px 0">
<h3>Import Documents (JSON)</h3>
<p>Import documents from JSON file.</p>
<form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
<input type="hidden" name="action" value="tkm_import_documents">
<?php wp_nonce_field('tkm_import_documents'); ?>
<input type="file" name="tkm_import_file" accept=".json" required>
<button type="submit" class="button button-primary"><span class="dashicons dashicons-upload" style="margin-top:3px"></span> Import JSON File</button>
</form>
</div>

<div style="background:#d1ecf1;border-left:4px solid #0c5460;padding:20px;margin:20px 0">
<h3>📊 CSV Bulk Import</h3>
<p><strong>✅ CSV Import is now fully functional!</strong></p>
<p>Go to <strong>Documents → Import CSV</strong> to use the 3-step import wizard with field mapping and batch processing.</p>
<p><a href="<?php echo admin_url('edit.php?post_type=teacher_document&page=tkm-csv-import'); ?>" class="button button-primary">Open CSV Import Wizard</a></p>
</div>
<?php elseif ($active_tab === 'data'): ?>
<div style="background:#fff;border:1px solid #ccc;border-radius:8px;padding:20px;margin:20px 0">
<h3>Reset Download Counts</h3>
<p>Reset all download counts to zero. This cannot be undone.</p>
<form method="post" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirm('Are you sure? This will reset all download counts.');">
<input type="hidden" name="action" value="tkm_reset_downloads">
<?php wp_nonce_field('tkm_reset_downloads'); ?>
<button type="submit" class="button button-secondary">Reset All Download Counts</button>
</form>
</div>
<table class="form-table">
<tr><th scope="row">Remove Data on Uninstall</th><td>
<form method="post" action="">
<?php wp_nonce_field('tkm_settings_save'); ?>
<label><input type="checkbox" name="tkm_remove_on_uninstall" value="1" <?php checked($settings['remove_on_uninstall'], 'yes'); ?>> Delete all plugin data when plugin is uninstalled</label>
<p class="description">WARNING: This will permanently delete all documents, settings, and data.</p>
<button type="submit" name="tkm_save_settings" class="button button-primary" style="margin-top:10px">Save Setting</button>
</form>
</td></tr>
</table>
<?php endif; ?>
<?php if ($active_tab !== 'subjects' && $active_tab !== 'import' && $active_tab !== 'data'): ?>
<p class="submit"><button type="submit" name="tkm_save_settings" class="button button-primary button-large">Save Settings</button></p>
</form>
<?php endif; ?>
</div>
<script>
jQuery(document).ready(function($) {
    // Color picker
    if ($.fn.wpColorPicker) {
        $('.tkm-color-picker').wpColorPicker();
    }

    // Fallback featured image picker
    var fallbackImageFrame;

    $('#tkm_select_fallback_image').on('click', function(e) {
        e.preventDefault();

        if (fallbackImageFrame) {
            fallbackImageFrame.open();
            return;
        }

        fallbackImageFrame = wp.media({
            title: 'Select Fallback Featured Image',
            button: {
                text: 'Use this image'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });

        fallbackImageFrame.on('select', function() {
            var attachment = fallbackImageFrame.state().get('selection').first().toJSON();
            $('#tkm_fallback_featured_image').val(attachment.id);
            $('#tkm_fallback_preview img').attr('src', attachment.url);
            $('#tkm_fallback_preview').show();
            $('#tkm_remove_fallback_image').show();
        });

        fallbackImageFrame.open();
    });

    $('#tkm_remove_fallback_image').on('click', function(e) {
        e.preventDefault();
        $('#tkm_fallback_featured_image').val('');
        $('#tkm_fallback_preview').hide();
        $('#tkm_remove_fallback_image').hide();
    });
});
</script>
<style>
.tkm-settings-wrap .form-table th{width:220px}
.tkm-settings-wrap h3{margin-top:0}
.tkm-settings-wrap .button .dashicons{margin-top:3px}
</style>
