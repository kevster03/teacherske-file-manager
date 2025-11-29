<?php
/**
 * Single Document Template - PERFECTED
 * Matching TeachersKE homepage design with working features
 */
if (!defined('ABSPATH')) exit;
get_header();

$post_id = get_the_ID();
$file_url = get_post_meta($post_id, '_tkm_file', true);
$level = get_post_meta($post_id, '_tkm_level', true);
$grade = get_post_meta($post_id, '_tkm_grade', true);
$subject = get_post_meta($post_id, '_tkm_subject', true);
$version = get_post_meta($post_id, '_tkm_version', true);
$description = get_post_meta($post_id, '_tkm_description', true);
$file_ext = get_post_meta($post_id, '_tkm_file_ext', true);
$file_size = get_post_meta($post_id, '_tkm_file_size', true);
$download_count = intval(get_post_meta($post_id, '_tkm_download_count', true));
$view_count = intval(get_post_meta($post_id, '_tkm_view_count', true));
$categories = wp_get_post_terms($post_id, 'file_category', array('fields' => 'names'));
$category = !empty($categories) ? $categories[0] : '';
$levels = tkm_get_levels();
$level_label = isset($levels[$level]) ? $levels[$level]['label'] : $level;
$file_type = $file_ext ? tkm_get_file_type_label($file_ext) : 'File';
$file_size_formatted = $file_size ? tkm_format_file_size($file_size) : '';
$featured_image = tkm_get_document_image($post_id, 'large');
$countdown = intval(get_option('tkm_countdown_duration', 10));

/**
 * Schema.org JSON-LD markup is now handled centrally in includes/frontend.php
 * via the tkm_add_schema_markup() function to prevent duplicate schema output.
 * This ensures clean SEO and compatibility with SEO plugins like RankMath.
 */
