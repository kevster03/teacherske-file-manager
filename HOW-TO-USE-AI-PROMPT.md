# 📖 SIMPLE GUIDE: SEO Content Blocks with AI-Generated Templates

## 🎯 What This System Does

**Automatically creates 300-600 unique words per file page** using template variables that fill in with each document's metadata (grade, subject, category, etc.).

**One-time setup** → Works forever across all documents!

---

## ✨ NEW SYSTEM OVERVIEW (v7.0.0)

### What Changed?
❌ **OLD:** Create individual blocks for each category in WordPress admin
✅ **NEW:** Edit universal templates once with {variables} that auto-fill

### Key Benefits:
- **Unique content per page** → No duplicate content penalties
- **Zero maintenance** → Set up once, works for all documents
- **SEO-optimized** → RankMath/Yoast can see the content
- **Rich snippets** → Proper Schema.org markup for FAQs and How-To blocks

---

## 🚀 QUICK START (3 Steps)

### **STEP 1: Access Settings**
1. Go to **Documents → Settings**
2. Click the **"SEO Content Blocks"** tab
3. You'll see all available blocks with templates

### **STEP 2: Edit Templates (Optional)**
The system comes with **smart defaults**, but you can customize:
- Intro block
- About/Overview block
- FAQ block
- How-To Usage block
- Download Instructions
- Related Resources

### **STEP 3: Select Blocks Per Document**
When creating/editing a document:
1. Scroll to **"SEO Content Blocks"** meta box
2. Check the blocks you want (e.g., Intro + FAQ + How-To)
3. Save document
4. **Done!** Content auto-generates with that document's metadata

---

## 🔧 HOW TEMPLATE VARIABLES WORK

### Available Variables:
```
{grade}          → "Grade 1", "Grade 2", "Grade 3", etc.
{subject}        → "Mathematics", "English", "Science", etc.
{category}       → "Schemes of Work", "Lesson Plans", "Exams"
{level}          → "Primary", "Junior Secondary", "Senior Secondary"
{file_type}      → "PDF", "Word Document", "Excel Spreadsheet"
{file_name}      → The actual file name
{post_title}     → Document title
{current_year}   → 2025 (auto-updates)
{term}           → "Term 1", "Term 2", "Term 3"
{curriculum}     → "CBC" (Competency-Based Curriculum)
```

### Example Template:
```
Download free {grade} {subject} {category} for {term} {current_year}.
This {file_type} is designed for {level} learners following the {curriculum} curriculum.
```

### Auto-Generated Result (for specific document):
```
Download free Grade 3 Mathematics Schemes of Work for Term 1 2025.
This PDF is designed for Primary learners following the CBC curriculum.
```

**Each document gets UNIQUE content** based on its own metadata! 🎉

---

## 🤖 HOW TO USE AI TO IMPROVE TEMPLATES

### Prompt Template for ChatGPT/Claude:

```
I manage a Kenyan educational resources website (teacherske.co.ke) with 5000+ teacher documents.

I use a template-variable system where placeholders like {grade}, {subject}, {category}
auto-fill with each document's metadata to create unique SEO content.

Current template for [BLOCK NAME]:
"""
[PASTE CURRENT TEMPLATE HERE]
"""

Available variables:
- {grade} → Grade 1-12
- {subject} → Mathematics, English, Science, etc.
- {category} → Schemes of Work, Lesson Plans, Exams, etc.
- {level} → Primary, Junior Secondary, Senior Secondary
- {term} → Term 1, 2, 3
- {file_type} → PDF, Word, Excel
- {curriculum} → CBC

Please:
1. Improve this template for better SEO and user engagement
2. Make it sound natural and helpful (not keyword-stuffed)
3. Keep it 100-150 words
4. Ensure it works for ANY grade/subject/category combination
5. Include CBC curriculum alignment mentions where relevant
```

### Example Conversation:

**You:**
```
Current Intro template:
Download free {grade} {subject} {category}. This is a {file_type}
for {level} students.

Please improve it.
```

