# Changelog

All notable changes to TeachersKE File Manager will be documented in this file.

## [6.0.0] - 2025-11-28

### 🔥 MAJOR UPDATE - SEO Plugin Compatibility

**BREAKING CHANGES:**
- Plugin no longer outputs its own meta description tags
- SEO plugins (RankMath, Yoast, AIOSEO, etc.) now have full control

### Fixed
- **Critical SEO Bug:** Removed duplicate meta descriptions causing SEO tool errors
- **Duplicate Schema:** Removed duplicate JSON-LD schema markup from template
- Meta tags (`<meta name="description">`, OpenGraph, Twitter Cards) now handled exclusively by SEO plugins

### Changed
- **Meta Tag Handling:** Disabled `tkm_add_meta_tags()` function to prevent conflicts
- **Schema Output:** Consolidated to single schema.org output in `includes/frontend.php`
- **Description:** Updated to include "RankMath compatible"

### Preserved
- ✅ All custom meta fields remain intact (`_tkm_description`, `_tkm_grade`, `_tkm_subject`, etc.)
- ✅ Schema.org JSON-LD structured data still outputs for rich snippets
- ✅ Full compatibility with TKM-table-builder plugin
- ✅ All download tracking and functionality unchanged

### Migration Notes
**For existing installations:**
1. Clear all caches (WordPress, CDN, browser) after update
2. Configure your SEO plugin to use custom fields:
   - Meta Description: `%_tkm_description%`
   - Keywords: Include `%_tkm_grade%`, `%_tkm_subject%`
3. Resubmit sitemap to Google Search Console (optional)
4. Run SEO audit to confirm no duplicate meta tags

**Files Modified:**
- `includes/frontend.php` - Removed duplicate meta tags function
- `templates/single-teacher_document.php` - Removed duplicate schema markup
- `teacherske-file-manager.php` - Version bump to 6.0.0

---

## [5.1.0] - Previous Release

Initial stable release with full features.
