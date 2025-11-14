<?php
/**
 * Download Tracker Class
 * 
 * Handles download counting with IP-based spam prevention
 * Prevents multiple counts from same IP within cooldown period
 */

if (!defined('ABSPATH')) exit;

class TKM_Download_Tracker {
    
    /**
     * Cooldown period in seconds (1 hour)
     */
    const COOLDOWN_PERIOD = 3600; // 1 hour
    
    /**
     * Track a download
     *
     * @param int $post_id Post ID
     * @return bool True if counted, false if skipped
     */
    public function track_download($post_id) {
        // Verify post exists and is correct type
        if (get_post_type($post_id) !== 'teacher_document') {
            return false;
        }

        // ALWAYS increment download count (tracking is permanent)
        $this->increment_count($post_id);

        // Optional: Record IP for spam prevention (but don't block counting)
        if (tkm_get_setting('track_by_ip', 'yes') === 'yes') {
            $user_ip = $this->get_user_ip();

            if ($user_ip && !$this->is_recent_download($post_id, $user_ip)) {
                // Record this IP and timestamp for analytics only
                $this->record_ip($post_id, $user_ip);
            }
        }

        // Fire action hook for tracking integrations
        do_action('tkm_download_tracked', $post_id, $this->get_user_ip());

        return true;
    }
    
    /**
     * Get download count for a document
     * 
     * @param int $post_id Post ID
     * @return int Download count
     */
    public function get_download_count($post_id) {
        $count = get_post_meta($post_id, '_tkm_download_count', true);
        return $count ? intval($count) : 0;
    }
    
    /**
     * Increment download count
     * 
     * @param int $post_id Post ID
     */
    private function increment_count($post_id) {
        $current_count = $this->get_download_count($post_id);
        $new_count = $current_count + 1;
        
        update_post_meta($post_id, '_tkm_download_count', $new_count);
    }
    
    /**
     * Check if IP recently downloaded this document
     * 
     * @param int $post_id Post ID
     * @param string $ip IP address
     * @return bool True if recent download exists
     */
    private function is_recent_download($post_id, $ip) {
        $tracked_ips = get_post_meta($post_id, '_tkm_download_ips', true);
        
        if (!is_array($tracked_ips)) {
            $tracked_ips = array();
        }
        
        $current_time = current_time('timestamp');
        
        // Check if IP exists and is within cooldown period
        if (isset($tracked_ips[$ip])) {
            $last_download_time = intval($tracked_ips[$ip]);
            $time_diff = $current_time - $last_download_time;
            
            if ($time_diff < self::COOLDOWN_PERIOD) {
                return true; // Recent download found
            }
        }
        
        return false;
    }
    
    /**
     * Record IP and timestamp
     * 
     * @param int $post_id Post ID
     * @param string $ip IP address
     */
    private function record_ip($post_id, $ip) {
        $tracked_ips = get_post_meta($post_id, '_tkm_download_ips', true);
        
        if (!is_array($tracked_ips)) {
            $tracked_ips = array();
        }
        
        // Add/update IP with current timestamp
        $tracked_ips[$ip] = current_time('timestamp');
        
        // Clean up old entries (older than 24 hours) to keep array small
        $this->cleanup_old_ips($tracked_ips);
        
        update_post_meta($post_id, '_tkm_download_ips', $tracked_ips);
    }
    
    /**
     * Clean up old IP entries
     * 
     * @param array &$tracked_ips IP array (passed by reference)
     */
    private function cleanup_old_ips(&$tracked_ips) {
        $current_time = current_time('timestamp');
        $cleanup_threshold = 86400; // 24 hours
        
        foreach ($tracked_ips as $ip => $timestamp) {
            if (($current_time - $timestamp) > $cleanup_threshold) {
                unset($tracked_ips[$ip]);
            }
        }
    }
    
    /**
     * Get user IP address
     * 
     * @return string|false IP address or false if unavailable
     */
    private function get_user_ip() {
        // Check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        // Check for IPs passing through proxies
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can contain multiple IPs, take the first one
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
        }
        // Standard remote address
        elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            return false;
        }
        
        // Validate IP
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        return false;
    }
    
    /**
     * Reset download count for a document
     * 
     * @param int $post_id Post ID
     */
    public function reset_count($post_id) {
        update_post_meta($post_id, '_tkm_download_count', 0);
        delete_post_meta($post_id, '_tkm_download_ips');
        
        do_action('tkm_download_count_reset', $post_id);
    }
    
    /**
     * Get download statistics
     * 
     * @param int $post_id Post ID
     * @return array Statistics
     */
    public function get_stats($post_id) {
        $count = $this->get_download_count($post_id);
        $tracked_ips = get_post_meta($post_id, '_tkm_download_ips', true);
        
        if (!is_array($tracked_ips)) {
            $tracked_ips = array();
        }
        
        return array(
            'total_downloads' => $count,
            'unique_ips' => count($tracked_ips),
            'last_download' => !empty($tracked_ips) ? max($tracked_ips) : null
        );
    }
    
    /**
     * Get global statistics
     * 
     * @return array Global stats
     */
    public static function get_global_stats() {
        global $wpdb;
        
        // Total downloads across all documents
        $total_downloads = $wpdb->get_var(
            "SELECT SUM(CAST(meta_value AS UNSIGNED)) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_tkm_download_count'"
        );
        
        // Total documents with downloads
        $documents_with_downloads = $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_tkm_download_count' 
            AND CAST(meta_value AS UNSIGNED) > 0"
        );
        
        // Most downloaded document
        $most_downloaded = $wpdb->get_row(
            "SELECT post_id, MAX(CAST(meta_value AS UNSIGNED)) as downloads 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_tkm_download_count' 
            GROUP BY post_id 
            ORDER BY downloads DESC 
            LIMIT 1"
        );
        
        return array(
            'total_downloads' => $total_downloads ? intval($total_downloads) : 0,
            'documents_with_downloads' => $documents_with_downloads ? intval($documents_with_downloads) : 0,
            'most_downloaded_id' => $most_downloaded ? intval($most_downloaded->post_id) : null,
            'most_downloaded_count' => $most_downloaded ? intval($most_downloaded->downloads) : 0
        );
    }
    
    /**
     * Get top downloaded documents
     * 
     * @param int $limit Number of documents to return
     * @return array Array of document data
     */
    public static function get_top_downloads($limit = 10) {
        $args = array(
            'post_type' => 'teacher_document',
            'posts_per_page' => $limit,
            'meta_key' => '_tkm_download_count',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_tkm_download_count',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC'
                )
            )
        );
        
        $query = new WP_Query($args);
        $results = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $post_id = get_the_ID();
                $results[] = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'downloads' => intval(get_post_meta($post_id, '_tkm_download_count', true)),
                    'grade' => get_post_meta($post_id, '_tkm_grade', true)
                );
            }
            wp_reset_postdata();
        }
        
        return $results;
    }
}