**AI Response:**
```
Here's an improved version:

Access comprehensive {grade} {subject} {category} designed specifically for
{level} learners in Kenya's {curriculum} curriculum. This free {file_type}
provides educators with ready-to-use materials that align with current
curriculum standards for {term} {current_year}. Whether you're planning
lessons, preparing assessments, or organizing term schedules, these resources
support effective teaching and learning. Download instantly and adapt to your
classroom needs—perfect for both experienced teachers and those new to the {curriculum} framework.
```

**You copy this improved template → Paste into WordPress Settings → Save!**

---

## 📋 EDITING SPECIFIC BLOCK TYPES

### 1. Standard Blocks (Intro, About, Downloads)
**Format:** Regular text with {variables}

**Example:**
```
Welcome to our collection of {grade} {subject} resources! This {category}
is perfect for {level} teachers in Kenya following the {curriculum} curriculum.
```

---

### 2. FAQ Blocks (Rich Snippets)
**Format:** Use `||` to separate questions and answers

**Template Structure:**
```
Q: First question here?||A: First answer here||Q: Second question?||A: Second answer||Q: Third question?||A: Third answer
```

**Real Example:**
```
Q: Is this {category} free to download?||A: Yes! All our {grade} {subject} resources are completely free for Kenyan teachers.||Q: Is it aligned with {curriculum}?||A: Absolutely! This {category} follows the latest {curriculum} curriculum standards for {term} {current_year}.||Q: What file format is it?||A: This resource is available as a {file_type} for easy access and printing.
```

**AI Prompt for FAQs:**
```
Create 5 FAQ pairs for a {grade} {subject} {category} download page.
Format: Q: question?||A: answer||Q: question?||A: answer

Focus on:
- Free download confirmation
- CBC curriculum alignment
- File format and usage
- Term/year relevance
- Practical classroom application
```

---

### 3. How-To Blocks (Rich Snippets)
**Format:** Use `STEP_1:`, `STEP_2:`, etc., separated by `||`

**Template Structure:**
```
STEP_1: Title||Description of step 1||STEP_2: Title||Description of step 2||STEP_3: Title||Description of step 3
```

**Real Example:**
```
STEP_1: Download the {file_type}||Click the download button above to save this {grade} {subject} {category} to your device. The file works on any device with a PDF reader.||STEP_2: Review the Content||Open the {file_type} and familiarize yourself with the {curriculum}-aligned content for {term}. Check that it matches your class needs.||STEP_3: Adapt and Use||Customize the materials based on your learners' needs and begin implementation in your {level} classroom.
```

**AI Prompt for How-To:**
```
Create a 4-step guide for teachers using {grade} {subject} {category}.
Format: STEP_1: title||description||STEP_2: title||description

Steps should cover:
- Downloading/accessing the resource
- Reviewing content for CBC alignment
- Customizing for their specific classroom
- Implementation tips
```

---

## 🎨 COMPLETE AI WORKFLOW

### One-Time Setup for Each Block:

1. **Copy current template** from Settings → SEO Content Blocks
2. **Open ChatGPT** (or Claude)
3. **Use this prompt:**
   ```
   Improve this template for a Kenyan teacher resource site.
   Make it SEO-friendly, natural, and helpful.

   Current template:
   [PASTE HERE]

   Variables available: {grade}, {subject}, {category}, {level},
   {term}, {file_type}, {curriculum}

   Keep 100-150 words, ensure it works for any subject/grade combo.
   ```
4. **Copy AI's improved version**
5. **Paste into WordPress Settings**
6. **Save**
7. **Test** on 2-3 documents to see how it looks
8. **Adjust** if needed

**Repeat for all blocks you want to use!**

---

## 💡 BLOCK SELECTION STRATEGY

### Recommended Combinations:

**For Schemes of Work:**
- ✅ Intro Block
- ✅ FAQ Block
- ✅ How-To Usage Block

**For Exams & Marking Schemes:**
- ✅ Intro Block
- ✅ FAQ Block
- ✅ Download Instructions

