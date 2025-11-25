<?php
/**
 * Helper Functions
 * 
 * Utility functions used throughout the plugin
 * All functions prefixed with tkm_ to avoid conflicts
 */

if (!defined('ABSPATH')) exit;

/**
 * Get CBC Education Levels with Grades
 * 
 * @return array Associative array of levels with their grades
 */
function tkm_get_levels() {
    return array(
        'early_years' => array(
            'label' => __('Early Years / Pre-Primary', 'teacherske'),
            'grades' => array('Playgroup', 'PP1', 'PP2')
        ),
        'lower_primary' => array(
            'label' => __('Lower Primary', 'teacherske'),
            'grades' => array('Grade 1', 'Grade 2', 'Grade 3')
        ),
        'upper_primary' => array(
            'label' => __('Upper Primary', 'teacherske'),
            'grades' => array('Grade 4', 'Grade 5', 'Grade 6')
        ),
        'junior_secondary' => array(
            'label' => __('Junior Secondary', 'teacherske'),
            'grades' => array('Grade 7', 'Grade 8', 'Grade 9')
        ),
        'senior_secondary' => array(
            'label' => __('Senior Secondary', 'teacherske'),
            'grades' => array('Grade 10', 'Grade 11', 'Grade 12')
        )
    );
}

/**
 * Get Version Options (Year Editions)
 * 
 * @return array Array of version strings
 */
function tkm_get_versions() {
    $start = intval(tkm_get_setting('version_start', 2025));
    $end = intval(tkm_get_setting('version_end', 2050));
    
    $versions = array();
    for ($year = $start; $year <= $end; $year++) {
        $versions[] = $year . ' Edition';
    }
    
    return $versions;
}

/**
 * Get Subjects for a Specific Level
 * 
 * @param string $level Level key (e.g., 'upper_primary')
 * @return array Array of subject names
 */
function tkm_get_subjects_for_level($level) {
    $all_subjects = get_option('tkm_subjects_by_level', array());
    
    if (isset($all_subjects[$level]) && is_array($all_subjects[$level])) {
        return $all_subjects[$level];
    }
    
    return array();
}

/**
 * Get All Subjects Organized by Level
 * 
 * @return array Subjects organized by level
 */
function tkm_get_all_subjects_by_level() {
    return get_option('tkm_subjects_by_level', array());
}

/**
 * Save Subject for a Document (as meta field)
 * 
 * @param int $post_id Post ID
 * @param string $subject Subject name
 * @return bool Success
 */
function tkm_save_document_subject($post_id, $subject) {
    if (empty($subject)) {
        delete_post_meta($post_id, '_tkm_subject');
        return true;
    }
    return update_post_meta($post_id, '_tkm_subject', sanitize_text_field($subject));
}

/**
 * Get Subject for a Document
 * 
 * @param int $post_id Post ID
 * @return string Subject name
 */
function tkm_get_document_subject($post_id) {
    return get_post_meta($post_id, '_tkm_subject', true);
}

/**
 * Get File Type Label from Extension
 * 
 * @param string $ext File extension
 * @return string Readable file type label
 */
function tkm_get_file_type_label($ext) {
    $ext = strtolower($ext);
    
    $map = array(
        'pdf' => 'PDF',
        'doc' => 'Word Document',
        'docx' => 'Word Document',
        'ppt' => 'PowerPoint',
        'pptx' => 'PowerPoint',
        'xls' => 'Excel Spreadsheet',
        'xlsx' => 'Excel Spreadsheet',
        'zip' => 'ZIP Archive',
        'rar' => 'RAR Archive',
        'txt' => 'Text File',
        'csv' => 'CSV File',
        'mp4' => 'Video',
        'mp3' => 'Audio',
        'jpg' => 'Image',
        'jpeg' => 'Image',
        'png' => 'Image',
        'gif' => 'Image'
    );
    
    return isset($map[$ext]) ? $map[$ext] : strtoupper($ext);
}

