# CSV Bulk Import Format Guide

## Overview
This guide explains the exact formats required for each field when importing documents via CSV bulk import in TeachersKE File Manager.

---

## CSV Structure

### Required Columns Header
```csv
title,description,file,level,grade,version,subject,featured_image,author
```

### Field Order
The columns can be in any order - the import wizard will let you map them. However, the template uses the order shown above.

---

## Field Specifications

### 1. **title** (REQUIRED)
- **Type:** Text
- **Description:** Document title/name
- **Format:** Any text string
- **Examples:**
  ```
  Grade 7 Mathematics - Algebra Notes
  PP2 Literacy Activities
  Senior Secondary Chemistry Syllabus
  ```

### 2. **file** (REQUIRED)
- **Type:** URL
- **Description:** Full URL to the downloadable file
- **Format:** Valid URL starting with `http://` or `https://`
- **Accepted:** PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP, etc.
- **Examples:**
  ```
  https://example.com/files/math-grade7.pdf
  https://cdn.teacherske.com/documents/chemistry-notes.docx
  https://storage.googleapis.com/bucket/science.zip
  ```
- **Invalid:**
  ```
  /files/document.pdf                    ❌ (not full URL)
  www.example.com/file.pdf               ❌ (missing http/https)
  example.com/file.pdf                   ❌ (missing http/https)
  ```

### 3. **level** (REQUIRED)
- **Type:** Predefined Key
- **Description:** Education level according to CBC system
- **Format:** EXACT KEY (case-sensitive, lowercase with underscores)
- **Allowed Values:**

| Level Key | Label | Valid Grades |
|-----------|-------|--------------|
| `early_years` | Early Years / Pre-Primary | Playgroup, PP1, PP2 |
| `lower_primary` | Lower Primary | Grade 1, Grade 2, Grade 3 |
| `upper_primary` | Upper Primary | Grade 4, Grade 5, Grade 6 |
| `junior_secondary` | Junior Secondary | Grade 7, Grade 8, Grade 9 |
| `senior_secondary` | Senior Secondary | Grade 10, Grade 11, Grade 12 |

- **Examples:**
  ```
  junior_secondary          ✅ Correct
  upper_primary             ✅ Correct
  early_years               ✅ Correct
  ```
- **Invalid:**
  ```
  Junior Secondary          ❌ (must be lowercase with underscores)
  junior-secondary          ❌ (must use underscores, not hyphens)
  JuniorSecondary           ❌ (no spaces/underscores)
  primary                   ❌ (not a valid key)
  ```

### 4. **grade** (REQUIRED)
- **Type:** Grade Level
- **Description:** Specific grade within the level
- **Format:** Flexible - auto-normalized to standard format
- **Auto-Normalization:** The system automatically converts various formats:

#### Accepted Input Formats (Auto-Normalized):

**For numbered grades (1-12):**
- `grade 7` → `Grade 7` ✅
- `grade7` → `Grade 7` ✅
- `Grade 7` → `Grade 7` ✅ (already correct)
- `GRADE 7` → `Grade 7` ✅
- `7` → `Grade 7` ✅

**For early years:**
- `pp1` → `PP1` ✅
- `PP1` → `PP1` ✅ (already correct)
- `Pp1` → `PP1` ✅
- `playgroup` → `Playgroup` ✅
- `Playgroup` → `Playgroup` ✅ (already correct)

#### Valid Grade Values by Level:

**early_years:**
```
Playgroup
PP1
PP2
```

**lower_primary:**
```
Grade 1
Grade 2
Grade 3
```

**upper_primary:**
```
Grade 4
Grade 5
Grade 6
```

**junior_secondary:**
```
Grade 7
Grade 8
Grade 9
```

**senior_secondary:**
```
Grade 10
Grade 11
Grade 12
```

#### Important Grade Rules:
1. Grade MUST match the level you specified
2. You cannot use `Grade 7` with `lower_primary` level
3. You cannot use `PP1` with `junior_secondary` level
4. Case and spacing are flexible - system normalizes them

#### Examples:

**✅ CORRECT Combinations:**
```csv
level,grade
junior_secondary,"grade 7"
junior_secondary,8
upper_primary,"Grade 4"
early_years,pp1
lower_primary,"grade 2"
```

