<?php
/**
 * Content Blocks Diagnostic Tool
 * Access: wp-admin/admin.php?page=tkm-blocks-debug
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=teacher_document',
        'Content Blocks Debug',
        '🔧 Debug Blocks',
        'manage_options',
        'tkm-blocks-debug',
        'tkm_render_debug_page'
    );
});

function tkm_render_debug_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tkm_content_blocks';

    echo '<div class="wrap"><h1>🔧 Content Blocks Diagnostic</h1>';

    // Test 1: Check if table exists
    echo '<h2>Test 1: Database Table</h2>';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

    if ($table_exists) {
        echo '<p style="color:green;">✅ Table exists: ' . $table_name . '</p>';

        // Show table structure
        $columns = $wpdb->get_results("DESCRIBE $table_name");
        echo '<details><summary>View table structure</summary><pre>';
        print_r($columns);
        echo '</pre></details>';

        // Count rows
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo '<p>Total blocks in database: <strong>' . $count . '</strong></p>';

        // Show all blocks
        $blocks = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT 10");
        if ($blocks) {
            echo '<h3>Recent Blocks:</h3><table class="wp-list-table widefat"><thead><tr><th>ID</th><th>Title</th><th>Type</th><th>Active</th><th>Created</th></tr></thead><tbody>';
            foreach ($blocks as $block) {
                echo '<tr>';
                echo '<td>' . $block->id . '</td>';
                echo '<td>' . esc_html($block->block_title) . '</td>';
                echo '<td>' . $block->block_type . '</td>';
                echo '<td>' . ($block->active ? 'Yes' : 'No') . '</td>';
                echo '<td>' . $block->created_date . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p style="color:orange;">⚠️ No blocks found in database</p>';
        }
    } else {
        echo '<p style="color:red;">❌ Table does not exist!</p>';
        echo '<p><a href="#" onclick="tkm_create_table(); return false;" class="button button-primary">Create Table Now</a></p>';
    }

    // Test 2: Check WPDB errors
    echo '<h2>Test 2: Database Errors</h2>';
    if ($wpdb->last_error) {
        echo '<p style="color:red;">❌ Last error: ' . $wpdb->last_error . '</p>';
    } else {
        echo '<p style="color:green;">✅ No database errors</p>';
    }

    // Test 3: Check WordPress debug mode
    echo '<h2>Test 3: Debug Mode</h2>';
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo '<p style="color:green;">✅ WP_DEBUG is enabled</p>';
    } else {
        echo '<p style="color:orange;">⚠️ WP_DEBUG is disabled (errors might be hidden)</p>';
    }

    // Test 4: Test insert
    echo '<h2>Test 4: Test Insert</h2>';
    if (isset($_GET['test_insert']) && $_GET['test_insert'] === '1') {
        $wpdb->show_errors();
        $result = $wpdb->insert($table_name, array(
            'block_title' => 'Test Block - ' . date('Y-m-d H:i:s'),
            'block_type' => 'generic',
            'block_content' => '<p>This is a test block created by the diagnostic tool.</p>',
            'has_schema' => 0,
            'display_order' => 0,
            'active' => 1
        ));

        if ($result) {
            echo '<p style="color:green;">✅ Test insert successful! Block ID: ' . $wpdb->insert_id . '</p>';
        } else {
            echo '<p style="color:red;">❌ Test insert failed!</p>';
            echo '<p>Error: ' . $wpdb->last_error . '</p>';
        }
        $wpdb->hide_errors();
    } else {
        echo '<p><a href="' . admin_url('admin.php?page=tkm-blocks-debug&test_insert=1') . '" class="button">Run Test Insert</a></p>';
    }

    // Test 5: Check post_content sync
    echo '<h2>Test 5: Post Content Sync</h2>';
    $recent_docs = get_posts(array(
        'post_type' => 'teacher_document',
        'posts_per_page' => 5,
        'post_status' => 'publish'
    ));

    if ($recent_docs) {
        echo '<table class="wp-list-table widefat"><thead><tr><th>Document</th><th>Description Meta</th><th>Post Content</th><th>Status</th></tr></thead><tbody>';
        foreach ($recent_docs as $doc) {
            $description = get_post_meta($doc->ID, '_tkm_description', true);
            $has_content = !empty($doc->post_content);

            echo '<tr>';
            echo '<td><a href="' . get_edit_post_link($doc->ID) . '">' . esc_html($doc->post_title) . '</a></td>';
            echo '<td>' . (strlen($description) . ' chars') . '</td>';
            echo '<td>' . (strlen($doc->post_content) . ' chars') . '</td>';

            if ($has_content) {
                echo '<td style="color:green;">✅ Synced</td>';
            } else {
                echo '<td style="color:orange;">⚠️ No post_content</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    // Test 6: Bulk sync all documents
    echo '<h2>Test 6: Bulk Sync to post_content</h2>';
    if (isset($_GET['bulk_sync']) && $_GET['bulk_sync'] === '1') {
        $all_docs = get_posts(array(
            'post_type' => 'teacher_document',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft')
        ));

        $synced = 0;
        foreach ($all_docs as $doc) {
            $description = get_post_meta($doc->ID, '_tkm_description', true);
            if ($description) {
                wp_update_post(array(
                    'ID' => $doc->ID,
                    'post_content' => $description
                ));
                $synced++;
            }
        }

        echo '<p style="color:green;">✅ Synced ' . $synced . ' documents to post_content for RankMath scanning!</p>';
        echo '<p><em>RankMath should now detect keywords, headings, and content in your descriptions.</em></p>';
    } else {
        echo '<p>This will copy all document descriptions to the post_content field so RankMath can scan them.</p>';
        echo '<p><a href="' . admin_url('admin.php?page=tkm-blocks-debug&bulk_sync=1') . '" class="button button-primary">Sync All Documents Now</a></p>';
    }

    echo '<hr><p><a href="' . admin_url('admin.php?page=tkm-content-blocks') . '" class="button">← Back to Content Blocks</a></p>';
    echo '</div>';
}