?>
<style>
/* FORCE OVERRIDE THEME STYLES */
.tkm-wrap,.tkm-wrap *{box-sizing:border-box !important}
.tkm-wrap{font-family:inherit !important;font-size:18px !important;line-height:1.6 !important;color:#2b1055 !important;max-width:1200px !important;margin:40px auto !important;padding:0 20px !important;display:grid !important;grid-template-columns:2.5fr 1fr !important;gap:30px !important;align-items:start !important;min-height:1500px !important;position:relative !important}
.tkm-main{min-width:0 !important;min-height:1500px !important}

/* MAIN CARD - MINIMAL BORDERS */
.tkm-card{background:#fff !important;border:1px solid #c92651 !important;border-radius:20px !important;padding:40px !important;margin-bottom:30px !important;box-shadow:0 4px 15px rgba(0,0,0,.18) !important}

/* TOP SECTION - IMAGE + META */
.tkm-top{display:grid !important;grid-template-columns:400px 1fr !important;gap:30px !important;margin-bottom:30px !important}
.tkm-img{border-radius:0px !important;overflow:hidden !important;border:0px solid #c92651 !important;height:300px !important}
.tkm-img img{width:100% !important;height:100% !important;object-fit:cover !important}

/* TITLE - SPAN NOT H1 */
.tkm-title{font-size:32px !important;font-weight:800 !important;color:#3b1a36 !important;margin:0 0 20px 0 !important;line-height:1.3 !important;display:block !important}

/* META BOX - LIGHT PURPLE SOLID */
.tkm-meta-box{background:#e0c8ff !important;padding:25px !important;border-radius:15px !important;border:1px solid #c92651 !important}

/* META GRID - 2 COLUMNS */
.tkm-meta-grid{display:grid !important;grid-template-columns:1fr 1fr !important;gap:15px !important}
.tkm-meta-item{display:flex !important;flex-direction:column !important;gap:5px !important}
.tkm-meta-label{font-size:14px !important;font-weight:700 !important;color:#c92651 !important;text-transform:uppercase !important}
.tkm-meta-value{font-size:17px !important;font-weight:600 !important;color:#3b1a36 !important}

/* DOWNLOAD SECTION - LIGHT YELLOW SOLID */
.tkm-download{background:#f2ffb2 !important;padding:30px !important;border-radius:15px !important;border:1px solid #1f1f1f !important;text-align:center !important;margin-bottom:30px !important}

/* DOWNLOAD STATUS - ABOVE BUTTON */
.tkm-status{font-size:16px !important;font-weight:600 !important;color:#2b1055 !important;margin-bottom:15px !important;min-height:24px !important;transition:all .3s !important}

/* DOWNLOAD BUTTON - MORPHS INTO PROGRESS BAR */
.tkm-btn-container{position:relative !important;width:100% !important;max-width:400px !important;margin:0 auto !important}
.tkm-btn{width:100% !important;background:#c92651 !important;color:#fff !important;border:none !important;border-radius:12px !important;padding:18px 50px !important;font-size:20px !important;font-weight:700 !important;cursor:pointer !important;transition:all .3s !important;display:block !important;text-decoration:none !important;box-shadow:0 4px 12px rgba(201,38,81,.3) !important;position:relative !important;overflow:hidden !important}
.tkm-btn:hover{background:#a01d3f !important;transform:translateY(-2px) !important;box-shadow:0 6px 18px rgba(201,38,81,.4) !important}
.tkm-btn:disabled{cursor:not-allowed !important}
.tkm-btn.green{background:#28a745 !important;box-shadow:0 4px 12px rgba(40,167,69,.3) !important}

/* PROGRESS FILL - INSIDE BUTTON */
.tkm-btn-progress{position:absolute !important;left:0 !important;top:0 !important;height:100% !important;background:rgba(255,255,255,0.2) !important;transition:width .3s linear !important;border-radius:12px !important}

/* BUTTON TEXT */
.tkm-btn-text{position:relative !important;z-index:2 !important}

/* FALLBACK LINK */
.tkm-fallback{margin-top:15px !important;font-size:15px !important;font-weight:600 !important;color:#2b1055 !important}
.tkm-fallback a{color:#c92651 !important;font-weight:700 !important;text-decoration:underline !important;cursor:pointer !important}
.tkm-fallback a:hover{color:#a01d3f !important}

/* DESCRIPTION - LIGHT BLUE SOLID */
.tkm-description{background:#c6e0f2 !important;padding:30px !important;border-radius:15px !important;border:1px solid #3b1a36 !important;margin-bottom:30px !important}
.tkm-description h2{font-size:28px !important;font-weight:700 !important;color:#3b1a36 !important;margin:0 0 15px 0 !important}
.tkm-description p{font-size:17px !important;font-weight:400 !important;color:#3b1a36 !important;line-height:1.7 !important;margin:0 0 10px 0 !important}

/* META PILLS - ALTERNATING COLORS */
.tkm-pills{display:grid !important;grid-template-columns:repeat(2,1fr) !important;gap:15px !important;margin-bottom:30px !important}
.tkm-pill{padding:15px 20px !important;border-radius:12px !important;border:1px solid #3b1a36 !important;font-size:16px !important;font-weight:600 !important;display:flex !important;align-items:center !important;gap:10px !important}
.tkm-pill:nth-child(1){background:#e0c8ff !important}
.tkm-pill:nth-child(2){background:#c6e0f2 !important}
.tkm-pill:nth-child(3){background:#e0c8ff !important}
.tkm-pill:nth-child(4){background:#c6e0f2 !important}
.tkm-pill-label{color:#c92651 !important;font-weight:700 !important}
.tkm-pill-value{color:#3b1a36 !important}

/* RELATED RESOURCES */
.tkm-related{background:#fff !important;padding:35px !important;border-radius:15px !important;border:0px solid #c92651 !important}
.tkm-related h2{font-size:28px !important;font-weight:700 !important;color:#2b1055 !important;margin:0 0 25px 0 !important;display:flex !important;align-items:center !important;gap:10px !important}
.tkm-rel-grid{display:grid !important;grid-template-columns:repeat(2,1fr) !important;gap:20px !important}
.tkm-rel-card{padding:20px !important;border-radius:12px !important;border:1px solid #3b1a36 !important;text-decoration:none !important;display:flex !important;gap:15px !important;transition:all .3s !important}
.tkm-rel-card:nth-child(odd){background:#e6d1b2 !important}
.tkm-rel-card:nth-child(even){background:#c6e0f2 !important}
.tkm-rel-card:hover{transform:translateY(-3px) !important;box-shadow:0 6px 15px rgba(0,0,0,.12) !important}
.tkm-rel-img{width:100px !important;height:80px !important;border-radius:8px !important;border:1px solid #c92651 !important;object-fit:cover!important;flex-shrink:0 !important}
.tkm-rel-content h3{font-size:16px !important;font-weight:700 !important;color:#2b1055 !important;margin:0 0 8px 0 !important;line-height:1.4 !important}
.tkm-rel-meta{font-size:13px !important;font-weight:600 !important;color:#666 !important}

/* STICKY SIDEBAR - Uses Theme's Theia Sticky Sidebar Plugin */
.tkm-sidebar{position:relative !important;overflow:visible !important;box-sizing:border-box !important;min-height:1px !important;align-self:start !important}
.tkm-sidebar .theiaStickySidebar{padding-top:0px !important;padding-bottom:1px !important}
.tkm-sidebar .custom-well{background:#f2dec1 !important;padding:25px !important;border-radius:15px !important;border:0px solid #c92651 !important}
.tkm-sidebar h3{font-size:22px !important;font-weight:700 !important;color:#2b1055 !important;margin:0 0 20px 0 !important;padding-bottom:15px !important;border-bottom:2px solid #c92651 !important}
.tkm-sidebar .widget,.tkm-sidebar .sidebar-widget,.tkm-sidebar .widget_block{background:#fff !important;padding:20px !important;border-radius:10px !important;border:1px solid #c92651 !important;margin-bottom:20px !important}
.tkm-sidebar .widget:last-child,.tkm-sidebar .sidebar-widget:last-child{margin-bottom:0 !important}
.tkm-sidebar .widget h4,.tkm-sidebar .sidebar-widget h4,.tkm-sidebar .widget-title{font-size:18px !important;font-weight:700 !important;color:#c92651 !important;margin:0 0 12px 0 !important}
.tkm-sidebar .widget p,.tkm-sidebar .widget li,.tkm-sidebar .sidebar-widget p,.tkm-sidebar .sidebar-widget li{font-size:15px !important;font-weight:400 !important;color:#2b1055 !important;line-height:1.6 !important;margin:0 0 10px 0 !important}
.tkm-sidebar .widget ul,.tkm-sidebar .sidebar-widget ul{margin:0 !important;padding:0 0 0 20px !important}
/* Ensure all widget types get white background */
.tkm-sidebar > div > div > *{background:#fff !important;padding:20px !important;border-radius:10px !important;border:1px solid #c92651 !important;margin-bottom:20px !important}

/* RESPONSIVE */
@media(max-width:900px){
.tkm-wrap{grid-template-columns:1fr !important;min-height:auto !important}
.tkm-main{min-height:auto !important}
.tkm-top{grid-template-columns:1fr !important}
.tkm-img{height:250px !important}
.tkm-sidebar{position:static !important;overflow:visible !important;margin-top:30px !important}
.tkm-sidebar .theiaStickySidebar{position:static !important;transform:none !important;width:auto !important;left:auto !important;top:auto !important}
.tkm-sidebar .custom-well{margin:0 !important}
.tkm-rel-grid{grid-template-columns:1fr !important}
}
@media(max-width:600px){
.tkm-card{padding:25px !important}
.tkm-title{font-size:26px !important}
.tkm-meta-grid,.tkm-pills{grid-template-columns:1fr !important}
}

/* ACCESSIBILITY */
.tkm-btn:focus,.tkm-rel-card:focus{outline:3px solid #c92651 !important;outline-offset:3px !important}
</style>

<div class="tkm-wrap">
<div class="tkm-main">

<!-- MAIN CARD -->
<article class="tkm-card">

<!-- TOP: IMAGE + META -->
<div class="tkm-top">
<div class="tkm-img">
<img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="eager" width="400" height="300">
</div>
<div class="tkm-meta-box">
<span class="tkm-title"><?php the_title(); ?></span>
<div class="tkm-meta-grid">
<div class="tkm-meta-item">
<span class="tkm-meta-label">Subject:</span>
<span class="tkm-meta-value"><?php echo esc_html($subject); ?></span>
</div>
<div class="tkm-meta-item">
<span class="tkm-meta-label">Level:</span>
<span class="tkm-meta-value"><?php echo esc_html($level_label); ?></span>
</div>
<div class="tkm-meta-item">
<span class="tkm-meta-label">Grade:</span>
<span class="tkm-meta-value"><?php echo esc_html($grade); ?></span>
</div>
<div class="tkm-meta-item">
<span class="tkm-meta-label">Type:</span>
<span class="tkm-meta-value"><?php echo esc_html($file_type); ?></span>
</div>
<?php if($file_size_formatted): ?>
<div class="tkm-meta-item">
<span class="tkm-meta-label">Size:</span>
<span class="tkm-meta-value"><?php echo esc_html($file_size_formatted); ?></span>
</div>
<?php endif; ?>
<?php if($category): ?>
<div class="tkm-meta-item">
<span class="tkm-meta-label">Category:</span>
<span class="tkm-meta-value"><?php echo esc_html($category); ?></span>
</div>
<?php endif; ?>
</div>
</div>
</div>

<!-- EZOIC AD ZONE 1: ABOVE DOWNLOAD BUTTON -->
<!-- HIGHEST REVENUE ZONE - Users wait here during countdown -->
<!-- Recommended sizes: 728x90 (Leaderboard), 336x280 (Large Rectangle), 300x250 (Medium Rectangle) -->
<!-- Example: <div id="ezoic-pub-ad-placeholder-101"></div> -->

<!-- DOWNLOAD SECTION -->
<div class="tkm-download">
<div class="tkm-status" id="tkm-status"></div>
<div class="tkm-btn-container">
<button class="tkm-btn" id="tkm-btn" data-file="<?php echo esc_attr($file_url); ?>" data-post="<?php echo esc_attr($post_id); ?>" data-title="<?php echo esc_attr(get_the_title()); ?>">
<div class="tkm-btn-progress" id="tkm-btn-progress"></div>
<span class="tkm-btn-text" id="tkm-btn-text">Download it Free</span>
</button>
</div>
<div class="tkm-fallback" id="tkm-fallback"></div>
</div>

<!-- EZOIC AD ZONE 2: BELOW DOWNLOAD BUTTON -->
<!-- HIGH VALUE ZONE - Users look here after clicking -->
<!-- Recommended sizes: 300x250 (Medium Rectangle), 336x280 (Large Rectangle) -->
<!-- Example: <div id="ezoic-pub-ad-placeholder-102"></div> -->

<?php if($description): ?>
<!-- DESCRIPTION -->
<div class="tkm-description">
<h2>Description</h2>
<?php echo wpautop(wp_kses_post($description)); ?>
</div>
<?php endif; ?>

<!-- META PILLS -->
<div class="tkm-pills">
<div class="tkm-pill">
<span class="tkm-pill-label">👤 Author:</span>
<span class="tkm-pill-value"><?php the_author(); ?></span>
</div>
<div class="tkm-pill">
<span class="tkm-pill-label">📅 Date:</span>
<span class="tkm-pill-value"><?php echo get_the_date(); ?></span>
</div>
<?php if($version): ?>
<div class="tkm-pill">
<span class="tkm-pill-label">🏷️ Version:</span>
<span class="tkm-pill-value"><?php echo esc_html($version); ?></span>
</div>
<?php endif; ?>
<div class="tkm-pill">
<span class="tkm-pill-label">👁️ Views:</span>
<span class="tkm-pill-value"><?php echo number_format($view_count); ?></span>
</div>
</div>

</article>

<?php
// ==========================================
// AUTO-DISPLAY SELECTED SEO CONTENT BLOCKS
// ==========================================
$selected_blocks = get_post_meta($post_id, '_tkm_selected_blocks', true) ?: array();

if (!empty($selected_blocks)) {
    global $wpdb;
    $block_ids = implode(',', array_map('intval', $selected_blocks));
    $blocks = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}tkm_content_blocks
        WHERE id IN ($block_ids) AND active = 1
        ORDER BY display_order ASC
    ");

    // Display each selected block
    foreach ($blocks as $block) {
        $bg_color = !empty($block->bg_color) ? $block->bg_color : '#c6e0f2';

        echo '<div class="tkm-card tkm-content-block" style="background:' . esc_attr($bg_color) . ' !important;">';
        echo '<h2 style="font-size:28px !important;font-weight:700 !important;color:#3b1a36 !important;margin:0 0 20px 0 !important;">' . esc_html($block->block_title) . '</h2>';

        // Check if this is a structured FAQ or How-To block
        if ($block->has_schema && !empty($block->structured_data)) {
            $structured = json_decode($block->structured_data, true);

            if ($block->schema_type === 'FAQPage' && !empty($structured['faqs'])) {
                // Display FAQ items
                foreach ($structured['faqs'] as $faq) {
                    echo '<div style="margin-bottom:25px !important;">';
                    echo '<h3 style="font-size:20px !important;font-weight:700 !important;color:#c92651 !important;margin:0 0 10px 0 !important;">' . esc_html($faq['question']) . '</h3>';
                    echo '<div style="font-size:16px !important;line-height:1.8 !important;color:#3b1a36 !important;">' . wpautop(wp_kses_post($faq['answer'])) . '</div>';
                    echo '</div>';
                }
            } elseif ($block->schema_type === 'HowTo' && !empty($structured['steps'])) {
                // Display How-To steps
                echo '<div style="counter-reset:step-counter;">';
                foreach ($structured['steps'] as $step) {
                    echo '<div style="margin-bottom:25px !important;padding-left:40px !important;position:relative !important;">';
                    echo '<div style="position:absolute !important;left:0 !important;top:0 !important;width:30px !important;height:30px !important;background:#c92651 !important;color:#fff !important;border-radius:50% !important;display:flex !important;align-items:center !important;justify-content:center !important;font-weight:700 !important;">' . esc_html($step['position']) . '</div>';
                    echo '<h3 style="font-size:20px !important;font-weight:700 !important;color:#c92651 !important;margin:0 0 10px 0 !important;">' . esc_html($step['name']) . '</h3>';
                    echo '<div style="font-size:16px !important;line-height:1.8 !important;color:#3b1a36 !important;">' . wpautop(wp_kses_post($step['text'])) . '</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                // Fallback to regular content
                echo '<div style="font-size:16px !important;line-height:1.8 !important;color:#3b1a36 !important;">' . wpautop(wp_kses_post($block->block_content)) . '</div>';
            }
        } else {
            // Regular content block - render HTML properly
            echo '<div style="font-size:16px !important;line-height:1.8 !important;color:#3b1a36 !important;">' . wpautop(wp_kses_post($block->block_content)) . '</div>';
        }

        echo '</div>';
    }
}
?>

<!-- EZOIC AD ZONE 3: BELOW DESCRIPTION -->
<!-- CONTENT BREAK ZONE - Natural reading pause -->
<!-- Recommended sizes: 300x250 (Medium Rectangle), Native Ad Unit -->
<!-- Example: <div id="ezoic-pub-ad-placeholder-103"></div> -->

<?php
$related_count = intval(get_option('tkm_related_files_count', 8));
$related_args = array(
'post_type' => 'teacher_document',
'posts_per_page' => $related_count,
'post__not_in' => array($post_id),
'meta_query' => array('relation' => 'OR')
);
if($level && $subject) $related_args['meta_query'][] = array('relation'=>'AND',array('key'=>'_tkm_level','value'=>$level),array('key'=>'_tkm_subject','value'=>$subject));
if($grade) $related_args['meta_query'][] = array('key'=>'_tkm_grade','value'=>$grade);
$related_query = new WP_Query($related_args);
if($related_query->have_posts()): ?>

<!-- RELATED RESOURCES -->
<div class="tkm-related">
<h2>📚 Related Resources</h2>
<div class="tkm-rel-grid">
<?php while($related_query->have_posts()): $related_query->the_post(); ?>
<a href="<?php the_permalink(); ?>" class="tkm-rel-card">
<img src="<?php echo esc_url(tkm_get_document_image(get_the_ID(),'medium')); ?>" alt="" class="tkm-rel-img" loading="lazy" width="100" height="80">
<div class="tkm-rel-content">
<h3><?php the_title(); ?></h3>
<div class="tkm-rel-meta">
<?php echo esc_html(strtoupper(get_post_meta(get_the_ID(),'_tkm_file_ext',true))); ?> •
<?php echo esc_html(get_post_meta(get_the_ID(),'_tkm_grade',true)); ?> •
<?php echo number_format(intval(get_post_meta(get_the_ID(),'_tkm_view_count',true))); ?> views
</div>
</div>
</a>
<?php endwhile; ?>
</div>
</div>

<?php endif; wp_reset_postdata(); ?>

</div>

<?php if(is_active_sidebar('tkm_document_sidebar')): ?>
<!-- STICKY SIDEBAR - Uses Theme's Theia Sticky Sidebar -->
<aside class="tkm-sidebar">
<div class="theiaStickySidebar">
<div class="custom-well sidebar-nav">

<!-- ========================================= -->
<!-- EZOIC SIDEBAR AD RECOMMENDATIONS         -->
<!-- ========================================= -->

<!-- EZOIC AD ZONE 1: SIDEBAR TOP (HIGHEST PRIORITY) -->
<!-- STICKY ADVANTAGE: This ad stays visible during scroll! -->
<!-- Desktop Recommended: 300x600 (Half Page) - Best EPMV -->
<!-- Alternative: 300x250 (Medium Rectangle) stacked x2 -->
<!-- Mobile: 300x250 (one unit) or disable on mobile -->
<!-- Placement: <div id="ezoic-pub-ad-placeholder-104"></div> -->
<!-- Performance: ⭐⭐⭐⭐⭐ Excellent viewability & engagement -->

<h3>Free Resources</h3>

<!-- EZOIC AD ZONE 2: AFTER HEADING, BEFORE WIDGETS -->
<!-- Natural break point - high user attention -->
<!-- Recommended: 300x250 (Medium Rectangle) -->
<!-- Mobile: 300x250 (works well) -->
<!-- Placement: <div id="ezoic-pub-ad-placeholder-105"></div> -->
<!-- Performance: ⭐⭐⭐⭐ Great engagement -->

<?php dynamic_sidebar('tkm_document_sidebar'); ?>

<!-- EZOIC AD ZONE 3: SIDEBAR BOTTOM -->
<!-- Catches engaged users who scrolled through content -->
<!-- Recommended: 300x250 (Medium Rectangle) -->
<!-- Alternative: 728x90 (Leaderboard) if space allows -->
<!-- Mobile: 300x250 or skip if too many ads -->
<!-- Placement: <div id="ezoic-pub-ad-placeholder-106"></div> -->
<!-- Performance: ⭐⭐⭐ Good for engaged users -->

<!-- ========================================= -->
<!-- EZOIC SIDEBAR STRATEGY NOTES             -->
<!-- ========================================= -->
<!--
OPTIMAL SETUP (Desktop):
1. Top: 300x600 Half Page (sticky, high visibility)
2. Middle: 300x250 after widgets (natural break)
3. Bottom: Skip or light ad (prevent clutter)

OPTIMAL SETUP (Mobile):
1. Top: 300x250 Medium Rectangle
2. Skip middle and bottom (prevent ad fatigue)

STICKY SIDEBAR ADVANTAGE:
- Top ad remains visible during entire scroll
- Increases impressions without adding ads
- Users see ad while reading content
- Best EPMV location in sidebar

EZOIC AUTO VS MANUAL:
- Auto Placeholders: Let Ezoic test positions
- Manual: Use specific IDs for control
- Recommended: Start with auto, optimize based on data

DENSITY RULES:
- Desktop: Max 2-3 sidebar ads
- Mobile: Max 1 sidebar ad
- Total page: Follow Ezoic guidelines
- Monitor EPMV vs user experience

AD SIZES PRIORITY:
1. 300x600 (Half Page) - Best EPMV
2. 300x250 (Medium Rectangle) - Versatile
3. 160x600 (Wide Skyscraper) - Alternative
4. Avoid 120x600 (too narrow)

INTEGRATION STEPS:
1. Go to Ezoic Dashboard → Ad Tester
2. Add manual placeholders or enable auto
3. Use CSS class: ezoic-sidebar-ad
4. Test on mobile and desktop
5. Monitor EPMV in reporting

EXAMPLE IMPLEMENTATION:
<div class="ezoic-sidebar-ad">
  <div id="ezoic-pub-ad-placeholder-104"></div>
</div>
-->

</div>
</div>
</aside>
<?php endif; ?>

</div>

<script>
// Initialize Theia Sticky Sidebar on our custom sidebar - Desktop only
jQuery(document).ready(function($) {
    var $sidebar = $('.tkm-sidebar');
    var initialized = false;

    function initSticky() {
        if (typeof $.fn.theiaStickySidebar !== 'undefined' && $sidebar.length && $(window).width() > 900) {
            if (!initialized) {
                $sidebar.theiaStickySidebar({
                    additionalMarginTop: 30,
                    additionalMarginBottom: 30,
                    updateSidebarHeight: true,
                    minWidth: 900
                });
                initialized = true;
            }
        } else if (initialized && $(window).width() <= 900) {
            // Destroy on mobile to prevent footer overlap
            $sidebar.trigger('detach.TheiaStickySidebar');
            initialized = false;
        }
    }

    // Initialize on load
    initSticky();

    // Re-check on window resize (debounced)
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initSticky();
        }, 250);
    });
});
</script>

<?php get_footer(); ?>
