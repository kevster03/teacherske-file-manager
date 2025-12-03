<?php
/**
 * Content Blocks System - Version 7.0.0
 *
 * Template-variable driven SEO content blocks for unique, scalable content
 * Production-ready, optimized for live sites
 */

if (!defined('ABSPATH')) exit;

class TKM_Content_Blocks {

    /**
     * Initialize
     */
    public static function init() {
        add_action('admin_post_tkm_save_content_blocks', array(__CLASS__, 'save_blocks'));
    }

    /**
     * Get default content blocks
     */
    public static function get_default_blocks() {
        return array(
            'intro' => array(
                'title' => 'Introduction Block',
                'heading' => '{grade} {subject} - Free {category} Download',
                'template' => 'Download free {grade} {subject} {category} designed for Kenya\'s Competency-Based Curriculum. This {file_type} resource provides comprehensive coverage aligned with {version} learning outcomes. Trusted by {download_count_formatted}+ Kenyan educators.',
                'enabled' => true,
                'priority' => 10,
                'conditions' => array(),
            ),
            'grade_benefits' => array(
                'title' => 'Grade-Specific Benefits',
                'heading' => 'Why {grade} Students Need This {subject} Resource',
                'template' => '{grade} students studying {subject} will benefit from this {category} during {current_term_text}. The material addresses {level_label} competencies and supports progressive learning outlined in CBC frameworks. This resource has been carefully designed to match the cognitive development level of {grade} learners.',
                'enabled' => true,
                'priority' => 20,
                'conditions' => array(),
            ),
            'format_info' => array(
                'title' => 'File Format Benefits',
                'heading' => 'About This {file_ext} Format',
                'template' => 'This {file_ext} format ensures compatibility across all devices - smartphones, tablets, and computers. The {file_size} file size makes it accessible even with limited internet connectivity. {file_type} files are ideal for {category} as they maintain formatting and can be easily shared among teachers and students.',
                'enabled' => true,
                'priority' => 30,
                'conditions' => array(),
            ),
            'kenya_context' => array(
                'title' => 'Kenyan Education Context',
                'heading' => '{subject} Education in Kenya',
                'template' => '{subject} education in Kenya has evolved under CBC, with {level_label} learners now developing competencies through active learning. This {grade} resource reflects Kenyan classroom realities and cultural contexts relevant to our education system. Teachers across Kenya use these materials to deliver engaging, curriculum-aligned lessons.',
                'enabled' => true,
                'priority' => 40,
                'conditions' => array(),
            ),
            'how_to_use' => array(
                'title' => 'How to Use (How-To Rich Snippet)',
                'heading' => 'How to Use This {category}',
                'template' => 'STEP_1: Download the {file_ext} file ({file_size}) by clicking the download button above||STEP_2: Open with your preferred application - {file_type} files work on most devices||STEP_3: Review the content before use to ensure alignment with your lesson objectives||STEP_4: Adapt as needed for your specific classroom or student needs',
                'enabled' => true,
                'priority' => 50,
                'conditions' => array(),
                'type' => 'howto',
            ),
            'faq' => array(
                'title' => 'FAQ (FAQ Rich Snippet)',
                'heading' => 'Frequently Asked Questions',
                'template' => 'Q: Is this resource free to download?||A: Yes! All resources on TeachersKE are completely free for Kenyan educators, students, and parents.||Q: Is this aligned with the CBC curriculum?||A: Absolutely. This {grade} {subject} resource follows KICD CBC guidelines for {version}.||Q: Can I edit this {file_type}?||A: {file_editable_answer}||Q: How do I access more resources?||A: Browse our collection of {subject} materials for all CBC grades, or explore other subjects in our library.',
                'enabled' => true,
                'priority' => 60,
                'conditions' => array(),
                'type' => 'faq',
            ),
        );
    }