/**
 * Get Plugin Setting with Default
 * 
 * @param string $key Setting key (without tkm_ prefix)
 * @param mixed $default Default value if setting not found
 * @return mixed Setting value
 */
function tkm_get_setting($key, $default = '') {
    return get_option('tkm_' . $key, $default);
}

/**
 * Sanitize Hex Color
 * 
 * @param string $color Hex color code
 * @return string Sanitized hex color or default
 */
function tkm_sanitize_color($color) {
    if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
        return $color;
    }
    return '#c92651'; // Default brand color
}

/**
 * Format File Size
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted size string
 */
function tkm_format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Get Allowed File Types for Upload
 * 
 * @return array Array of allowed extensions
 */
function tkm_get_allowed_file_types() {
    $types = array(
        'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx',
        'txt', 'csv', 'zip', 'rar', 'mp4', 'mp3',
        'jpg', 'jpeg', 'png', 'gif'
    );
    
    return apply_filters('tkm_allowed_file_types', $types);
}

/**
 * Check if File Type is Allowed
 * 
 * @param string $ext File extension
 * @return bool True if allowed
 */
function tkm_is_allowed_file_type($ext) {
    $allowed = tkm_get_allowed_file_types();
    return in_array(strtolower($ext), $allowed);
}

/**
 * Get File Extension from URL
 * 
 * @param string $url File URL
 * @return string File extension (lowercase)
 */
function tkm_get_file_extension($url) {
    $path = parse_url($url, PHP_URL_PATH);
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    return strtolower($ext);
}

/**
 * Calculate File Size from URL (if local)
 * 
 * @param string $url File URL
 * @return int|false File size in bytes or false
 */
function tkm_calculate_file_size($url) {
    // Only works for local files
    if (strpos($url, home_url()) === false) {
        return false;
    }
    
    $upload_dir = wp_upload_dir();
    $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
    
    if (file_exists($file_path)) {
        return filesize($file_path);
    }
    
    return false;
}

/**
 * Get Fallback Image URL
 * 
 * @return string Image URL
 */
function tkm_get_fallback_image() {
    $custom = tkm_get_setting('fallback_image', '');
    
    if ($custom && filter_var($custom, FILTER_VALIDATE_URL)) {
        return $custom;
    }
    
    // Return default placeholder
    return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect fill="%23f0f0f0" width="400" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" fill="%23999" font-size="18" font-family="Arial"%3EDocument Preview%3C/text%3E%3C/svg%3E';
}

/**
 * Get Featured Image or Fallback
 * 
 * @param int $post_id Post ID
 * @param string $size Image size
 * @return string Image URL
 */
function tkm_get_document_image($post_id, $size = 'large') {
    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, $size);
    }
    
    return tkm_get_fallback_image();
}

/**
 * Truncate Text
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add
 * @return string Truncated text
 */
function tkm_truncate_text($text, $length = 150, $suffix = '...') {
    $text = strip_tags($text);
    
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get Loader Type (bar or circular)
 * 
 * @return string Loader type
 */
function tkm_get_loader_type() {
    return tkm_get_setting('loader_type', 'bar');
}

/**
 * Check if Tracking is Enabled
 * 
 * @return bool True if enabled
 */
function tkm_is_tracking_enabled() {
    return tkm_get_setting('enable_tracking', 'yes') === 'yes';
}

/**
 * Check if Schema Markup is Enabled
 * 
 * @return bool True if enabled
 */
function tkm_is_schema_enabled() {
    return tkm_get_setting('enable_schema', 'yes') === 'yes';
}

/**
 * Get Related Files Count
 * 
 * @return int Number of related files to show
 */
function tkm_get_related_files_count() {
    return intval(tkm_get_setting('related_files_count', 8));
}

/**
 * Generate Unique Download ID
 * 
 * @return string Unique ID
 */
function tkm_generate_download_id() {
    return uniqid('tkm_', true);
}

/**
 * Log Debug Message (if WP_DEBUG is enabled)
 * 
 * @param string $message Debug message
 */
function tkm_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log('[TeachersKE FM] ' . $message);
    }
}

