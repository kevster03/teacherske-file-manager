<?php
/**
 * Bulk CSV Importer Class
 * 
 * Handles CSV file imports with field mapping
 * Allows bulk creation of documents from spreadsheet data
 */

if (!defined('ABSPATH')) exit;

class TKM_Bulk_Importer {
    
    /**
     * Required fields for import
     */
    const REQUIRED_FIELDS = array('title', 'file', 'level', 'grade');
    
    /**
     * Optional fields
     */
    const OPTIONAL_FIELDS = array('description', 'version', 'subject', 'author');
    
    /**
     * Maximum file size (10MB)
     */
    const MAX_FILE_SIZE = 10485760;
    
    /**
     * Process CSV import
     * 
     * @param string $file_path Path to CSV file
     * @param array $field_mapping Column to field mapping
     * @return array Result with success count and errors
     */
    public function import_csv($file_path, $field_mapping) {
        // Validate file
        if (!file_exists($file_path)) {
            return array(
                'success' => false,
                'error' => __('File not found', 'teacherske')
            );
        }
        
        if (filesize($file_path) > self::MAX_FILE_SIZE) {
            return array(
                'success' => false,
                'error' => __('File too large. Maximum 10MB.', 'teacherske')
            );
        }
        
        // Read CSV
        $rows = $this->read_csv($file_path);
        
        if (empty($rows)) {
            return array(
                'success' => false,
                'error' => __('CSV file is empty or unreadable', 'teacherske')
            );
        }
        
        // Validate mapping
        $validation = $this->validate_mapping($field_mapping);
        if (!$validation['valid']) {
            return array(
                'success' => false,
                'error' => $validation['error']
            );
        }
        
        // Process each row
        $imported = 0;
        $errors = array();
        
        foreach ($rows as $index => $row) {
            $row_number = $index + 2; // +2 because index starts at 0 and first row is header
            
            $result = $this->import_row($row, $field_mapping);
            
            if ($result['success']) {
                $imported++;
            } else {
                $errors[] = sprintf(
                    __('Row %d: %s', 'teacherske'),
                    $row_number,
                    $result['error']
                );
            }
        }
        
        return array(
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'total_rows' => count($rows)
        );
    }
    
    /**
     * Read CSV file
     * 
     * @param string $file_path File path
     * @return array Rows of data
     */
    private function read_csv($file_path) {
        $rows = array();
        
        if (($handle = fopen($file_path, 'r')) !== false) {
            // Skip header row
            $header = fgetcsv($handle, 0, ',');
            
            // Read data rows
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                // Skip empty rows
                if (empty(array_filter($data))) {
                    continue;
                }
                
                // Combine header with data
                $row = array();
                foreach ($header as $i => $col) {
                    $row[$col] = isset($data[$i]) ? $data[$i] : '';
                }
                
                $rows[] = $row;
            }
            
            fclose($handle);
        }
        
