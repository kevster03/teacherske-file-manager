<?php
/**
 * CSV Import Admin Page
 *
 * Provides a user-friendly interface for bulk importing documents from CSV files
 * with intelligent field mapping and batch processing
 */

if (!defined('ABSPATH')) exit;

/**
 * Register CSV Import Menu Page
 */
function tkm_add_csv_import_menu() {
    add_submenu_page(
        'edit.php?post_type=teacher_document',
        __('Import CSV', 'teacherske'),
        __('Import CSV', 'teacherske'),
        'edit_posts',
        'tkm-csv-import',
        'tkm_render_csv_import_page'
    );
}
add_action('admin_menu', 'tkm_add_csv_import_menu');

/**
 * Render CSV Import Page
 */
function tkm_render_csv_import_page() {
    if (!current_user_can('edit_posts')) {
        wp_die(__('You do not have permission to access this page.', 'teacherske'));
    }

    $step = isset($_GET['step']) ? intval($_GET['step']) : 1;

    ?>
    <div class="wrap tkm-csv-import-wrap">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-upload" style="color:#c92651;"></span>
            <?php _e('Import Documents from CSV', 'teacherske'); ?>
        </h1>

        <hr class="wp-header-end">

        <!-- Progress Steps -->
        <div class="tkm-import-steps">
            <div class="tkm-step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                <span class="step-number">1</span>
                <span class="step-title"><?php _e('Upload File', 'teacherske'); ?></span>
            </div>
            <div class="tkm-step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                <span class="step-number">2</span>
                <span class="step-title"><?php _e('Map Fields', 'teacherske'); ?></span>
            </div>
            <div class="tkm-step <?php echo $step >= 3 ? 'active' : ''; ?>">
                <span class="step-number">3</span>
                <span class="step-title"><?php _e('Import', 'teacherske'); ?></span>
            </div>
        </div>

        <?php
        switch ($step) {
            case 1:
                tkm_render_step_upload();
                break;
            case 2:
                tkm_render_step_mapping();
                break;
            case 3:
                tkm_render_step_import();
                break;
            default:
                tkm_render_step_upload();
        }
        ?>
    </div>

    <style>
    .tkm-csv-import-wrap { max-width: 900px; }
    .tkm-import-steps { display: flex; gap: 20px; margin: 30px 0; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .tkm-step { flex: 1; display: flex; align-items: center; gap: 12px; padding: 15px; border-radius: 6px; background: #f0f0f1; position: relative; }
    .tkm-step.active { background: #f0f6fc; border: 2px solid #c92651; }
    .tkm-step.completed { background: #e8f5e9; }
    .tkm-step.completed .step-number { background: #28a745; }
    .step-number { width: 36px; height: 36px; border-radius: 50%; background: #666; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; }
    .tkm-step.active .step-number { background: #c92651; }
    .step-title { font-weight: 600; color: #2c3338; }
    .tkm-import-box { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; }
    .tkm-file-upload-area { border: 2px dashed #c92651; border-radius: 8px; padding: 40px; text-align: center; background: #f9f9f9; margin: 20px 0; }
    .tkm-file-upload-area.dragover { background: #f0f6fc; }
    .upload-icon { font-size: 48px; color: #c92651; margin-bottom: 15px; }
    .tkm-field-mapping-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    .tkm-field-mapping-table th, .tkm-field-mapping-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    .tkm-field-mapping-table th { background: #f0f0f1; font-weight: 600; }
    .tkm-field-mapping-table select { width: 100%; }
    .tkm-required { color: #d63638; font-weight: 700; }
    .tkm-preview-rows { background: #f9f9f9; padding: 15px; border-radius: 6px; margin: 20px 0; max-height: 200px; overflow: auto; }
    .tkm-progress-bar { width: 100%; height: 40px; background: #f0f0f1; border-radius: 20px; overflow: hidden; margin: 20px 0; }
    .tkm-progress-fill { height: 100%; background: linear-gradient(90deg, #c92651, #ff6100); transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; }
    .tkm-import-status { background: #f0f6fc; padding: 20px; border-radius: 8px; border-left: 4px solid #c92651; margin: 20px 0; }
    .tkm-import-results { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
    .tkm-result-box { background: #fff; padding: 20px; border-radius: 8px; border: 2px solid #ddd; text-align: center; }
    .tkm-result-box.success { border-color: #28a745; }
    .tkm-result-box.warning { border-color: #ffc107; }
    .tkm-result-box.error { border-color: #d63638; }
    .tkm-result-number { font-size: 36px; font-weight: 700; margin-bottom: 8px; }
    .tkm-result-label { font-size: 14px; color: #666; }
    </style>
    <?php
}

/**
 * Step 1: Upload CSV File
 */
function tkm_render_step_upload() {
    ?>
    <div class="tkm-import-box">
        <h2><?php _e('Step 1: Upload Your CSV File', 'teacherske'); ?></h2>
        <p><?php _e('Upload a CSV file containing your document data. Maximum file size: 10MB. Batch size: 30 rows per batch.', 'teacherske'); ?></p>

        <form method="post" enctype="multipart/form-data" id="tkm-upload-form">
            <input type="hidden" id="tkm_csv_nonce" value="<?php echo wp_create_nonce('tkm_csv_upload'); ?>" />

            <div class="tkm-file-upload-area" id="tkm-drop-zone">
                <div class="upload-icon">📄</div>
                <h3><?php _e('Drag & Drop CSV File Here', 'teacherske'); ?></h3>
                <p><?php _e('or', 'teacherske'); ?></p>
                <input type="file" name="csv_file" id="csv-file-input" accept=".csv" style="display:none;" required>
                <button type="button" class="button button-primary button-large" id="select-file-btn">
                    <?php _e('Select CSV File', 'teacherske'); ?>
                </button>
                <p class="description" style="margin-top:15px;">
                    <?php _e('Supported format: CSV (Comma Separated Values) | Max size: 10MB', 'teacherske'); ?>
                </p>
            </div>

            <div id="file-info" style="display:none; background:#f0f6fc; padding:15px; border-radius:6px; margin:15px 0;">
                <p style="margin:0;"><strong><?php _e('Selected File:', 'teacherske'); ?></strong> <span id="file-name"></span></p>
                <p style="margin:5px 0 0;"><strong><?php _e('Size:', 'teacherske'); ?></strong> <span id="file-size"></span></p>
            </div>

            <button type="submit" class="button button-primary button-large" id="upload-btn" disabled>
                <?php _e('Continue to Field Mapping', 'teacherske'); ?> →
            </button>
        </form>

        <hr style="margin:30px 0;">

        <div style="background:#fff9e6; padding:20px; border-left:4px solid #ffc107; border-radius:6px;">
            <h3 style="margin-top:0;">💡 <?php _e('Don\'t have a CSV file?', 'teacherske'); ?></h3>
            <p><?php _e('Download our template to get started with the correct format.', 'teacherske'); ?></p>
            <a href="<?php echo admin_url('admin-ajax.php?action=tkm_download_template'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-download" style="margin-top:3px;"></span>
                <?php _e('Download CSV Template', 'teacherske'); ?>
            </a>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var $dropZone = $('#tkm-drop-zone');
        var $fileInput = $('#csv-file-input');
        var $selectBtn = $('#select-file-btn');
        var $uploadBtn = $('#upload-btn');
        var $fileInfo = $('#file-info');

        // Click to select file
        $selectBtn.on('click', function() {
            $fileInput.click();
        });

        // File selected
        $fileInput.on('change', function() {
            if (this.files && this.files[0]) {
                handleFile(this.files[0]);
            }
        });

        // Drag and drop
        $dropZone.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });

        $dropZone.on('dragleave', function() {
            $(this).removeClass('dragover');
        });

        $dropZone.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');

            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                $fileInput[0].files = files;
                handleFile(files[0]);
            }
        });

        function handleFile(file) {
            // Validate file type
            if (!file.name.endsWith('.csv')) {
                alert('<?php _e('Please select a CSV file', 'teacherske'); ?>');
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10485760) {
                alert('<?php _e('File is too large. Maximum size is 10MB.', 'teacherske'); ?>');
                return;
            }

            // Show file info
            $('#file-name').text(file.name);
            $('#file-size').text((file.size / 1024).toFixed(2) + ' KB');
            $fileInfo.show();
            $uploadBtn.prop('disabled', false);
        }
    });
    </script>
    <?php
}

/**
 * Step 2: Field Mapping
 */
function tkm_render_step_mapping() {
    // This will be populated by JavaScript after file upload
    ?>
    <div class="tkm-import-box">
        <h2><?php _e('Step 2: Map CSV Columns to Document Fields', 'teacherske'); ?></h2>
        <p><?php _e('Match your CSV columns to the corresponding document fields below.', 'teacherske'); ?></p>

        <div id="mapping-interface">
            <!-- Populated by JavaScript -->
        </div>
    </div>
    <?php
}

/**
 * Step 3: Import Progress
 */
function tkm_render_step_import() {
    ?>
    <div class="tkm-import-box">
        <h2><?php _e('Step 3: Importing Documents...', 'teacherske'); ?></h2>

        <div class="tkm-import-status">
            <p id="import-status-text"><?php _e('Preparing import...', 'teacherske'); ?></p>
        </div>

        <div class="tkm-progress-bar">
            <div class="tkm-progress-fill" id="import-progress" style="width:0%;">0%</div>
        </div>

        <div id="import-results" style="display:none;">
            <!-- Results will be shown here -->
        </div>
    </div>
    <?php
}

/**
 * AJAX: Handle CSV Upload
 */
function tkm_ajax_upload_csv() {
    check_ajax_referer('tkm_csv_upload', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied', 'teacherske')));
    }

    // Check if file was uploaded
    if (empty($_FILES['csv_file'])) {
        wp_send_json_error(array('message' => __('No file uploaded', 'teacherske')));
    }

    $file = $_FILES['csv_file'];

    // Validate file type
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_ext !== 'csv') {
        wp_send_json_error(array('message' => __('Please upload a CSV file', 'teacherske')));
    }

    // Validate file size (10MB)
    if ($file['size'] > 10485760) {
        wp_send_json_error(array('message' => __('File too large. Maximum 10MB', 'teacherske')));
    }

    // Move to temp directory
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/tkm-csv-temp/';

    // Create temp directory if not exists
    if (!file_exists($temp_dir)) {
        wp_mkdir_p($temp_dir);
    }

    // Generate unique filename
    $temp_filename = 'import_' . uniqid() . '_' . time() . '.csv';
    $temp_filepath = $temp_dir . $temp_filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $temp_filepath)) {
        wp_send_json_error(array('message' => __('Failed to save uploaded file', 'teacherske')));
    }

    // Get CSV headers
    $importer = new TKM_Bulk_Importer();
    $headers = $importer->get_csv_headers($temp_filepath);

    if (!$headers) {
        unlink($temp_filepath);
        wp_send_json_error(array('message' => __('Could not read CSV headers', 'teacherske')));
    }

    // Count total rows
    $row_count = 0;
    if (($handle = fopen($temp_filepath, 'r')) !== false) {
        fgetcsv($handle); // Skip header
        while (fgetcsv($handle) !== false) {
            $row_count++;
        }
        fclose($handle);
    }

    // Store file path in transient (expires in 1 hour)
    $transient_key = 'tkm_csv_import_' . get_current_user_id();
    set_transient($transient_key, $temp_filepath, HOUR_IN_SECONDS);

    // Auto-detect field mapping
    $auto_mapping = tkm_auto_detect_mapping($headers);

    wp_send_json_success(array(
        'headers' => $headers,
        'row_count' => $row_count,
        'auto_mapping' => $auto_mapping,
        'temp_key' => $transient_key
    ));
}
add_action('wp_ajax_tkm_upload_csv', 'tkm_ajax_upload_csv');

/**
 * Auto-detect CSV field mapping
 */
function tkm_auto_detect_mapping($headers) {
    $mapping = array();

    // Mapping patterns (case-insensitive)
    $patterns = array(
        'title' => array('title', 'name', 'document', 'doc_name', 'filename'),
        'description' => array('description', 'desc', 'details', 'about', 'summary'),
        'file' => array('file', 'url', 'link', 'fileurl', 'file_url', 'document_url', 'download'),
        'level' => array('level', 'education_level', 'edu_level', 'class_level'),
        'grade' => array('grade', 'class', 'year', 'std', 'form'),
        'subject' => array('subject', 'topic', 'course', 'category'),
        'version' => array('version', 'edition', 'year', 'release'),
        'author' => array('author', 'creator', 'uploaded_by', 'by')
    );

    foreach ($headers as $header) {
        $header_lower = strtolower(trim($header));

        foreach ($patterns as $field => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($header_lower, $keyword) !== false) {
                    if (!isset($mapping[$field])) {
                        $mapping[$field] = $header;
                    }
                    break 2;
                }
            }
        }
    }

    return $mapping;
}

/**
 * AJAX: Process Batch Import
 */
function tkm_ajax_batch_import() {
    check_ajax_referer('tkm_csv_import', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied', 'teacherske')));
    }

    // Get file path from transient
    $transient_key = sanitize_text_field($_POST['temp_key']);
    $file_path = get_transient($transient_key);

    if (!$file_path || !file_exists($file_path)) {
        wp_send_json_error(array('message' => __('CSV file not found. Please upload again.', 'teacherske')));
    }

    // Get field mapping
    $field_mapping = isset($_POST['mapping']) ? json_decode(stripslashes($_POST['mapping']), true) : array();

    // Get batch parameters
    $batch_start = isset($_POST['batch_start']) ? intval($_POST['batch_start']) : 0;
    $batch_size = 30; // Process 30 rows per batch

    // Read CSV and process batch
    $rows = array();
    $current_row = 0;

    if (($handle = fopen($file_path, 'r')) !== false) {
        $headers = fgetcsv($handle); // Get headers

        // Skip to batch start
        while ($current_row < $batch_start && fgetcsv($handle) !== false) {
            $current_row++;
        }

        // Read batch rows
        $batch_count = 0;
        while ($batch_count < $batch_size && ($data = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty(array_filter($data))) {
                $current_row++;
                continue;
            }

            // Combine headers with data
            $row = array();
            foreach ($headers as $i => $col) {
                $row[$col] = isset($data[$i]) ? $data[$i] : '';
            }

            $rows[] = $row;
            $batch_count++;
            $current_row++;
        }

        fclose($handle);
    }

    // Process each row
    $importer = new TKM_Bulk_Importer();
    $imported = 0;
    $errors = array();

    foreach ($rows as $index => $row) {
        $row_number = $batch_start + $index + 2; // +2 for header and 1-based index

        $result = $importer->import_row($row, $field_mapping);

        if ($result['success']) {
            $imported++;
        } else {
            $errors[] = sprintf(__('Row %d: %s', 'teacherske'), $row_number, $result['error']);
        }
    }

    // Check if more batches remain
    $total_processed = $batch_start + count($rows);
    $has_more = count($rows) === $batch_size;

    // Clean up if done
    if (!$has_more) {
        unlink($file_path);
        delete_transient($transient_key);
    }

    wp_send_json_success(array(
        'imported' => $imported,
        'errors' => $errors,
        'total_processed' => $total_processed,
        'has_more' => $has_more,
        'next_batch_start' => $total_processed
    ));
}
add_action('wp_ajax_tkm_batch_import', 'tkm_ajax_batch_import');

/**
 * Expose import_row method for AJAX
 */
if (!class_exists('TKM_Bulk_Importer')) {
    require_once TKM_DIR . 'includes/class-bulk-importer.php';
}

/**
 * Enqueue CSV Import Assets
 */
function tkm_enqueue_csv_import_assets() {
    $screen = get_current_screen();

    if (!$screen || $screen->id !== 'teacher_document_page_tkm-csv-import') {
        return;
    }

    // Enqueue CSV import JavaScript
    wp_enqueue_script(
        'tkm-csv-import',
        TKM_URL . 'assets/js/csv-import.js',
        array('jquery'),
        TKM_VERSION,
        true
    );

    // Localize script
    wp_localize_script('tkm-csv-import', 'tkmCSV', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tkm_csv_import'),
        'documentsUrl' => admin_url('edit.php?post_type=teacher_document')
    ));
}
add_action('admin_enqueue_scripts', 'tkm_enqueue_csv_import_assets');