/**
 * Check if Current User Can Manage Documents
 * 
 * @return bool True if user can manage
 */
function tkm_user_can_manage() {
    return current_user_can('edit_posts');
}

/**
 * Get Admin Columns Configuration
 * 
 * @return array Array of column configurations
 */
function tkm_get_admin_columns() {
    return array(
        'cb' => '<input type="checkbox" />',
        'title' => __('Title', 'teacherske'),
        'file_type' => __('File', 'teacherske'),
        'level' => __('Level', 'teacherske'),
        'grade' => __('Grade', 'teacherske'),
        'subject' => __('Subject', 'teacherske'),
        'category' => __('Category', 'teacherske'),
        'downloads' => __('Downloads', 'teacherske'),
        'author' => __('Author', 'teacherske'),
        'date' => __('Date', 'teacherske')
    );
}

/**
 * Sanitize CSV Row
 * 
 * @param array $row CSV row data
 * @return array Sanitized row
 */
function tkm_sanitize_csv_row($row) {
    return array_map('sanitize_text_field', $row);
}

/**
 * Validate Required Fields
 * 
 * @param array $data Data array
 * @param array $required Required field keys
 * @return bool|WP_Error True if valid, WP_Error if invalid
 */
function tkm_validate_required_fields($data, $required) {
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return new WP_Error('missing_field', sprintf(__('Required field "%s" is missing', 'teacherske'), $field));
        }
    }
    return true;
}

/**
 * Get User IP Address
 *
 * @return string IP address
 */
function tkm_get_user_ip() {
    // Check for proxy headers first
    $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR');

    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle multiple IPs (proxy chain)
            if (strpos($ip, ',') !== false) {
                $ips = explode(',', $ip);
                $ip = trim($ips[0]);
            }
            // Validate IP format (allow private IPs for localhost testing)
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
}

/**
 * Get Daily Download Limit
 *
 * @return int Download limit (0 = unlimited)
 */
function tkm_get_daily_download_limit() {
    return intval(tkm_get_setting('daily_download_limit', 0));
}

/**
 * Check Remaining Downloads for IP
 *
 * @param string $ip IP address (optional, uses current IP if not provided)
 * @return array Array with 'remaining' and 'limit' keys
 */
function tkm_check_remaining_downloads($ip = null) {
    $limit = tkm_get_daily_download_limit();

    // If limit is 0, unlimited downloads
    if ($limit === 0) {
        return array(
            'remaining' => -1, // -1 indicates unlimited
            'limit' => 0,
            'used' => 0,
            'unlimited' => true
        );
    }

    // Get IP
    if ($ip === null) {
        $ip = tkm_get_user_ip();
    }

    // Get transient key (unique per day)
    $transient_key = 'tkm_dl_' . md5($ip . date('Y-m-d'));

    // Get current download count for this IP today
    $used = intval(get_transient($transient_key));

    $remaining = max(0, $limit - $used);

    return array(
        'remaining' => $remaining,
        'limit' => $limit,
        'used' => $used,
        'unlimited' => false
    );
}

/**
 * Increment Download Count for IP
 *
 * @param string $ip IP address (optional, uses current IP if not provided)
 * @return bool True if incremented, false if limit reached
 */
function tkm_increment_download_count($ip = null) {
    $limit = tkm_get_daily_download_limit();

    // If limit is 0, don't track (unlimited)
    if ($limit === 0) {
        return true;
    }

    // Get IP
    if ($ip === null) {
        $ip = tkm_get_user_ip();
    }

    // Check if already at limit
    $status = tkm_check_remaining_downloads($ip);
    if ($status['remaining'] <= 0) {
        return false; // At limit, cannot increment
    }

    // Get transient key (unique per day)
    $transient_key = 'tkm_dl_' . md5($ip . date('Y-m-d'));

    // Increment count
    $new_count = $status['used'] + 1;

    // Set transient to expire at end of day (midnight)
    $midnight = strtotime('tomorrow 00:00:00') - time();
    set_transient($transient_key, $new_count, $midnight);

    return true;
}