**For Lesson Plans:**
- ✅ Intro Block
- ✅ About/Overview Block
- ✅ How-To Usage Block

**For Worksheets/Activities:**
- ✅ Intro Block
- ✅ How-To Usage Block

---

## 🧪 TESTING YOUR TEMPLATES

### After editing templates:

1. **Create test document** with metadata:
   - Grade: Grade 5
   - Subject: Mathematics
   - Category: Lesson Plans
   - Term: Term 1

2. **Select 2-3 blocks** in meta box

3. **View document** on frontend

4. **Check:**
   - ✅ All {variables} filled correctly
   - ✅ Text reads naturally
   - ✅ No grammar errors
   - ✅ FAQ/How-To formatting works
   - ✅ Rich snippets validate ([Google Rich Results Test](https://search.google.com/test/rich-results))

5. **Adjust template** if needed

6. **Apply to all documents** once satisfied

---

## 🎯 ADVANCED: CATEGORY-SPECIFIC TEMPLATES

Want different text for Schemes vs. Lesson Plans?

**Unfortunately not built-in**, but you can:
1. Create multiple intro blocks (intro_schemes, intro_lessons)
2. In Settings, add conditions to template
3. Select appropriate block per document type

**OR** use AI to create "universal" templates that work for everything:

```
This {grade} {subject} {category} provides {level} teachers with
comprehensive materials for {term} {current_year}. Whether you're using
this for lesson planning, assessment preparation, or curriculum coverage,
these {curriculum}-aligned resources support effective teaching.
```

---

## ❓ COMMON QUESTIONS

### Q: Do I need to create blocks for every grade/subject combo?
**A:** NO! That's the beauty of this system. One template = works for all combinations.

### Q: Can I edit templates without breaking existing documents?
**A:** YES! Templates regenerate on page load. Edit anytime.

### Q: What if a document doesn't have metadata (e.g., no Term)?
**A:** Variable shows empty. Use generic fallbacks in templates: "for {term} {current_year}" → shows "for 2025"

### Q: Can I use HTML in templates?
**A:** Yes, but keep it simple. `<strong>`, `<em>`, `<br>` work fine.

### Q: How do I know if rich snippets work?
**A:** Use [Google Rich Results Test](https://search.google.com/test/rich-results) with your page URL.

### Q: Can I disable blocks site-wide?
**A:** Yes. Go to Settings → SEO Content Blocks → Uncheck "Enabled" → Save.

---

## 🆘 TROUBLESHOOTING

**Problem:** Variables showing as {grade} instead of "Grade 5"
**Solution:** Document metadata is empty. Edit document → Fill in grade field → Save

**Problem:** Blocks not showing on page
**Solution:** Check meta box → Ensure blocks are selected → Save document

**Problem:** FAQ/How-To formatting broken
**Solution:** Check `||` separators in template. Must be exactly `||` with no spaces.

**Problem:** Content looks keyword-stuffed
**Solution:** Ask AI to make template more natural. Less repetition of {subject}.

---

## 🎉 YOU'RE READY!

**Remember:**
1. ✅ Templates use {variables} that auto-fill per document
2. ✅ Edit templates once → works forever
3. ✅ Use AI to improve wording and SEO
4. ✅ Select blocks per document in meta box
5. ✅ Each page gets unique content automatically

**Start by improving your Intro block template with AI today!** 🚀

---

## 📚 QUICK REFERENCE

### Good Template Example:
```
Access high-quality {grade} {subject} {category} tailored for Kenya's
{curriculum} curriculum. This free {file_type} helps {level} teachers
deliver engaging lessons for {term} {current_year}. Download now and
bring comprehensive, standards-aligned education materials to your classroom.
```

### FAQ Format:
```
Q: Question here?||A: Answer here||Q: Next question?||A: Next answer
```

### How-To Format:
```
STEP_1: Title||Description||STEP_2: Title||Description
```

**Happy teaching! 🇰🇪**
