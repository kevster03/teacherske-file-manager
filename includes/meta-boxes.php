<?php
/**
 * Meta Boxes - Ultra-Fast Document Editor
 * 
 * Handles the admin form for creating/editing documents
 * Features: Dynamic dropdowns, AJAX save, keyboard shortcuts
 * Subject is now a META FIELD (not taxonomy)
 */

if (!defined('ABSPATH')) exit;

/**
 * Add Meta Box
 */
function tkm_add_meta_boxes() {
    add_meta_box(
        'tkm_document_meta',
        '<span class="dashicons dashicons-media-document" style="color:#c92651;"></span> ' . __('Document Details', 'teacherske'),
        'tkm_render_meta_box',
        'teacher_document',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tkm_add_meta_boxes');

/**
 * Render Meta Box
 */
function tkm_render_meta_box($post) {
    wp_nonce_field('tkm_save_meta', 'tkm_meta_nonce');
    
    // Get existing values
    $file = get_post_meta($post->ID, '_tkm_file', true);
    $level = get_post_meta($post->ID, '_tkm_level', true);
    $grade = get_post_meta($post->ID, '_tkm_grade', true);
    $subject = get_post_meta($post->ID, '_tkm_subject', true);
    $version = get_post_meta($post->ID, '_tkm_version', true);
    $desc = get_post_meta($post->ID, '_tkm_description', true);
    $file_ext = get_post_meta($post->ID, '_tkm_file_ext', true);
    $file_size = get_post_meta($post->ID, '_tkm_file_size', true);
    
    // Get data
    $levels = tkm_get_levels();
    $versions = tkm_get_versions();
    $current_year = date('Y');
    $default_version = $current_year . ' Edition';
    
    // Get subjects for current level
    $subjects_for_level = $level ? tkm_get_subjects_for_level($level) : array();
    
    ?>
    <div class="tkm-meta-box-wrapper">

        <!-- Template Selector -->
        <?php if ($post->post_status === 'auto-draft' || (isset($_GET['template']) && $_GET['template'])):
            $templates = get_posts(array(
                'post_type' => 'teacher_template',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            if (!empty($templates)):
        ?>
        <div class="tkm-field tkm-field-full" style="background:#f0f6fc;border:2px solid #c92651;padding:15px;border-radius:8px;margin-bottom:20px;">
            <label class="tkm-label">
                <strong style="color:#c92651;"><?php _e('Apply Template', 'teacherske'); ?></strong>
                <span class="tkm-hint"><?php _e('Pre-fill document fields from a saved template', 'teacherske'); ?></span>
            </label>
            <select name="tkm_apply_template" id="tkm_apply_template" class="tkm-input" style="margin-top:8px;">
                <option value=""><?php _e('— Select a Template —', 'teacherske'); ?></option>
                <?php foreach ($templates as $template): ?>
                    <option value="<?php echo esc_attr($template->ID); ?>"><?php echo esc_html($template->post_title); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="tkm_apply_template_btn" class="button button-secondary" style="margin-top:10px;" disabled>
                <?php _e('Apply Template', 'teacherske'); ?>
            </button>
        </div>
        <?php
            endif;
        endif;
        ?>

        <!-- Description Field -->
        <div class="tkm-field tkm-field-full">
            <label class="tkm-label">
                <strong><?php _e('Description', 'teacherske'); ?></strong>
                <span class="tkm-hint"><?php _e('Brief overview of the document content (shown on download page)', 'teacherske'); ?></span>
            </label>
            <textarea 
                name="tkm_description" 
                id="tkm_description"
                rows="4" 
                class="tkm-textarea"
                placeholder="<?php esc_attr_e('e.g., Comprehensive notes covering all topics in Grade 7 Mathematics curriculum including algebra, geometry, and statistics...', 'teacherske'); ?>"
            ><?php echo esc_textarea($desc); ?></textarea>
        </div>

        <!-- File Upload -->
        <div class="tkm-field tkm-field-full">
            <label class="tkm-label">
                <strong><?php _e('File Upload', 'teacherske'); ?> <span class="required">*</span></strong>
                <span class="tkm-hint"><?php _e('Select document file from media library or enter URL', 'teacherske'); ?></span>
            </label>
            <div class="tkm-file-wrapper">
                <input 
                    type="text" 
                    id="tkm_file" 
                    name="tkm_file" 
                    value="<?php echo esc_attr($file); ?>" 
                    class="tkm-input-file"
                    placeholder="<?php esc_attr_e('No file selected', 'teacherske'); ?>"
                    required
                />
                <button type="button" class="button button-primary" id="tkm_file_button">
                    <span class="dashicons dashicons-upload" style="margin-top:3px;"></span>
                    <?php _e('Select File', 'teacherske'); ?>
                </button>
                <?php if ($file): ?>
                    <button type="button" class="button" id="tkm_clear_file" style="display:inline-block;">
                        <span class="dashicons dashicons-no" style="margin-top:3px;"></span>
                    </button>
                <?php else: ?>
                    <button type="button" class="button" id="tkm_clear_file" style="display:none;">
                        <span class="dashicons dashicons-no" style="margin-top:3px;"></span>
                    </button>
                <?php endif; ?>
            </div>
            <?php if ($file_ext || $file_size): ?>
                <div class="tkm-file-info">
                    <?php if ($file_ext): ?>
                        <span class="tkm-badge tkm-badge-type"><?php echo esc_html(strtoupper($file_ext)); ?></span>
                    <?php endif; ?>
                    <?php if ($file_size): ?>
                        <span class="tkm-badge tkm-badge-size"><?php echo esc_html(tkm_format_file_size($file_size)); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Grid: Level and Grade -->
        <div class="tkm-grid-2">
            <div class="tkm-field">
                <label class="tkm-label">
                    <strong><?php _e('Education Level', 'teacherske'); ?> <span class="required">*</span></strong>
                </label>
                <select name="tkm_level" id="tkm_level" class="tkm-select" required>
                    <option value=""><?php _e('— Select Level —', 'teacherske'); ?></option>
                    <?php foreach ($levels as $key => $data): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($level, $key); ?>>
                            <?php echo esc_html($data['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="tkm-field">
                <label class="tkm-label">
                    <strong><?php _e('Grade', 'teacherske'); ?> <span class="required">*</span></strong>
                </label>
                <select name="tkm_grade" id="tkm_grade" class="tkm-select" required <?php echo !$level ? 'disabled' : ''; ?>>
                    <option value=""><?php _e('— Select Level First —', 'teacherske'); ?></option>
                    <?php if ($level && isset($levels[$level])): ?>
                        <?php foreach ($levels[$level]['grades'] as $g): ?>
                            <option value="<?php echo esc_attr($g); ?>" <?php selected($grade, $g); ?>>
                                <?php echo esc_html($g); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <!-- Grid: Subject and Version -->
        <div class="tkm-grid-2">
            <div class="tkm-field">
                <label class="tkm-label">
                    <strong><?php _e('Subject', 'teacherske'); ?></strong>
                    <span class="tkm-hint"><?php _e('Based on selected level', 'teacherske'); ?></span>
                </label>
                <select name="tkm_subject" id="tkm_subject" class="tkm-select" <?php echo !$level ? 'disabled' : ''; ?>>
                    <option value=""><?php _e('— Select Level First —', 'teacherske'); ?></option>
                    <?php if (!empty($subjects_for_level)): ?>
                        <?php foreach ($subjects_for_level as $subj): ?>
                            <option value="<?php echo esc_attr($subj); ?>" <?php selected($subject, $subj); ?>>
                                <?php echo esc_html($subj); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="tkm-field">
                <label class="tkm-label">
                    <strong><?php _e('Version / Edition', 'teacherske'); ?></strong>
                </label>
                <select name="tkm_version" class="tkm-select">
                    <option value=""><?php _e('— Select Version —', 'teacherske'); ?></option>
                    <?php foreach ($versions as $v): ?>
                        <option value="<?php echo esc_attr($v); ?>" <?php selected($version ? $version : $default_version, $v); ?>>
                            <?php echo esc_html($v); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Author Selection -->
        <div class="tkm-field">
            <label class="tkm-label">
                <strong><?php _e('Author', 'teacherske'); ?></strong>
            </label>
            <select name="tkm_author_select" class="tkm-select">
                <?php
                $author_id = $post->post_author ?: get_current_user_id();
                $users = get_users(array('orderby' => 'display_name', 'who' => 'authors'));
                foreach ($users as $user):
                ?>
                    <option value="<?php echo intval($user->ID); ?>" <?php selected($author_id, $user->ID); ?>>
                        <?php echo esc_html($user->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Info Box -->
        <div class="tkm-info-box">
            <div class="tkm-info-icon">💡</div>
            <div class="tkm-info-content">
                <strong><?php _e('Quick Tips:', 'teacherske'); ?></strong>
                <ul>
                    <li><?php _e('Select education level first to see subjects for that level', 'teacherske'); ?></li>
                    <li><?php _e('Use Ctrl+S (Cmd+S) to quick save without page reload', 'teacherske'); ?></li>
                    <li><?php _e('File type and size are automatically detected', 'teacherske'); ?></li>
                    <li><?php _e('Subjects are managed in Settings → File Manager', 'teacherske'); ?></li>
                </ul>
            </div>
        </div>

        <!-- Hidden fields for auto-detected data -->
        <input type="hidden" name="tkm_file_ext" id="tkm_file_ext" value="<?php echo esc_attr($file_ext); ?>" />
        <input type="hidden" name="tkm_file_size" id="tkm_file_size" value="<?php echo esc_attr($file_size); ?>" />
        
    </div>

    <!-- Pass data to JavaScript -->
    <script type="text/javascript">
        var tkmLevels = <?php echo json_encode($levels); ?>;
        var tkmSubjectsByLevel = <?php echo json_encode(tkm_get_all_subjects_by_level()); ?>;
        var tkmCurrentLevel = <?php echo json_encode($level); ?>;
        var tkmCurrentGrade = <?php echo json_encode($grade); ?>;
        var tkmCurrentSubject = <?php echo json_encode($subject); ?>;
    </script>
    <?php
}

/**
 * Save Meta Box Data
 */
function tkm_save_meta($post_id) {
    // Security checks
    if (!isset($_POST['tkm_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['tkm_meta_nonce'], 'tkm_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (get_post_type($post_id) !== 'teacher_document') return;

    // Save all meta fields
    $fields = array(
        'description' => 'wp_kses_post',
        'file' => 'esc_url_raw',
        'level' => 'sanitize_text_field',
        'grade' => 'sanitize_text_field',
        'subject' => 'sanitize_text_field', // Subject as meta field
        'version' => 'sanitize_text_field',
        'file_ext' => 'sanitize_text_field',
        'file_size' => 'intval'
    );
    
    foreach ($fields as $field => $sanitize_func) {
        if (isset($_POST['tkm_' . $field])) {
            $value = call_user_func($sanitize_func, $_POST['tkm_' . $field]);
            update_post_meta($post_id, '_tkm_' . $field, $value);
        }
    }

    // Auto-detect file metadata if file URL provided
    $file = get_post_meta($post_id, '_tkm_file', true);
    if ($file) {
        // Get extension
        $ext = tkm_get_file_extension($file);
        if ($ext) {
            update_post_meta($post_id, '_tkm_file_ext', $ext);
        }
        
        // Get file size (if local)
        $size = tkm_calculate_file_size($file);
        if ($size) {
            update_post_meta($post_id, '_tkm_file_size', $size);
        }
    } else {
        delete_post_meta($post_id, '_tkm_file_ext');
        delete_post_meta($post_id, '_tkm_file_size');
    }

    // Update author
    if (isset($_POST['tkm_author_select'])) {
        $new_author = intval($_POST['tkm_author_select']);
        $current_post = get_post($post_id);
        if ($new_author !== $current_post->post_author) {
            remove_action('save_post', 'tkm_save_meta');
            wp_update_post(array('ID' => $post_id, 'post_author' => $new_author));
            add_action('save_post', 'tkm_save_meta');
        }
    }
    
    // Initialize download tracking
    if (!get_post_meta($post_id, '_tkm_download_count', true)) {
        update_post_meta($post_id, '_tkm_download_count', 0);
    }
    
    // Fire action hook
    do_action('tkm_document_saved', $post_id);
}
add_action('save_post', 'tkm_save_meta', 10, 1);

/**
 * Enqueue Admin Scripts & Styles
 */
function tkm_admin_enqueue($hook) {
    global $post;
    
    // Only on document edit screens
    if (!in_array($hook, array('post.php', 'post-new.php'))) return;
    if (!isset($post) || $post->post_type !== 'teacher_document') return;
    
    // Media uploader
    wp_enqueue_media();
    
    // Custom styles
    wp_enqueue_style(
        'tkm-admin-meta-css',
        TKM_URL . 'assets/css/admin-meta-box.css',
        array(),
        TKM_VERSION
    );
    
    // Admin scripts
    wp_enqueue_script(
        'tkm-admin-js',
        TKM_URL . 'assets/js/admin.js',
        array('jquery'),
        TKM_VERSION,
        true
    );
    
    // AJAX quick save
    wp_enqueue_script(
        'tkm-admin-ajax-js',
        TKM_URL . 'assets/js/admin-ajax.js',
        array('jquery'),
        TKM_VERSION,
        true
    );
    
    // Localize scripts
    wp_localize_script('tkm-admin-ajax-js', 'tkmAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tkm_quick_save_' . $post->ID),
        'postId' => $post->ID,
        'saveText' => __('Saving...', 'teacherske'),
        'savedText' => __('✓ Saved!', 'teacherske'),
        'errorText' => __('Save failed', 'teacherske'),
        'templateNonce' => wp_create_nonce('tkm_template_apply')
    ));
}
add_action('admin_enqueue_scripts', 'tkm_admin_enqueue');

/**
 * AJAX Quick Save Handler
 */
function tkm_ajax_quick_save() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !isset($_POST['post_id'])) {
        wp_send_json_error('Invalid request');
    }
    
    $post_id = intval($_POST['post_id']);
    
    if (!wp_verify_nonce($_POST['nonce'], 'tkm_quick_save_' . $post_id)) {
        wp_send_json_error('Security check failed');
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error('Permission denied');
    }
    
    // Update post title
    if (isset($_POST['post_title'])) {
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field($_POST['post_title'])
        ));
    }
    
    // Update meta fields (reuse save function logic)
    $_POST['tkm_meta_nonce'] = wp_create_nonce('tkm_save_meta');
    tkm_save_meta($post_id);
    
    wp_send_json_success(array(
        'message' => __('Document saved successfully', 'teacherske'),
        'time' => current_time('mysql')
    ));
}
add_action('wp_ajax_tkm_quick_save', 'tkm_ajax_quick_save');

/**
 * Remove Unnecessary Meta Boxes (Speed Optimization)
 */
function tkm_remove_unnecessary_metaboxes() {
    $screen = 'teacher_document';
    
    // Remove these to speed up editor
    remove_meta_box('commentstatusdiv', $screen, 'normal');
    remove_meta_box('commentsdiv', $screen, 'normal');
    remove_meta_box('trackbacksdiv', $screen, 'normal');
    remove_meta_box('slugdiv', $screen, 'normal');
    remove_meta_box('authordiv', $screen, 'normal');
    remove_meta_box('postcustom', $screen, 'normal');
    remove_meta_box('postexcerpt', $screen, 'normal');
}
add_action('admin_menu', 'tkm_remove_unnecessary_metaboxes');

/**
 * Disable Auto-save (Optional - for speed)
 */
function tkm_disable_autosave() {
    global $post;

    if ($post && get_post_type($post->ID) === 'teacher_document') {
        wp_dequeue_script('autosave');
    }
}
add_action('admin_print_scripts', 'tkm_disable_autosave');

/**
 * Add Template Meta Boxes
 */
function tkm_add_template_meta_boxes() {
    add_meta_box(
        'tkm_template_defaults',
        '<span class="dashicons dashicons-admin-generic" style="color:#c92651;"></span> ' . __('Template Defaults', 'teacherske'),
        'tkm_render_template_meta_box',
        'teacher_template',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tkm_add_template_meta_boxes');

/**
 * Render Template Meta Box
 */
function tkm_render_template_meta_box($post) {
    wp_nonce_field('tkm_save_template', 'tkm_template_nonce');

    // Get existing values
    $level = get_post_meta($post->ID, '_tkm_template_level', true);
    $grade = get_post_meta($post->ID, '_tkm_template_grade', true);
    $subject = get_post_meta($post->ID, '_tkm_template_subject', true);
    $version = get_post_meta($post->ID, '_tkm_template_version', true);
    $description = get_post_meta($post->ID, '_tkm_template_description', true);

    $levels = tkm_get_levels();
    $subjects_for_level = $level ? tkm_get_subjects_for_level($level) : array();
    ?>
    <div class="tkm-meta-box-wrapper">
        <p style="background:#f0f6fc;padding:12px;border-left:4px solid #c92651;margin-bottom:20px;">
            <?php _e('Templates allow you to pre-fill document fields. When creating a new document, users can select this template to automatically populate these fields.', 'teacherske'); ?>
        </p>

        <div class="tkm-field-row">
            <div class="tkm-field">
                <label class="tkm-label">
                    <strong><?php _e('Education Level', 'teacherske'); ?></strong>
                </label>
                <select name="tkm_template_level" id="tkm_template_level" class="tkm-input">
                    <option value=""><?php _e('— Select Level —', 'teacherske'); ?></option>
                    <?php foreach ($levels as $key => $data): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($level, $key); ?>>
                            <?php echo esc_html($data['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="tkm-field">
                <label class="tkm-label">
                    <strong><?php _e('Grade', 'teacherske'); ?></strong>
                </label>
                <input type="text" name="tkm_template_grade" value="<?php echo esc_attr($grade); ?>" class="tkm-input" placeholder="e.g., Grade 7" id="tkm_template_grade" />
            </div>
        </div>

        <div class="tkm-field-row">
            <div class="tkm-field">
                <label class="tkm-label">
                    <strong><?php _e('Subject', 'teacherske'); ?></strong>
                </label>
                <select name="tkm_template_subject" id="tkm_template_subject" class="tkm-input">
                    <option value=""><?php _e('— Select Subject —', 'teacherske'); ?></option>
                    <?php foreach ($subjects_for_level as $subj): ?>
                        <option value="<?php echo esc_attr($subj); ?>" <?php selected($subject, $subj); ?>>
                            <?php echo esc_html($subj); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="tkm-field">
                <label class="tkm-label">
                    <strong><?php _e('Version', 'teacherske'); ?></strong>
                </label>
                <input type="text" name="tkm_template_version" value="<?php echo esc_attr($version); ?>" class="tkm-input" placeholder="e.g., 2026 Edition" />
            </div>
        </div>

        <div class="tkm-field tkm-field-full">
            <label class="tkm-label">
                <strong><?php _e('Description Template', 'teacherske'); ?></strong>
                <span class="tkm-hint"><?php _e('Default description text (can use placeholders like {{TITLE}}, {{GRADE}}, {{SUBJECT}})', 'teacherske'); ?></span>
            </label>
            <textarea name="tkm_template_description" rows="4" class="tkm-textarea" placeholder="e.g., Comprehensive {{SUBJECT}} notes for {{GRADE}} students covering all topics..."><?php echo esc_textarea($description); ?></textarea>
        </div>
    </div>
    <?php
}

/**
 * Save Template Meta
 */
function tkm_save_template_meta($post_id) {
    // Security checks
    if (!isset($_POST['tkm_template_nonce']) || !wp_verify_nonce($_POST['tkm_template_nonce'], 'tkm_save_template')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (get_post_type($post_id) !== 'teacher_template') {
        return;
    }

    // Save template fields
    $fields = array('level', 'grade', 'subject', 'version', 'description');

    foreach ($fields as $field) {
        $key = 'tkm_template_' . $field;
        if (isset($_POST[$key])) {
            update_post_meta($post_id, '_' . $key, sanitize_text_field($_POST[$key]));
        }
    }
}
add_action('save_post', 'tkm_save_template_meta');

/**
 * AJAX: Get Template Data
 */
function tkm_ajax_get_template_data() {
    check_ajax_referer('tkm_template_apply', 'nonce');

    $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

    if (!$template_id || get_post_type($template_id) !== 'teacher_template') {
        wp_send_json_error(array('message' => __('Invalid template', 'teacherske')));
    }

    // Get template data
    $data = array(
        'level' => get_post_meta($template_id, '_tkm_template_level', true),
        'grade' => get_post_meta($template_id, '_tkm_template_grade', true),
        'subject' => get_post_meta($template_id, '_tkm_template_subject', true),
        'version' => get_post_meta($template_id, '_tkm_template_version', true),
        'description' => get_post_meta($template_id, '_tkm_template_description', true),
        'thumbnail_id' => get_post_thumbnail_id($template_id)
    );

    // Get subjects for level
    if ($data['level']) {
        $data['subjects'] = tkm_get_subjects_for_level($data['level']);
    }

    wp_send_json_success($data);
}
add_action('wp_ajax_tkm_get_template_data', 'tkm_ajax_get_template_data');
