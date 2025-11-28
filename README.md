# TeachersKE File Manager

**Version:** 6.0.0
**Requires:** WordPress 5.0+
**Tested up to:** WordPress 6.4
**License:** GPLv2 or later

Ultra-optimized WordPress document management plugin designed for educational content. Fast, SEO-ready, and Ezoic-friendly.

---

## 🚀 Features

### Core Document Management
- **Custom Post Type:** `teacher_document` with full WordPress integration
- **Hierarchical File Categories:** Filter and organize documents
- **Meta Fields:** Level, Grade, Subject, Version, Description
- **Featured Images:** Support for document cover images
- **Download Tracking:** IP-based duplicate prevention
- **View Tracking:** Separate view counter for analytics
- **File Detection:** Auto-detect file type and size

### Advanced Features
- **Template System:** Pre-fill document fields from saved templates
- **Subject Management:** Configure subjects per education level
- **Dynamic Dropdowns:** Grades update based on selected level
- **AJAX Quick Save:** Save without page reload (Ctrl+S / Cmd+S)
- **Sticky Sidebar:** Widget-ready sidebar area on single documents
- **Progress Bar Loader:** Visual countdown before download

### CSV Bulk Import
- **Smart Field Mapping:** Auto-detect column names
- **Batch Processing:** Import thousands of documents (30 rows per batch)
- **Grade Normalization:** Handles "grade 7", "grade7", "Grade 7" formats
- **Featured Image Download:** Automatically fetch and attach images from URLs
- **Single-Page Workflow:** No redirects, smooth UX
- **Real-Time Progress:** Visual feedback during import

### SEO & Performance
- **Schema.org Markup:** DigitalDocument structured data
- **Optimized Queries:** Fast database operations
- **Minified Assets:** CSS/JS optimization
- **No Bloat:** Only essential features
- **Ezoic Compatible:** See Ezoic recommendations below

---

## 📦 Installation

1. **Upload** the plugin folder to `/wp-content/plugins/`
2. **Activate** through the 'Plugins' menu in WordPress
3. **Configure** subjects in Settings → File Manager
4. **Start adding** documents!

---

## 🎯 Quick Start Guide

### Adding a Document

1. Go to **Documents → Add New**
2. Enter document **Title**
3. Upload or enter **File URL**
4. Select **Education Level** (dropdown updates grades)
5. Select **Grade**
6. *(Optional)* Add Subject, Description, Version
7. *(Optional)* Set Featured Image
8. **Publish!**

### Bulk Import via CSV

1. Go to **Documents → Import CSV**
2. **Step 1:** Upload your CSV file
3. **Step 2:** Map CSV columns to document fields
   - Auto-mapping detects common column names
   - Adjust manually if needed
4. **Step 3:** Review and start import
   - Watch real-time progress
   - See any errors immediately

#### CSV Format

Required columns: `title`, `file`, `level`, `grade`
Optional columns: `description`, `subject`, `version`, `featured_image`, `author`

**Sample CSV:**
```csv
title,description,file,level,grade,subject,version,featured_image
"Grade 7 Math Notes","Complete algebra guide","https://example.com/file.pdf","junior_secondary","Grade 7","Mathematics","2026 Edition","https://example.com/cover.jpg"
```

#### Education Levels
- `early_years` - PP1, PP2
- `lower_primary` - Grade 1, 2, 3
- `upper_primary` - Grade 4, 5, 6
- `junior_secondary` - Grade 7, 8, 9
- `senior_secondary` - Grade 10, 11, 12

---

## ⚙️ Configuration

### Settings (Settings → File Manager)

#### General Tab
- **Version Range:** Start/end years for document editions
- **Related Files Count:** Number of suggestions to show (4-12)
- **Countdown Duration:** Seconds before download starts (0-60)

#### Colors & Borders Tab
- **Primary Color:** Main brand color
- **Secondary Color:** Accent color
- **Background Colors:** 4 customizable background colors
- **Border Color & Size:** Customize document card borders

#### Subjects Tab
- Configure subjects for each education level
- One subject per line
- Updates dynamic dropdowns in document editor

#### Import/Export Tab
- Export all documents to JSON
- Import documents from JSON
- Access CSV import wizard

#### Data Tab
- Reset all download counts
- Configure data removal on uninstall

---

## 🏎️ Performance Optimizations

### Database
- **Indexed Queries:** Fast post retrieval
- **Transient Caching:** CSV uploads cached for 1 hour
- **Batch Processing:** Import 30 rows at a time (prevents timeouts)
- **IP Hashing:** Efficient duplicate download prevention

### Frontend
- **Lazy Loading Ready:** Compatible with lazy load plugins
- **Minimal HTTP Requests:** Combined assets
- **No External Dependencies:** All assets self-hosted
- **Cache-Friendly:** Version-based asset URLs