**❌ INCORRECT Combinations:**
```csv
level,grade,error
junior_secondary,"Grade 4"    # Grade 4 not in junior_secondary
upper_primary,"Grade 7"       # Grade 7 not in upper_primary
early_years,"Grade 1"         # Grade 1 not in early_years
lower_primary,"PP1"           # PP1 not in lower_primary
```

### 5. **description** (OPTIONAL)
- **Type:** Text
- **Description:** Brief description of the document
- **Format:** Any text (HTML allowed but will be sanitized)
- **Examples:**
  ```
  Comprehensive notes covering all algebra topics
  Fun literacy activities for pre-primary learners
  Complete syllabus with learning outcomes
  ```
- **Can be empty:** Yes

### 6. **version** (OPTIONAL)
- **Type:** Text
- **Description:** Document version or edition year
- **Format:** Free text, but typically year edition
- **Common Formats:**
  ```
  2026 Edition
  2027 Edition
  2025 Edition
  Version 2.0
  Updated 2026
  ```
- **Can be empty:** Yes

### 7. **subject** (OPTIONAL)
- **Type:** Text
- **Description:** Subject name
- **Format:** Free text (stored as meta field, not taxonomy)
- **Examples:**
  ```
  Mathematics
  Science and Technology
  English Language
  Kiswahili
  Social Studies
  Chemistry
  Physics
  Biology
  ```
- **Note:** Subject does not need to pre-exist - any value is accepted
- **Can be empty:** Yes

### 8. **featured_image** (OPTIONAL)
- **Type:** Image URL
- **Description:** URL to featured/thumbnail image
- **Format:** Valid URL to image file
- **Accepted Types:** JPG, JPEG, PNG, GIF, WEBP
- **Behavior:** Image will be downloaded and added to WordPress media library
- **Examples:**
  ```
  https://example.com/images/cover.jpg
  https://cdn.site.com/thumbnails/math-cover.png
  https://storage.googleapis.com/bucket/images/science.webp
  ```
- **Can be empty:** Yes
- **Invalid URL:** Will be skipped silently (no error, just no image set)

### 9. **author** (OPTIONAL)
- **Type:** WordPress User
- **Description:** Document author
- **Format:** WordPress username OR email address
- **Lookup:** System searches by username first, then by email
- **Examples:**
  ```
  admin
  john.doe
  teacher@teacherske.com
  ```
- **Behavior:**
  - If found: Document assigned to that user
  - If not found: Document assigned to current user (uploader)
- **Can be empty:** Yes (defaults to current user)

---

## Complete CSV Examples

### Example 1: Minimum Required Fields
```csv
title,file,level,grade
Grade 7 Mathematics Notes,https://example.com/math7.pdf,junior_secondary,7
PP1 Literacy Activities,https://example.com/pp1-lit.pdf,early_years,pp1
Grade 4 Science Workbook,https://example.com/sci4.docx,upper_primary,"grade 4"
```

### Example 2: All Fields Populated
```csv
title,description,file,level,grade,version,subject,featured_image,author
Grade 7 Mathematics - Algebra,"Comprehensive notes covering all algebra topics for CBC Grade 7",https://example.com/files/math-grade7.pdf,junior_secondary,"grade 7","2026 Edition",Mathematics,https://example.com/images/math-cover.jpg,admin
PP2 Literacy Activities,"Fun activities to develop early literacy skills",https://example.com/files/pp2-literacy.pdf,early_years,PP2,"2026 Edition","English Language",https://example.com/images/literacy.png,teacher1
Grade 10 Chemistry Syllabus,"Complete chemistry syllabus with learning outcomes",https://example.com/chem-syllabus.pdf,senior_secondary,"Grade 10","2027 Edition",Chemistry,https://example.com/chem.jpg,admin
```

### Example 3: Mixed Optional Fields
```csv
title,description,file,level,grade,version,subject,featured_image,author
Grade 5 Math Quiz,,https://example.com/quiz5.pdf,upper_primary,5,,,https://example.com/thumb.jpg,
Science Notes,Detailed notes,https://example.com/sci.pdf,junior_secondary,"grade 8","2026 Edition",Science,,john.doe
PP1 Worksheet,,https://example.com/ws.pdf,early_years,pp1,,,,
```
*Note: Empty fields are valid for optional columns*

---

## CSV Format Requirements

### File Format
- **Encoding:** UTF-8 (recommended for international characters)
- **Extension:** `.csv`
- **Delimiter:** Comma (`,`)
- **Text Qualifier:** Double quotes (`"`) for fields containing commas
- **Maximum Size:** 10MB

