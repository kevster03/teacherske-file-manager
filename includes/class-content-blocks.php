<?php
/**
 * Content Blocks System for SEO Enhancement
 *
 * Allows creation of reusable content blocks to boost word count and SEO
 * without modifying core template functionality
 */

if (!defined('ABSPATH')) exit;

class TKM_Content_Blocks {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'tkm_content_blocks';

        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Meta box for document selection
        add_action('add_meta_boxes', array($this, 'add_selection_meta_box'));
        add_action('save_post_teacher_document', array($this, 'save_selected_blocks'));

        // Sync to post_content for RankMath
        add_action('save_post_teacher_document', array($this, 'sync_to_post_content'), 99);

        // Schema markup for eligible blocks
        add_action('wp_head', array($this, 'output_block_schema'), 15);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=teacher_document',
            'SEO Content Blocks',
            '📝 Content Blocks',
            'manage_options',
            'tkm-content-blocks',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'tkm-content-blocks') === false && $hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        wp_enqueue_editor();
        wp_enqueue_style('tkm-blocks-admin', TKM_URL . 'assets/css/admin.css', array(), TKM_VERSION);
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';

        switch ($action) {
            case 'add':
            case 'edit':
                $this->render_edit_form();
                break;
            case 'delete':
                $this->handle_delete();
                break;
            default:
                $this->render_blocks_list();
        }
    }

    /**
     * Render blocks list
     */
    private function render_blocks_list() {
        global $wpdb;

        // Handle bulk actions
        if (isset($_POST['bulk_action']) && isset($_POST['block_ids'])) {
            $this->handle_bulk_action($_POST['bulk_action'], $_POST['block_ids']);
        }

        $blocks = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY display_order ASC, created_date DESC");

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">📝 SEO Content Blocks</h1>
            <a href="<?php echo admin_url('admin.php?page=tkm-content-blocks&action=add'); ?>" class="page-title-action">Add New Block</a>
            <hr class="wp-header-end">

            <div class="tkm-blocks-intro" style="background:#f0f9ff;border-left:4px solid #0891b2;padding:20px;margin:20px 0;border-radius:4px;">
                <h3 style="margin-top:0;">🎯 What are Content Blocks?</h3>
                <p>Content blocks are reusable SEO-optimized sections that automatically boost your document pages to 600+ words for better search rankings.</p>
                <p><strong>How it works:</strong> Create blocks once → Select during document creation → Auto-displayed on page → RankMath detects content!</p>
            </div>

            <?php if (empty($blocks)): ?>
                <div class="notice notice-warning">
                    <p><strong>No content blocks found.</strong> Click "Add New Block" to create your first SEO block!</p>
                </div>
            <?php else: ?>
                <form method="post">
                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <select name="bulk_action">
                                <option value="-1">Bulk Actions</option>
                                <option value="activate">Activate</option>
                                <option value="deactivate">Deactivate</option>
                                <option value="delete">Delete</option>
                            </select>
                            <input type="submit" class="button action" value="Apply">
                        </div>
                    </div>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <td class="check-column"><input type="checkbox" id="select-all"></td>
                                <th>Block Title</th>
                                <th>Type</th>
                                <th>Word Count</th>
                                <th>Scope</th>
                                <th>Schema</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blocks as $block):
                                $word_count = str_word_count(wp_strip_all_tags($block->block_content));
                                $scope = $this->get_block_scope($block);
                            ?>
                            <tr>
                                <th class="check-column">
                                    <input type="checkbox" name="block_ids[]" value="<?php echo $block->id; ?>">
                                </th>
                                <td><strong><?php echo esc_html($block->block_title); ?></strong></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $block->block_type)); ?></td>
                                <td><?php echo $word_count; ?> words</td>
                                <td><?php echo $scope; ?></td>
                                <td>
                                    <?php if ($block->has_schema): ?>
                                        <span style="color:#059669;">✓ <?php echo strtoupper($block->schema_type); ?></span>
                                    <?php else: ?>
                                        <span style="color:#64748b;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($block->active): ?>
                                        <span style="color:#059669;">● Active</span>
                                    <?php else: ?>
                                        <span style="color:#dc2626;">● Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=tkm-content-blocks&action=edit&id=' . $block->id); ?>">Edit</a> |
                                    <a href="<?php echo admin_url('admin.php?page=tkm-content-blocks&action=delete&id=' . $block->id . '&_wpnonce=' . wp_create_nonce('delete_block_' . $block->id)); ?>"
                                       onclick="return confirm('Delete this block? This cannot be undone.');"
                                       style="color:#dc2626;">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            <?php endif; ?>
        </div>

        <script>
        document.getElementById('select-all')?.addEventListener('change', function() {
            document.querySelectorAll('input[name="block_ids[]"]').forEach(cb => cb.checked = this.checked);
        });
        </script>
        <?php
    }

    /**
     * Get block scope description
     */
    private function get_block_scope($block) {
        $parts = array();
        if ($block->subject) $parts[] = $block->subject;
        if ($block->grade) $parts[] = $block->grade;
        if ($block->level) $parts[] = $block->level;
        if ($block->category) $parts[] = $block->category;

        return !empty($parts) ? implode(', ', $parts) : 'Universal';
    }

    /**
     * Render edit form
     */
    private function render_edit_form() {
        $block_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $block = null;

        if ($block_id) {
            global $wpdb;
            $block = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $block_id));
            if (!$block) {
                wp_die('Block not found.');
            }
        }

        // Handle form submission
        if (isset($_POST['save_block'])) {
            $this->handle_save();
            return;
        }

        $block_types = array(
            'how_to' => 'How-To Guide',
            'faq' => 'FAQ Section',
            'checklist' => 'Checklist',
            'learning_objectives' => 'Learning Objectives',
            'assessment_guide' => 'Assessment Guide',
            'teacher_tips' => 'Teacher Tips',
            'materials_list' => 'Materials List',
            'generic' => 'Generic Content'
        );

        $schema_types = array(
            '' => 'None',
            'HowTo' => 'How-To (Step-by-step guide)',
            'FAQPage' => 'FAQ (Questions & Answers)'
        );

        ?>
        <div class="wrap">
            <h1><?php echo $block_id ? 'Edit Content Block' : 'Add New Content Block'; ?></h1>

            <form method="post" style="max-width:900px;">
                <?php wp_nonce_field('save_block', 'block_nonce'); ?>
                <input type="hidden" name="block_id" value="<?php echo $block_id; ?>">

                <table class="form-table">
                    <tr>
                        <th><label for="block_title">Block Title *</label></th>
                        <td>
                            <input type="text" id="block_title" name="block_title"
                                   value="<?php echo $block ? esc_attr($block->block_title) : ''; ?>"
                                   class="regular-text" required>
                            <p class="description">Displayed as heading on the page (e.g., "How to Use This Resource")</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="block_type">Block Type *</label></th>
                        <td>
                            <select id="block_type" name="block_type" required>
                                <?php foreach ($block_types as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php selected($block && $block->block_type === $value); ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="block_content">Content *</label></th>
                        <td>
                            <?php
                            $content = $block ? $block->block_content : '';
                            wp_editor($content, 'block_content', array(
                                'textarea_rows' => 15,
                                'media_buttons' => false,
                                'teeny' => false,
                                'quicktags' => true
                            ));
                            ?>
                            <p class="description">Use HTML headings (H3, H4) and lists for better SEO. RankMath will detect this content!</p>
                        </td>
                    </tr>

                    <tr>
                        <th>Scope (Optional)</th>
                        <td>
                            <p class="description" style="margin:0 0 10px 0;">Leave blank for universal blocks, or specify criteria:</p>
                            <label>Subject: <input type="text" name="subject" value="<?php echo $block ? esc_attr($block->subject) : ''; ?>" placeholder="e.g., Mathematics"></label><br>
                            <label>Grade: <input type="text" name="grade" value="<?php echo $block ? esc_attr($block->grade) : ''; ?>" placeholder="e.g., Grade 3"></label><br>
                            <label>Level: <input type="text" name="level" value="<?php echo $block ? esc_attr($block->level) : ''; ?>" placeholder="e.g., Lower Primary"></label><br>
                            <label>Category: <input type="text" name="category" value="<?php echo $block ? esc_attr($block->category) : ''; ?>" placeholder="e.g., Schemes of Work"></label>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="schema_type">Schema Markup</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="has_schema" name="has_schema" value="1"
                                       <?php checked($block && $block->has_schema); ?>>
                                Enable structured data for this block
                            </label><br>
                            <select id="schema_type" name="schema_type" style="margin-top:10px;">
                                <?php foreach ($schema_types as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php selected($block && $block->schema_type === $value); ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">FAQ and How-To schemas can appear in Google rich snippets!</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="display_order">Display Order</label></th>
                        <td>
                            <input type="number" id="display_order" name="display_order"
                                   value="<?php echo $block ? $block->display_order : 0; ?>"
                                   min="0" max="999" style="width:80px;">
                            <p class="description">Lower numbers appear first (0 = first)</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="active">Status</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="active" name="active" value="1"
                                       <?php checked(!$block || $block->active); ?>>
                                Active (available for selection)
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="save_block" class="button button-primary" value="Save Block">
                    <a href="<?php echo admin_url('admin.php?page=tkm-content-blocks'); ?>" class="button">Cancel</a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Handle save
     */
    private function handle_save() {
        if (!isset($_POST['block_nonce']) || !wp_verify_nonce($_POST['block_nonce'], 'save_block')) {
            wp_die('Security check failed');
        }

        global $wpdb;

        $block_id = isset($_POST['block_id']) ? intval($_POST['block_id']) : 0;

        $data = array(
            'block_title' => sanitize_text_field($_POST['block_title']),
            'block_type' => sanitize_key($_POST['block_type']),
            'block_content' => wp_kses_post($_POST['block_content']),
            'subject' => sanitize_text_field($_POST['subject']),
            'grade' => sanitize_text_field($_POST['grade']),
            'level' => sanitize_text_field($_POST['level']),
            'category' => sanitize_text_field($_POST['category']),
            'has_schema' => isset($_POST['has_schema']) ? 1 : 0,
            'schema_type' => sanitize_text_field($_POST['schema_type']),
            'display_order' => intval($_POST['display_order']),
            'active' => isset($_POST['active']) ? 1 : 0
        );

        if ($block_id) {
            $wpdb->update($this->table_name, $data, array('id' => $block_id));
            $message = 'Block updated successfully!';
        } else {
            $wpdb->insert($this->table_name, $data);
            $message = 'Block created successfully!';
        }

        wp_redirect(add_query_arg(array(
            'page' => 'tkm-content-blocks',
            'message' => urlencode($message)
        ), admin_url('admin.php')));
        exit;
    }

    /**
     * Handle delete
     */
    private function handle_delete() {
        $block_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';

        if (!wp_verify_nonce($nonce, 'delete_block_' . $block_id)) {
            wp_die('Security check failed');
        }

        global $wpdb;
        $wpdb->delete($this->table_name, array('id' => $block_id));

        wp_redirect(add_query_arg(array(
            'page' => 'tkm-content-blocks',
            'message' => urlencode('Block deleted successfully!')
        ), admin_url('admin.php')));
        exit;
    }

    /**
     * Handle bulk actions
     */
    private function handle_bulk_action($action, $ids) {
        global $wpdb;

        $ids = array_map('intval', $ids);
        $ids_string = implode(',', $ids);

        switch ($action) {
            case 'activate':
                $wpdb->query("UPDATE {$this->table_name} SET active = 1 WHERE id IN ($ids_string)");
                break;
            case 'deactivate':
                $wpdb->query("UPDATE {$this->table_name} SET active = 0 WHERE id IN ($ids_string)");
                break;
            case 'delete':
                $wpdb->query("DELETE FROM {$this->table_name} WHERE id IN ($ids_string)");
                break;
        }
    }

    /**
     * Add meta box to document edit screen
     */
    public function add_selection_meta_box() {
        add_meta_box(
            'tkm_seo_blocks',
            '🚀 SEO Content Blocks',
            array($this, 'render_selection_meta_box'),
            'teacher_document',
            'normal',
            'default'
        );
    }

    /**
     * Render selection meta box
     */
    public function render_selection_meta_box($post) {
        $subject = get_post_meta($post->ID, '_tkm_subject', true);
        $grade = get_post_meta($post->ID, '_tkm_grade', true);
        $level = get_post_meta($post->ID, '_tkm_level', true);
        $category_terms = wp_get_post_terms($post->ID, 'file_category');
        $category = !empty($category_terms) ? $category_terms[0]->name : '';

        $selected_blocks = get_post_meta($post->ID, '_tkm_selected_blocks', true) ?: array();

        // Fetch available blocks
        global $wpdb;
        $blocks = $wpdb->get_results("
            SELECT * FROM {$this->table_name}
            WHERE active = 1
            AND (
                (subject = '' OR subject IS NULL OR subject = '$subject')
                AND (grade = '' OR grade IS NULL OR grade = '$grade')
                AND (level = '' OR level IS NULL OR level = '$level')
                AND (category = '' OR category IS NULL OR category = '$category')
            )
            ORDER BY display_order ASC, block_type ASC
        ");

        wp_nonce_field('save_seo_blocks', 'seo_blocks_nonce');

        ?>
        <div class="tkm-seo-blocks-wrapper">
            <div style="background:#f0f9ff;padding:15px;border-left:4px solid #0891b2;margin-bottom:20px;">
                <p style="margin:0;"><strong>💡 Boost to 600+ words instantly!</strong> Select blocks to auto-add SEO-optimized content. RankMath will detect them!</p>
            </div>

            <?php if (empty($blocks)): ?>
                <div style="padding:20px;background:#fef3c7;border-left:4px solid #f59e0b;">
                    <p style="margin:0;"><strong>No content blocks available.</strong> <a href="<?php echo admin_url('admin.php?page=tkm-content-blocks'); ?>">Create your first block</a> to boost SEO!</p>
                </div>
            <?php else: ?>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                    <?php foreach ($blocks as $block):
                        $word_count = str_word_count(wp_strip_all_tags($block->block_content));
                    ?>
                    <label style="border:1px solid #e2e8f0;padding:15px;border-radius:8px;cursor:pointer;display:block;background:#fff;transition:all 0.2s;">
                        <div style="display:flex;gap:12px;align-items:start;">
                            <input type="checkbox" name="tkm_selected_blocks[]" value="<?php echo $block->id; ?>"
                                   <?php checked(in_array($block->id, $selected_blocks)); ?>
                                   style="margin-top:3px;">
                            <div style="flex:1;">
                                <strong style="display:block;margin-bottom:5px;color:#0f172a;font-size:14px;">
                                    <?php echo esc_html($block->block_title); ?>
                                </strong>
                                <span style="font-size:11px;color:#64748b;display:inline-block;background:#f1f5f9;padding:2px 8px;border-radius:4px;margin-bottom:8px;">
                                    <?php echo ucfirst(str_replace('_', ' ', $block->block_type)); ?> • +<?php echo $word_count; ?> words
                                </span>
                                <?php if ($block->has_schema): ?>
                                    <span style="font-size:11px;color:#059669;display:inline-block;background:#d1fae5;padding:2px 8px;border-radius:4px;margin:0 0 8px 4px;">
                                        ✓ Schema: <?php echo $block->schema_type; ?>
                                    </span>
                                <?php endif; ?>
                                <div style="font-size:13px;color:#475569;line-height:1.5;">
                                    <?php echo wp_trim_words(wp_strip_all_tags($block->block_content), 15); ?>...
                                </div>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top:15px;padding:12px;background:#fef3c7;border-left:4px solid #f59e0b;border-radius:4px;">
                    <p style="margin:0;font-size:13px;">
                        <strong>💡 Tip:</strong> Select 4-6 blocks to reach 600-1000 words for optimal RankMath scoring!
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <style>
        .tkm-seo-blocks-wrapper label:hover {
            border-color: #0891b2;
            box-shadow: 0 2px 8px rgba(8, 145, 178, 0.1);
        }
        </style>
        <?php
    }

    /**
     * Save selected blocks
     */
    public function save_selected_blocks($post_id) {
        if (!isset($_POST['seo_blocks_nonce']) || !wp_verify_nonce($_POST['seo_blocks_nonce'], 'save_seo_blocks')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $selected = isset($_POST['tkm_selected_blocks']) ? array_map('intval', $_POST['tkm_selected_blocks']) : array();
        update_post_meta($post_id, '_tkm_selected_blocks', $selected);
    }

    /**
     * Sync blocks to post_content for RankMath scanning
     */
    public function sync_to_post_content($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;

        $description = get_post_meta($post_id, '_tkm_description', true);
        $selected_blocks = get_post_meta($post_id, '_tkm_selected_blocks', true) ?: array();

        $content = '';

        // Add description
        if ($description) {
            $content .= wpautop($description) . "\n\n";
        }

        // Add selected blocks
        if (!empty($selected_blocks)) {
            global $wpdb;
            $block_ids = implode(',', array_map('intval', $selected_blocks));
            $blocks = $wpdb->get_results("
                SELECT * FROM {$this->table_name}
                WHERE id IN ($block_ids) AND active = 1
                ORDER BY display_order ASC
            ");

            foreach ($blocks as $block) {
                $content .= "<h2>" . esc_html($block->block_title) . "</h2>\n";
                $content .= $block->block_content . "\n\n";
            }
        }

        // Update post_content (RankMath will scan this)
        if (!empty($content)) {
            remove_action('save_post_teacher_document', array($this, 'sync_to_post_content'), 99);
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $content
            ));
            add_action('save_post_teacher_document', array($this, 'sync_to_post_content'), 99);
        }
    }

    /**
     * Output schema for eligible blocks
     */
    public function output_block_schema() {
        if (!is_singular('teacher_document')) return;

        $post_id = get_the_ID();
        $selected_blocks = get_post_meta($post_id, '_tkm_selected_blocks', true) ?: array();

        if (empty($selected_blocks)) return;

        global $wpdb;
        $block_ids = implode(',', array_map('intval', $selected_blocks));
        $blocks = $wpdb->get_results("
            SELECT * FROM {$this->table_name}
            WHERE id IN ($block_ids)
            AND active = 1
            AND has_schema = 1
            ORDER BY display_order ASC
        ");

        foreach ($blocks as $block) {
            if ($block->schema_type === 'FAQPage') {
                $this->output_faq_schema($block);
            } elseif ($block->schema_type === 'HowTo') {
                $this->output_howto_schema($block);
            }
        }
    }

    /**
     * Output FAQ schema
     */
    private function output_faq_schema($block) {
        // Parse FAQ content for Q&A pairs
        $content = wp_strip_all_tags($block->block_content, '<h3><h4>');
        preg_match_all('/<h[34]>(.*?)<\/h[34]>(.*?)(?=<h[34]>|$)/s', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) return;

        $questions = array();
        foreach ($matches as $match) {
            $questions[] = array(
                '@type' => 'Question',
                'name' => wp_strip_all_tags($match[1]),
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => wp_strip_all_tags(trim($match[2]))
                )
            );
        }

        if (empty($questions)) return;

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions
        );

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }

    /**
     * Output How-To schema
     */
    private function output_howto_schema($block) {
        // Parse How-To content for steps
        $content = wp_strip_all_tags($block->block_content, '<h3><h4><p>');
        preg_match_all('/<h[34]>(.*?)<\/h[34]>(.*?)(?=<h[34]>|$)/s', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) return;

        $steps = array();
        foreach ($matches as $index => $match) {
            $steps[] = array(
                '@type' => 'HowToStep',
                'position' => $index + 1,
                'name' => wp_strip_all_tags($match[1]),
                'text' => wp_strip_all_tags(trim($match[2]))
            );
        }

        if (empty($steps)) return;

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => esc_html($block->block_title),
            'step' => $steps
        );

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
}

// Initialize
new TKM_Content_Blocks();