### Admin
- **AJAX Operations:** Quick save, template apply, subject updates
- **Disabled Auto-save:** Faster editor on slow connections
- **Minimal Meta Boxes:** Only essential boxes loaded
- **Classic Editor:** Faster than Gutenberg for custom post types

---

## 🌐 Ezoic Compatibility & Recommendations

### ✅ Ezoic-Friendly Features

#### 1. Clean HTML Structure
- Semantic markup throughout
- Proper heading hierarchy (H1 → H2 → H3)
- No inline styles in content areas
- Mobile-responsive grid layouts

#### 2. Ad Placeholder Friendly
The plugin uses clean section-based layouts that work perfectly with Ezoic's automatic and manual placeholders:

**Single Document Page Structure:**
```
┌─────────────────────────────────┐
│ Header (your theme)             │
├─────────────────────────────────┤
│ [Ezoic Placeholder 1]           │ ← Top of content
├─────────────────────────────────┤
│ Document Title (H1)             │
│ Meta Info (Level, Grade, etc.)  │
├─────────────────────────────────┤
│ [Ezoic Placeholder 2]           │ ← After title
├─────────────────────────────────┤
│ Description Section             │
├─────────────────────────────────┤
│ [Ezoic Placeholder 3]           │ ← Mid-content
├─────────────────────────────────┤
│ Download Button + Progress      │
├─────────────────────────────────┤
│ [Ezoic Placeholder 4]           │ ← Before sidebar
├─────────────────────────────────┤
│ Sticky Sidebar (widgets)        │
│  - Can contain Ezoic ads        │
├─────────────────────────────────┤
│ Related Documents Section       │
├─────────────────────────────────┤
│ [Ezoic Placeholder 5]           │ ← After content
└─────────────────────────────────┘
```

#### 3. Fast Page Load Times
- **Minimal CSS/JS:** Only loads what's needed
- **No jQuery Dependencies:** Vanilla JS where possible
- **Optimized Images:** Supports WebP, lazy loading
- **Database Efficiency:** Indexed queries, minimal overhead

#### 4. User Experience (Ezoic EPMV Factors)
- **High Engagement:** Download countdown encourages page time
- **Related Content:** Increases page views per session
- **Clear CTAs:** Download buttons are prominent
- **Mobile Optimized:** Responsive design on all devices

#### 5. SEO (Helps Ezoic Traffic)
- **Schema.org Markup:** DigitalDocument structured data
- **Meta Descriptions:** Auto-generated from description field
- **Semantic HTML:** Proper heading structure
- **Fast Loading:** Improves Core Web Vitals

### 🎯 Ezoic Placement Recommendations

#### Best Performing Ad Locations

**1. Top of Content (Above Title)**
- **Placeholder:** Use Ezoic auto placeholder or manual
- **Format:** 728×90 Leaderboard (Desktop), 320×50 (Mobile)
- **Performance:** High viewability, catches users immediately

**2. After Title/Meta Info**
- **Placeholder:** Manual "After Title" placeholder
- **Format:** 300×250 Medium Rectangle
- **Performance:** Natural reading flow, high engagement

**3. Mid-Description**
- **Placeholder:** "Mid-Content" auto placeholder
- **Format:** In-content ad unit (responsive)
- **Performance:** Users engaged with content, good CTR

**4. Sidebar (Sticky) - ⭐ PREMIUM PLACEMENT**
- **Why It's Special:** Sidebar uses Theia Sticky Sidebar - ads stay visible during ENTIRE scroll!
- **Placement Zones:**
  - **Top (Highest Priority):** 300×600 Half Page - Best EPMV
  - **Middle (After Widgets):** 300×250 Medium Rectangle - Natural break
  - **Bottom (Optional):** 300×250 or skip to prevent clutter
- **Desktop Strategy:**
  - Primary: 300×600 at top (stays sticky)
  - Secondary: 300×250 after widgets
  - Max 2-3 sidebar ads total
- **Mobile Strategy:**
  - Only 300×250 at top
  - Disable sticky on mobile (auto-handled)
  - Skip middle/bottom ads
- **Performance:** ⭐⭐⭐⭐⭐ Excellent - persistent visibility boosts impressions without extra ads
- **Implementation:** See template comments for 3 strategic zones (104, 105, 106)

**5. Above Related Documents**
- **Placeholder:** "End of Content" auto placeholder
- **Format:** 728×90 or 970×250 Billboard
- **Performance:** Catches users before they leave

#### Ezoic Dashboard Setup

**Step 1: Enable Auto Placeholders**
```
Ezoic Dashboard → Ad Tester → Placeholders
✅ Enable Top of Content
✅ Enable Mid Content
✅ Enable End of Content
✅ Enable Sidebar
```

**Step 2: Exclude Download Button Area**
```
CSS Selector to Exclude: .tkm-download-section
Reason: Prevents ads from competing with CTA
```

**Step 3: Set Density Rules**
```
Mobile: Maximum 3 ads per page
Desktop: Maximum 5 ads per page
Helps maintain user experience
```

#### Ezoic EPMV Optimization Tips

