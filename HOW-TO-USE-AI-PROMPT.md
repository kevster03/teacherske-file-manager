# 📖 SIMPLE GUIDE: How to Use the AI Prompt (No JSON Knowledge Needed!)

## 🎯 What You'll Do (3 Simple Steps)

1. **Talk to AI** → Give it your keywords
2. **Pick blocks** → Choose 10 from AI's 15 suggestions
3. **Copy-paste** → Put AI's code into your WordPress admin

**That's it!** You don't need to understand JSON. Just copy and paste.

---

## 🚀 STEP-BY-STEP WALKTHROUGH

### **STEP 1: Open the AI Prompt**

1. Go to your GitHub repository
2. Click on `AI-CONTENT-BLOCKS-PROMPT.md`
3. Click the **"Raw"** button (top right)
4. Press `Ctrl+A` (select all) then `Ctrl+C` (copy)

**You've copied the entire prompt!**

---

### **STEP 2: Start a Conversation with AI**

Go to one of these AI tools (all free to try):
- **ChatGPT**: chat.openai.com (recommended)
- **Claude**: claude.ai
- **Gemini**: gemini.google.com

**Paste the prompt** you copied (Ctrl+V) and press Enter.

The AI will respond with something like:
> "I'm ready to help you create SEO content blocks for TeachersKE! Which category should we start with?"

---

### **STEP 3: Choose a Category**

Type one of these:
- `Let's start with Schemes of Work`
- `Let's start with Lesson Plans`
- `Let's start with Exams & Marking Schemes`

---

### **STEP 4: Upload Your Keywords**

**Option A: If you have Excel file**
1. Copy the keywords column from Excel
2. Paste into the AI chat
3. Type: "Here are my keywords"

**Option B: If you have Word/Text file**
1. Copy all keywords
2. Paste into AI chat

**Option C: If you don't have keywords yet**
Just type:
```
I don't have keywords yet. Can you suggest some common ones for [category name]?
```

AI will research and suggest keywords for you!

---

### **STEP 5: AI Suggests 15 Blocks**

AI will analyze your keywords and suggest 15 content block ideas.

**Example Output:**
```
Based on your keywords, here are 15 block ideas:

1. "How to Use Schemes of Work Effectively" (How-To, SEO: 9/10)
2. "FAQs About CBC Schemes" (FAQ, SEO: 10/10)
3. "Weekly Planning Checklist" (Generic, SEO: 7/10)
... (12 more)

Which 10 would you like me to generate?
```

---

### **STEP 6: Pick Your Favorite 10**

Just type the numbers you want:
```
Generate blocks: 1, 2, 3, 5, 7, 9, 10, 12, 14, 15
```

Or be specific:
```
I want all the FAQ and How-To blocks
```

---

### **STEP 7: AI Generates the Code**

AI will create complete blocks for you. It looks like this:

```json
{
  "block_title": "How to Use This Scheme of Work",
  "block_type": "HowTo",
  "schema_type": "HowTo",
  "steps": [
    {
      "position": 1,
      "name": "Review the Scheme",
      "text": "Read through the entire scheme before term starts..."
    },
    {
      "position": 2,
      "name": "Gather Materials",
      "text": "Collect all resources listed in the scheme..."
    }
  ]
}
```

**Don't worry about understanding this code!** Just copy it.

---

### **STEP 8: Copy the Code**

For each block AI generates:
1. Look for the code block (it has a gray background)
2. Click the **"Copy"** button (top-right of code block)
3. **OR** select all the text and press `Ctrl+C`

---

### **STEP 9: Create Block in WordPress**

1. Go to **Documents → 📝 Content Blocks → Add New Block**
2. **Scroll down** to find the **special fields for FAQ or How-To**
3. **HERE'S THE TRICK:** You don't paste the JSON directly!

**For FAQ Blocks:**
1. Check **"Enable structured data"**
2. Select **"FAQ (Questions & Answers)"** from dropdown
3. The FAQ fields will appear automatically
4. Click **"+ Add FAQ Item"** for each question
5. **Look at the AI's JSON** and copy each question/answer into the fields:
   - Question 1 → Paste into "Question 1" field
   - Answer 1 → Paste into "Answer" field
   - Repeat for all Q&A pairs

**For How-To Blocks:**
1. Check **"Enable structured data"**
2. Select **"How-To (Step-by-step guide)"**
3. Click **"+ Add Step"** for each step
4. **Look at the AI's JSON** and copy each step:
   - Step 1 name → Paste into "Step 1 Title"
   - Step 1 text → Paste into "Step Instructions"
   - Repeat for all steps