        return $rows;
    }
    
    /**
     * Validate field mapping
     * 
     * @param array $mapping Field mapping
     * @return array Validation result
     */
    private function validate_mapping($mapping) {
        // Check required fields are mapped
        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($mapping[$field])) {
                return array(
                    'valid' => false,
                    'error' => sprintf(__('Required field "%s" is not mapped', 'teacherske'), $field)
                );
            }
        }
        
        return array('valid' => true);
    }
    
    /**
     * Import single row
     *
     * @param array $row CSV row data
     * @param array $mapping Field mapping
     * @return array Result
     */
    public function import_row($row, $mapping) {
        // Extract mapped data
        $data = array();
        
        foreach ($mapping as $field => $column) {
            if (!empty($column) && isset($row[$column])) {
                $data[$field] = trim($row[$column]);
            }
        }
        
        // Validate required fields
        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($data[$field])) {
                return array(
                    'success' => false,
                    'error' => sprintf(__('Missing required field: %s', 'teacherske'), $field)
                );
            }
        }
        
        // Validate level
        $levels = tkm_get_levels();
        if (!isset($levels[$data['level']])) {
            return array(
                'success' => false,
                'error' => sprintf(__('Invalid level: %s', 'teacherske'), $data['level'])
            );
        }
        
        // Validate grade
        if (!in_array($data['grade'], $levels[$data['level']]['grades'])) {
            return array(
                'success' => false,
                'error' => sprintf(__('Grade "%s" not valid for level "%s"', 'teacherske'), $data['grade'], $data['level'])
            );
        }
        
        // Validate file URL
        if (!filter_var($data['file'], FILTER_VALIDATE_URL)) {
            return array(
                'success' => false,
                'error' => __('Invalid file URL', 'teacherske')
            );
        }
        
        // Create post
        $post_data = array(
            'post_title' => sanitize_text_field($data['title']),
            'post_type' => 'teacher_document',
            'post_status' => 'draft', // Import as draft for review
            'post_content' => ''
        );
        
        // Set author if provided
        if (!empty($data['author'])) {
            $user = get_user_by('login', $data['author']);
            if (!$user) {
                $user = get_user_by('email', $data['author']);
            }
            
            if ($user) {
                $post_data['post_author'] = $user->ID;
            }
        }
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return array(
                'success' => false,
                'error' => $post_id->get_error_message()
            );
        }
        
        // Add meta data
        update_post_meta($post_id, '_tkm_file', esc_url_raw($data['file']));
        update_post_meta($post_id, '_tkm_level', sanitize_key($data['level']));
        update_post_meta($post_id, '_tkm_grade', sanitize_text_field($data['grade']));
        
        // Optional fields
        if (!empty($data['description'])) {
            update_post_meta($post_id, '_tkm_description', wp_kses_post($data['description']));
        }
        
        if (!empty($data['version'])) {
            update_post_meta($post_id, '_tkm_version', sanitize_text_field($data['version']));
        }
        
        // Auto-detect file metadata
        $ext = tkm_get_file_extension($data['file']);
        if ($ext) {
            update_post_meta($post_id, '_tkm_file_ext', $ext);
        }
        
        $size = tkm_calculate_file_size($data['file']);
        if ($size) {
            update_post_meta($post_id, '_tkm_file_size', $size);
        }
        
        // Add subject
        if (!empty($data['subject'])) {
            $subjects = array_map('trim', explode(',', $data['subject']));
            wp_set_post_terms($post_id, $subjects, 'subject');
        }
        
        // Initialize download tracking
        update_post_meta($post_id, '_tkm_download_count', 0);
        
        return array(
            'success' => true,
            'post_id' => $post_id
        );
    }
    
    /**
     * Get CSV headers
     * 
     * @param string $file_path File path
     * @return array|false Headers or false on error
     */
    public function get_csv_headers($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }
        
        if (($handle = fopen($file_path, 'r')) !== false) {
            $headers = fgetcsv($handle, 0, ',');
            fclose($handle);
            return $headers;
        }
        
        return false;
    }
    
    /**
     * Get sample CSV template
     * 
     * @return string CSV content
     */
    public static function get_csv_template() {
        $headers = array(
            'title',
            'description',
            'file',
            'level',
            'grade',
            'version',
            'subject',
            'author'
        );
        
        $sample_row = array(
            'Grade 7 Mathematics - Algebra Notes',
            'Comprehensive notes covering all algebra topics',
            'https://example.com/files/math-grade7.pdf',
            'junior_secondary',
            'Grade 7',
            '2026 Edition',
            'Mathematics',
            'admin'
        );
        
        $csv = implode(',', $headers) . "\n";
        $csv .= implode(',', array_map(function($val) {
            return '"' . str_replace('"', '""', $val) . '"';
        }, $sample_row));
        
        return $csv;
    }
    
    /**
     * Download CSV template
     */
    public static function download_template() {
        $csv = self::get_csv_template();
        $filename = 'teacherske-import-template-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $csv;
        exit;
    }
    
    /**
     * Get field descriptions
     * 
     * @return array Field descriptions
     */
    public static function get_field_descriptions() {
        return array(
            'title' => __('Document title (required)', 'teacherske'),
            'description' => __('Brief description of the document', 'teacherske'),
            'file' => __('Full URL to the file (required)', 'teacherske'),
            'level' => __('Education level key (required): early_years, lower_primary, upper_primary, junior_secondary, senior_secondary', 'teacherske'),
            'grade' => __('Specific grade (required): PP1, PP2, Grade 1, Grade 2, etc.', 'teacherske'),
            'version' => __('Document version: 2026 Edition, 2027 Edition, etc.', 'teacherske'),
            'subject' => __('Subject name (must match existing subjects)', 'teacherske'),
            'author' => __('WordPress username or email of document author', 'teacherske')
        );
    }
}

/**
 * AJAX: Download CSV Template
 */
function tkm_ajax_download_template() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied', 'teacherske'));
    }

    TKM_Bulk_Importer::download_template();
}
add_action('wp_ajax_tkm_download_template', 'tkm_ajax_download_template');