**1. Increase Session Duration**
- Use countdown timer (10 seconds default)
- Add related documents (encourages more clicks)
- Compelling descriptions keep users reading

**2. Improve Bounce Rate**
- Related documents increase internal linking
- Clear navigation to other content
- Sidebar widgets for additional engagement

**3. Boost Pages Per Session**
- "You might also like" suggestions
- Category browsing
- Template-based content organization

**4. Mobile Optimization**
- Responsive design (works on all devices)
- Touch-friendly buttons
- Fast mobile load times

### 📊 Ezoic Analytics Integration

#### Track Document Engagement
Add custom dimensions in Google Analytics 4:
```
File Level: {{meta.level}}
File Grade: {{meta.grade}}
File Subject: {{meta.subject}}
```

This helps Ezoic understand which content types perform best.

#### Monitor Download Metrics
The plugin tracks:
- View count (displays to visitors)
- Download count (internal analytics only)
- Use this data to identify top-performing content

---

## 🔌 Hooks & Filters

### Actions
```php
// After document is saved
do_action('tkm_document_saved', $post_id);

// After CSV row is imported
do_action('tkm_row_imported', $post_id, $row_data);
```

### Filters
```php
// Modify education levels
add_filter('tkm_education_levels', function($levels) {
    // Add custom level
    $levels['custom_level'] = array(
        'label' => 'Custom Level',
        'grades' => array('Custom 1', 'Custom 2')
    );
    return $levels;
});

// Modify subject list for a level
add_filter('tkm_subjects_for_level', function($subjects, $level) {
    if ($level === 'junior_secondary') {
        $subjects[] = 'Computer Science';
    }
    return $subjects;
}, 10, 2);

// Customize download button text
add_filter('tkm_download_button_text', function($text) {
    return 'Get Free Resource';
});
```

---

## 🐛 Troubleshooting

### CSV Import Issues

**Problem:** "Permission denied" error
**Solution:** Go to Users → Your Profile → Check if you have "edit_posts" capability

**Problem:** Featured images not downloading
**Solution:** Check that URLs are publicly accessible, and server allows remote file downloads

**Problem:** Grade validation errors
**Solution:** Use exact formats: "Grade 7" not "grade7" or "class 7"

### Performance Issues

**Problem:** Slow admin dashboard
**Solution:** Check for plugin conflicts, disable unused meta boxes

**Problem:** Import timing out
**Solution:** Reduce batch size in code or import smaller files

### Display Issues

**Problem:** Sticky sidebar not working
**Solution:** Check theme compatibility, ensure proper CSS support for `position: sticky`

**Problem:** Download tracking not working
**Solution:** Check `_tkm_download_count` meta field, verify hooks are firing

---

## 📝 Changelog

### Version 6.0.0 (Current - Stable Release)
- **Removed:** Fallback featured image feature (deferred to future release)
- **Removed:** Category support from CSV import (deferred to future release)
- **Removed:** All debugging console.log statements
- **Optimized:** Code cleanup for better performance
- **Maintained:** All core features (CSV import, featured images from URL, subject management)
- **Focus:** Stable, battle-tested features only

### Version 5.3.5
- Added debugging for CSV import
- Forced asset cache refresh

### Version 5.3.4
- Complete CSV import redesign (single-page workflow)
- Fixed grade validation with normalization
- Improved error handling

### Version 5.3.3
- Added view tracking (separate from downloads)
- Fixed sticky sidebar issues
- Improved download button for logged-out users

---

## 🤝 Support

For issues, feature requests, or contributions:
- **Issues:** [GitHub Issues](https://github.com/your-repo/issues)
- **Documentation:** Check this README first
- **Support Forum:** WordPress.org support forum

---

## 📄 License

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version.

This plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

---

## 🙏 Credits

Developed by TeachersKE for educational content management.

Built with:
- WordPress REST API
- Custom Post Types API
- WordPress Transients API
- WordPress Media Library
- Schema.org Structured Data

---

## 🎓 Best Practices

### For Maximum Ezoic EPMV

1. **Content Quality:** Write detailed descriptions (200+ words)
2. **Engagement:** Use countdown timer to increase time on page
3. **Related Content:** Add 8+ related documents
4. **Mobile-First:** Test on mobile devices
5. **Page Speed:** Use caching plugins (WP Super Cache, W3 Total Cache)
6. **Images:** Add featured images to all documents (improves CTR)
7. **Categories:** Organize content into clear categories
8. **Internal Linking:** Use related documents feature

### For Best User Experience

1. **Clear Titles:** Descriptive, specific document names
2. **Accurate Metadata:** Correct level, grade, subject
3. **Quality Files:** Ensure file links work and are relevant
4. **Descriptions:** Explain what's in the document
5. **Versions:** Keep documents up-to-date with edition info
6. **Organization:** Use categories and subjects consistently

---

**Made with ❤️ for Teachers**

Version 6.0.0 | TeachersKE File Manager