### Header Row
- **Required:** Yes - first row must be column headers
- **Case Sensitive:** No - headers are case-insensitive
- **Column Order:** Flexible - mapping wizard handles any order

### Text with Commas
When a field contains commas, wrap it in double quotes:
```csv
title,description
"Mathematics - Algebra, Geometry, Calculus","Notes covering algebra, geometry, and calculus"
```

### Text with Quotes
To include quotes in text, double them:
```csv
title,description
"The ""Complete"" Guide","This is the ""ultimate"" resource"
```
*Displays as: The "Complete" Guide*

### Empty Fields
- **Optional fields:** Can be completely empty
- **Required fields:** Cannot be empty (import will fail for that row)

Examples:
```csv
title,description,file,level,grade,subject
Doc 1,,https://example.com/file.pdf,upper_primary,4,Math
Doc 2,Has description,https://example.com/file2.pdf,junior_secondary,7,
```

---

## Import Behavior

### Post Status
- **All imports:** Created as **Draft** status
- **Reason:** Allows you to review before publishing
- **Next Step:** Go to Documents → All Documents to review and publish

### Auto-Detection
System automatically detects:
- **File Extension:** PDF, DOC, DOCX, etc.
- **File Size:** Fetched from URL headers (if available)

### Download Tracking
- **View Count:** Initialized to 0
- **Download Count:** Initialized to 0

### Error Handling
- **Invalid Row:** Skipped with error message
- **Valid Rows:** Imported successfully
- **Summary:** Shows total imported and list of errors

---

## Common Errors and Solutions

### Error: "Invalid level: Junior Secondary"
**Problem:** Used human-readable label instead of key
**Solution:** Use `junior_secondary` (lowercase with underscores)

### Error: "Grade 'Grade 7' not valid for level 'upper_primary'"
**Problem:** Grade doesn't match the level
**Solution:** Check level-grade combinations table above

### Error: "Invalid file URL"
**Problem:** File URL is not a valid URL
**Solution:** Ensure URL starts with `http://` or `https://`

### Error: "Missing required field: title"
**Problem:** Title field is empty
**Solution:** Provide a title for every row

### Featured Image Not Showing
**Problem:** Image URL is invalid or inaccessible
**Solution:**
- Check URL is publicly accessible
- Verify image file type (JPG, PNG, GIF, WEBP)
- Test URL in browser first

### Grade Not Normalizing
**Problem:** Grade format not recognized
**Solution:** Use formats: `7`, `grade 7`, `Grade 7`, `pp1`, `PP1`, `Playgroup`

---

## Download Sample Template

1. Go to **Documents → Import CSV**
2. Click **Download Template** button
3. Template includes sample row with all fields populated
4. Use as reference for your own CSV file

---

## Best Practices

1. **Test with small batch first** - Import 5-10 rows to verify format
2. **Use template** - Download and modify the official template
3. **Review in spreadsheet software** - Excel, Google Sheets, LibreOffice
4. **Save as UTF-8 CSV** - Preserves special characters
5. **Verify URLs work** - Test file and image URLs in browser
6. **Use consistent grade format** - Stick to one format like "Grade 7"
7. **Check level-grade match** - Verify grades are valid for chosen level
8. **Review drafts** - Check imported documents before publishing
9. **Keep backup** - Save original CSV before importing

---

## Quick Reference Table

| Field | Required | Format | Example |
|-------|----------|--------|---------|
| title | ✅ Yes | Text | Grade 7 Math Notes |
| file | ✅ Yes | URL | https://example.com/file.pdf |
| level | ✅ Yes | Key | junior_secondary |
| grade | ✅ Yes | Auto-normalized | 7, grade 7, Grade 7 |
| description | ❌ No | Text | Comprehensive notes... |
| version | ❌ No | Text | 2026 Edition |
| subject | ❌ No | Text | Mathematics |
| featured_image | ❌ No | Image URL | https://example.com/img.jpg |
| author | ❌ No | Username/Email | admin, user@email.com |

---

## Valid Level Keys Reference

```
early_years         → Playgroup, PP1, PP2
lower_primary       → Grade 1, Grade 2, Grade 3
upper_primary       → Grade 4, Grade 5, Grade 6
junior_secondary    → Grade 7, Grade 8, Grade 9
senior_secondary    → Grade 10, Grade 11, Grade 12
```

---

**Version:** 6.0.0
**Last Updated:** 2025-11-25