    /**
     * Get all content blocks
     */
    public static function get_blocks() {
        $blocks = get_option('tkm_content_blocks', array());

        if (empty($blocks)) {
            $blocks = self::get_default_blocks();
            update_option('tkm_content_blocks', $blocks);
        }

        return $blocks;
    }

    /**
     * Get template variables
     */
    public static function get_template_variables() {
        return array(
            '{title}' => 'Document title',
            '{grade}' => 'Grade (e.g., Grade 7, PP2)',
            '{subject}' => 'Subject name',
            '{category}' => 'Category (Notes, Schemes, etc.)',
            '{level}' => 'Level key',
            '{level_label}' => 'Full level label',
            '{version}' => 'Curriculum version',
            '{file_type}' => 'File type label',
            '{file_ext}' => 'File extension (uppercase)',
            '{file_size}' => 'Formatted file size',
            '{download_count}' => 'Download count (number)',
            '{download_count_formatted}' => 'Download count (formatted)',
            '{author}' => 'Author name',
            '{date}' => 'Publication date',
            '{current_year}' => 'Current year',
            '{current_term}' => 'Current term (1, 2, or 3)',
            '{current_term_text}' => 'Current term (text)',
            '{year_difference}' => 'Years since publication',
            '{file_editable_answer}' => 'Editable answer based on file type',
        );
    }

