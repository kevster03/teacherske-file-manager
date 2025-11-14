<?php
/**
 * View Tracker Class
 *
 * Tracks page views for documents with IP-based spam prevention
 * Separate from download tracking for analytics purposes
 */

if (!defined('ABSPATH')) exit;

class TKM_View_Tracker {

    /**
     * Cooldown period in seconds (30 minutes - longer than downloads)
     */
    const COOLDOWN_PERIOD = 1800; // 30 minutes

    /**
     * Track a page view
     *
     * @param int $post_id Post ID
     * @return bool True if counted, false if skipped
     */
    public function track_view($post_id) {
        // Verify post exists and is correct type
        if (get_post_type($post_id) !== 'teacher_document') {
            return false;
        }

        // Check if view tracking is enabled
        if (tkm_get_setting('enable_view_tracking', 'yes') !== 'yes') {
            return false;
        }

        // Check if IP tracking is enabled
        if (tkm_get_setting('track_by_ip', 'yes') === 'yes') {
            $user_ip = $this->get_user_ip();

            if (!$user_ip) {
                // Can't get IP, still count but don't track
                $this->increment_count($post_id);
                return true;
            }

            // Check if this IP recently viewed
            if ($this->is_recent_view($post_id, $user_ip)) {
                // Skip counting, but view is still displayed
                return false;
            }

            // Record this IP and timestamp
            $this->record_ip($post_id, $user_ip);
        }

        // Increment view count
        $this->increment_count($post_id);

        // Fire action hook for tracking integrations
        do_action('tkm_view_tracked', $post_id, $this->get_user_ip());

        return true;
    }

    /**
     * Get view count for a document
     *
     * @param int $post_id Post ID
     * @return int View count
     */
    public function get_view_count($post_id) {
        $count = get_post_meta($post_id, '_tkm_view_count', true);
        return $count ? intval($count) : 0;
    }

    /**
     * Increment view count
     *
     * @param int $post_id Post ID
     */
    private function increment_count($post_id) {
        $current_count = $this->get_view_count($post_id);
        $new_count = $current_count + 1;

        update_post_meta($post_id, '_tkm_view_count', $new_count);
    }

    /**
     * Check if IP recently viewed this document
     *
     * @param int $post_id Post ID
     * @param string $ip IP address
     * @return bool True if recent view exists
     */
    private function is_recent_view($post_id, $ip) {
        $tracked_ips = get_post_meta($post_id, '_tkm_view_ips', true);

        if (!is_array($tracked_ips)) {
            $tracked_ips = array();
        }

        $current_time = current_time('timestamp');

        // Check if IP exists and is within cooldown period
        if (isset($tracked_ips[$ip])) {
            $last_view_time = intval($tracked_ips[$ip]);
            $time_diff = $current_time - $last_view_time;

            if ($time_diff < self::COOLDOWN_PERIOD) {
                return true; // Recent view found
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
        $tracked_ips = get_post_meta($post_id, '_tkm_view_ips', true);

        if (!is_array($tracked_ips)) {
            $tracked_ips = array();
        }

        // Add/update IP with current timestamp
        $tracked_ips[$ip] = current_time('timestamp');

        // Clean up old entries (older than 24 hours) to keep array small
        $this->cleanup_old_ips($tracked_ips);

        update_post_meta($post_id, '_tkm_view_ips', $tracked_ips);
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
     * Reset view count for a document
     *
     * @param int $post_id Post ID
     */
    public function reset_count($post_id) {
        update_post_meta($post_id, '_tkm_view_count', 0);
        delete_post_meta($post_id, '_tkm_view_ips');

        do_action('tkm_view_count_reset', $post_id);
    }

    /**
     * Get view statistics
     *
     * @param int $post_id Post ID
     * @return array Statistics
     */
    public function get_stats($post_id) {
        $count = $this->get_view_count($post_id);
        $tracked_ips = get_post_meta($post_id, '_tkm_view_ips', true);

        if (!is_array($tracked_ips)) {
            $tracked_ips = array();
        }

        return array(
            'total_views' => $count,
            'unique_ips' => count($tracked_ips),
            'last_view' => !empty($tracked_ips) ? max($tracked_ips) : null
        );
    }

    /**
     * Get global statistics
     *
     * @return array Global stats
     */
    public static function get_global_stats() {
        global $wpdb;

        // Total views across all documents
        $total_views = $wpdb->get_var(
            "SELECT SUM(CAST(meta_value AS UNSIGNED))
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_tkm_view_count'"
        );

        // Total documents with views
        $documents_with_views = $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id)
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_tkm_view_count'
            AND CAST(meta_value AS UNSIGNED) > 0"
        );

        // Most viewed document
        $most_viewed = $wpdb->get_row(
            "SELECT post_id, MAX(CAST(meta_value AS UNSIGNED)) as views
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_tkm_view_count'
            GROUP BY post_id
            ORDER BY views DESC
            LIMIT 1"
        );

        return array(
            'total_views' => $total_views ? intval($total_views) : 0,
            'documents_with_views' => $documents_with_views ? intval($documents_with_views) : 0,
            'most_viewed_id' => $most_viewed ? intval($most_viewed->post_id) : null,
            'most_viewed_count' => $most_viewed ? intval($most_viewed->views) : 0
        );
    }
}
