# Learning Platform with Automation Question Generation and Marking

## Overview
This is a **online learning platform** designed for learners and educators.  
It allows teachers to create, manage, and auto-grade assessments while providing students a platform to learn and complete assignments online.  

**Key features:**
- AI-assisted auto-generation of questions from PDF files.  
- Automatic marking of assessments.  
- User-friendly dashboard for managing courses, chapters, and questions.  

---

## Project Structure / Paths

| Path | Description |
|------|-------------|
| `app/admin/` | Contains **admin-accessible pages for teachers**: dashboards, manage everything inside the platfrom, and allow for view report |
| `app/ai/` | Contains AI scripts for auto-generating questions. |
| `app/controller/` | Contains **backend logic**: handles saving, processing, and updating questions and assessments. This is where server-side operations happen. |
| `public/` | Contains **user-accessible pages for learners**: dashboards, assessment views, and other frontend components that students and teachers interact with directly. |