**Choose background color** with the color picker.

**Click "Save Block"** ✅

---

## 🎨 VISUAL EXAMPLE

### AI Gives You This JSON:
```json
{
  "block_title": "FAQs About Schemes of Work",
  "faqs": [
    {
      "question": "What is a scheme of work?",
      "answer": "A scheme of work is a long-term plan..."
    },
    {
      "question": "How do I adapt it for my class?",
      "answer": "Adjust pacing based on learner needs..."
    }
  ]
}
```

### You Do This in WordPress:

**Block Title:** `FAQs About Schemes of Work`

**Enable Schema:** ✅ Checked

**Schema Type:** `FAQ (Questions & Answers)`

**FAQ Item 1:**
- Question: `What is a scheme of work?`
- Answer: `A scheme of work is a long-term plan...`

**FAQ Item 2:**
- Question: `How do I adapt it for my class?`
- Answer: `Adjust pacing based on learner needs...`

**Background Color:** Click color picker, choose blue

**Save!**

---

## 💡 PRO TIPS

### Tip 1: Do One Category Per Session
Don't try to do all 6 categories at once. Focus:
- Day 1: Schemes of Work (10 blocks)
- Day 2: Lesson Plans (10 blocks)
- etc.

### Tip 2: Test Before Creating All 10
Create 1-2 blocks first, test them on documents, see how they look. Then create the rest.

### Tip 3: Save AI Responses
Copy-paste the AI's entire response into a Word document. That way you have a backup.

### Tip 4: If JSON Confuses You
Just tell the AI:
```
Can you give me the questions and answers in a simple list format instead of JSON?
```

AI will give you:
```
Question 1: What is a scheme of work?
Answer 1: A scheme of work is...

Question 2: How do I adapt it?
Answer 2: Adjust pacing based on...
```

Much easier to copy-paste!

### Tip 5: Ask AI to Explain
If anything is unclear, just ask:
```
Can you explain what I should do with this code?
```

---

## ❓ COMMON QUESTIONS

### Q: What if I don't know what JSON is?
**A:** You don't need to! Just copy-paste from AI's response into WordPress fields. No JSON knowledge required.

### Q: Can I edit AI's content?
**A:** Yes! After creating the block, edit it anytime. Change wording, add more Q&A, etc.

### Q: What if AI gives me 15 blocks but I only like 8?
**A:** That's fine! Pick 8 and ask AI to suggest 2 more later.

### Q: Do I need to pay for AI?
**A:** ChatGPT free tier works fine. You might hit daily limits, but that's enough for creating blocks.

### Q: Can I use the same blocks across multiple documents?
**A:** YES! That's the whole point. One block = unlimited documents.

### Q: What if blocks don't show up on my site?
**A:**
1. Check you've selected the block in document's meta box
2. Check block is set to "Active"
3. Go to Debug tool and verify table columns exist

---

## 🎯 QUICK START CHECKLIST

- [ ] Copy `AI-CONTENT-BLOCKS-PROMPT.md` from GitHub
- [ ] Paste into ChatGPT/Claude
- [ ] Choose category (Schemes of Work)
- [ ] Paste your keywords OR ask AI to suggest keywords
- [ ] Review AI's 15 suggestions
- [ ] Pick your favorite 10
- [ ] For each block AI generates:
  - [ ] Create new block in WordPress
  - [ ] Copy-paste question/answer OR step/instructions
  - [ ] Choose background color
  - [ ] Save
- [ ] Apply blocks to 5 test documents
- [ ] Check how they look on frontend
- [ ] Check RankMath score improvement
- [ ] Repeat for other categories!

---

## 🆘 STUCK? TRY THIS

**Problem:** "AI's response is confusing"
**Solution:** Ask AI: "Can you explain this in simpler terms?"

**Problem:** "I don't see FAQ fields in WordPress"
**Solution:**
1. Update your database: Documents → Debug Blocks → Update Table Now
2. Make sure you selected "FAQ" from schema dropdown

**Problem:** "Blocks aren't showing on frontend"
**Solution:** Edit document → Scroll to meta box → Check the boxes for blocks you want

**Problem:** "AI won't respond"
**Solution:** You might have hit daily limit. Try tomorrow or use a different AI tool.

---

## 🎉 YOU'RE READY!

**Remember:**
- You're just copying and pasting
- AI does the hard work (writing, SEO optimization, CBC alignment)
- You just organize it in WordPress
- No coding or JSON knowledge needed

**Start with Schemes of Work** and create your first 3 blocks today! 🚀