    /**
     * Process template variables
     */
    public static function process_variables($template, $post_id) {
        // Get metadata
        $grade = get_post_meta($post_id, '_tkm_grade', true);
        $subject = get_post_meta($post_id, '_tkm_subject', true);
        $category_terms = wp_get_post_terms($post_id, 'file_category', array('fields' => 'names'));
        $category = !empty($category_terms) ? $category_terms[0] : '';
        $level = get_post_meta($post_id, '_tkm_level', true);
        $version = get_post_meta($post_id, '_tkm_version', true);
        $file_ext = get_post_meta($post_id, '_tkm_file_ext', true);
        $file_size = get_post_meta($post_id, '_tkm_file_size', true);
        $download_count = intval(get_post_meta($post_id, '_tkm_download_count', true));

        $levels = tkm_get_levels();
        $level_label = isset($levels[$level]) ? $levels[$level]['label'] : $level;
        $file_type = tkm_get_file_type_label($file_ext);
        $file_size_formatted = tkm_format_file_size($file_size);

        // Current date/term info
        $current_month = date('n');
        $current_term = ($current_month <= 3) ? 1 : (($current_month <= 7) ? 2 : 3);
        $current_term_text = 'Term ' . $current_term;

        // Version year difference
        preg_match('/\d{4}/', $version, $year_matches);
        $version_year = !empty($year_matches) ? intval($year_matches[0]) : date('Y');
        $year_difference = date('Y') - $version_year;

        // File editable answer
        $editable_exts = array('doc', 'docx', 'ppt', 'pptx', 'txt');
        $file_editable_answer = in_array(strtolower($file_ext), $editable_exts)
            ? 'Yes, ' . strtoupper($file_ext) . ' files are fully editable with Microsoft Office, Google Docs, or compatible applications.'
            : 'This ' . strtoupper($file_ext) . ' file is designed for viewing and printing. For editable versions, check our collection for DOC or DOCX formats.';

        // Build replacements
        $replacements = array(
            '{title}' => get_the_title($post_id),
            '{grade}' => $grade,
            '{subject}' => $subject,
            '{category}' => $category,
            '{level}' => $level,
            '{level_label}' => $level_label,
            '{version}' => $version,
            '{file_type}' => $file_type,
            '{file_ext}' => strtoupper($file_ext),
            '{file_size}' => $file_size_formatted,
            '{download_count}' => $download_count,
            '{download_count_formatted}' => number_format($download_count),
            '{author}' => get_the_author_meta('display_name', get_post_field('post_author', $post_id)),
            '{date}' => get_the_date('', $post_id),
            '{current_year}' => date('Y'),
            '{current_term}' => $current_term,
            '{current_term_text}' => $current_term_text,
            '{year_difference}' => $year_difference,
            '{file_editable_answer}' => $file_editable_answer,
        );

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Check if block should display based on conditions
     */
    public static function should_display($block, $post_id) {
        if (empty($block['conditions'])) return true;

        $conditions = $block['conditions'];

        // Get metadata
        $grade = get_post_meta($post_id, '_tkm_grade', true);
        $subject = get_post_meta($post_id, '_tkm_subject', true);
        $category_terms = wp_get_post_terms($post_id, 'file_category', array('fields' => 'names'));
        $category = !empty($category_terms) ? $category_terms[0] : '';
        $level = get_post_meta($post_id, '_tkm_level', true);
        $file_ext = get_post_meta($post_id, '_tkm_file_ext', true);

        // Check conditions
        if (!empty($conditions['grades'])) {
            if (!in_array($grade, $conditions['grades'])) return false;
        }

        if (!empty($conditions['subjects'])) {
            $match = false;
            foreach ($conditions['subjects'] as $allowed_subject) {
                if (stripos($subject, trim($allowed_subject)) !== false) {
                    $match = true;
                    break;
                }
            }
            if (!$match) return false;
        }

        if (!empty($conditions['categories'])) {
            if (!in_array($category, $conditions['categories'])) return false;
        }

        if (!empty($conditions['levels'])) {
            if (!in_array($level, $conditions['levels'])) return false;
        }

        if (!empty($conditions['file_extensions'])) {
            if (!in_array(strtolower($file_ext), array_map('strtolower', $conditions['file_extensions']))) return false;
        }

        return true;
    }

    /**
     * Render blocks for a post
     */
    public static function render_blocks($post_id, $selected_blocks = null) {
        $all_blocks = self::get_blocks();

        // If specific blocks selected, use only those
        if ($selected_blocks !== null && is_array($selected_blocks)) {
            $all_blocks = array_intersect_key($all_blocks, array_flip($selected_blocks));
        }

        // Sort by priority
        uasort($all_blocks, function($a, $b) {
            return ($a['priority'] ?? 999) - ($b['priority'] ?? 999);
        });

        $output = '';

        foreach ($all_blocks as $block_key => $block) {
            // Skip if disabled
            if (empty($block['enabled'])) continue;

            // Check conditions
            if (!self::should_display($block, $post_id)) continue;

            // Process template
            $content = self::process_variables($block['template'] ?? '', $post_id);
            $heading = !empty($block['heading']) ? self::process_variables($block['heading'], $post_id) : '';

            // Check block type for rich snippets
            $block_type = $block['type'] ?? 'standard';

            if ($block_type === 'howto') {
                $output .= self::render_howto_block($heading, $content, $post_id);
            } elseif ($block_type === 'faq') {
                $output .= self::render_faq_block($heading, $content, $post_id);
            } else {
                $output .= self::render_standard_block($heading, $content, $block_key);
            }
        }

        return $output;
    }

    /**
     * Render standard block
     */
    private static function render_standard_block($heading, $content, $block_key) {
        $output = '<div class="tkm-seo-block tkm-block-' . esc_attr($block_key) . '">';

        if ($heading) {
            $output .= '<h2>' . esc_html($heading) . '</h2>';
        }

        $output .= '<p>' . wp_kses_post($content) . '</p>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render How-To block with schema
     */
    private static function render_howto_block($heading, $content, $post_id) {
        // Parse steps (format: STEP_1: Description||STEP_2: Description)
        $steps_raw = explode('||', $content);
        $steps = array();

        foreach ($steps_raw as $step) {
            if (preg_match('/STEP_\d+:\s*(.+)/i', trim($step), $matches)) {
                $steps[] = trim($matches[1]);
            }
        }

        if (empty($steps)) return '';

        // Build schema
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $heading ?: 'How to Use This Resource',
            'step' => array(),
        );

        foreach ($steps as $index => $step_text) {
            $schema['step'][] = array(
                '@type' => 'HowToStep',
                'position' => $index + 1,
                'name' => 'Step ' . ($index + 1),
                'text' => $step_text,
            );
        }

        // Render
        $output = '<div class="tkm-seo-block tkm-block-howto">';
        $output .= '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';

        if ($heading) {
            $output .= '<h2>' . esc_html($heading) . '</h2>';
        }

        $output .= '<ol class="tkm-howto-steps">';
        foreach ($steps as $step_text) {
            $output .= '<li>' . esc_html($step_text) . '</li>';
        }
        $output .= '</ol>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render FAQ block with schema
     */
    private static function render_faq_block($heading, $content, $post_id) {
        // Parse Q&A (format: Q: Question||A: Answer||Q: Question||A: Answer)
        $parts = explode('||', $content);
        $faqs = array();

        for ($i = 0; $i < count($parts); $i += 2) {
            if (isset($parts[$i]) && isset($parts[$i + 1])) {
                $question = preg_replace('/^Q:\s*/i', '', trim($parts[$i]));
                $answer = preg_replace('/^A:\s*/i', '', trim($parts[$i + 1]));

                if ($question && $answer) {
                    $faqs[] = array('question' => $question, 'answer' => $answer);
                }
            }
        }

        if (empty($faqs)) return '';

        // Build schema
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array(),
        );

        foreach ($faqs as $faq) {
            $schema['mainEntity'][] = array(
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ),
            );
        }

        // Render
        $output = '<div class="tkm-seo-block tkm-block-faq">';
        $output .= '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';

        if ($heading) {
            $output .= '<h2>' . esc_html($heading) . '</h2>';
        }

        $output .= '<div class="tkm-faq-list">';
        foreach ($faqs as $faq) {
            $output .= '<div class="tkm-faq-item">';
            $output .= '<h3 class="tkm-faq-question">' . esc_html($faq['question']) . '</h3>';
            $output .= '<p class="tkm-faq-answer">' . wp_kses_post($faq['answer']) . '</p>';
            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Save blocks handler
     */
    public static function save_blocks() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'teacherske'));
        }

        check_admin_referer('tkm_save_content_blocks');

        $blocks = array();

        if (isset($_POST['blocks']) && is_array($_POST['blocks'])) {
            foreach ($_POST['blocks'] as $block_key => $block_data) {
                $block_key = sanitize_key($block_key);

                $blocks[$block_key] = array(
                    'title' => sanitize_text_field($block_data['title'] ?? ''),
                    'template' => wp_kses_post($block_data['template'] ?? ''),
                    'heading' => sanitize_text_field($block_data['heading'] ?? ''),
                    'enabled' => !empty($block_data['enabled']),
                    'priority' => intval($block_data['priority'] ?? 50),
                    'type' => sanitize_key($block_data['type'] ?? 'standard'),
                    'conditions' => array(),
                );

                // Parse conditions
                if (!empty($block_data['conditions'])) {
                    if (!empty($block_data['conditions']['subjects'])) {
                        $blocks[$block_key]['conditions']['subjects'] = array_map('trim', explode(',', $block_data['conditions']['subjects']));
                    }
                    if (!empty($block_data['conditions']['categories'])) {
                        $blocks[$block_key]['conditions']['categories'] = array_map('trim', explode(',', $block_data['conditions']['categories']));
                    }
                    if (!empty($block_data['conditions']['file_extensions'])) {
                        $blocks[$block_key]['conditions']['file_extensions'] = array_map('trim', explode(',', $block_data['conditions']['file_extensions']));
                    }
                }
            }
        }

        update_option('tkm_content_blocks', $blocks);

        wp_redirect(add_query_arg(array(
            'page' => 'tkm-settings',
            'tab' => 'content-blocks',
            'saved' => 'success'
        ), admin_url('options-general.php')));
        exit;
    }
}

// Initialize
TKM_Content_Blocks::init();
