# AI Resume Reviewer — Smart Career Assistant

**Last updated: July 22, 2025**

## 🚀 Project Overview

AI Resume Reviewer is a modern, AI-powered web application designed to **help job seekers optimize their resumes** for specific job descriptions. Leveraging the cutting-edge **Google Gemini AI**, the tool analyzes resumes to:

- Extract key personal and professional details.
- Compare qualifications from the resume with those required by a job description.
- Show a clear, side-by-side table of matched and missing qualifications.
- Estimate the likelihood of getting an interview with a prominent score.
- Provide actionable suggestions to enhance the resume for maximum impact.
- Generate tailored mock interview questions to practice.
- Draft personalized cover letters.
- Allow downloading of detailed feedback as PDF reports.

## 🧩 Features

- **Upload PDF, DOCX, or TXT resumes.**
- **Paste any job description for targeted analysis.**
- Intuitive display of **owner details** (name, email, phone, summary).
- **Clear tables showing qualifications present and missing.**
- **Prominent interview probability score** for real-world readiness.
- **AI-generated suggestions** to improve resume-job fit.
- **Mock Interview module** for question practice.
- **Cover Letter generator** for personalized applications.
- PDF download capability for sharing feedback.

## 🎯 Why Use This?

Standing out in today’s job market requires more than just a good resume — it demands **strategic tailoring** and **smart preparation**. This project empowers you to:

- Understand gaps between your resume and desired jobs.
- Target missing skills or keywords instantly.
- Develop confidence via simulated interviews.
- Save time creating effective cover letters.
- Share professional PDF feedback with recruiters or mentors.

## ⚙️ Tech Stack

- Backend: PHP 8+
- AI Integration: Google Gemini API (Generative Language API)
- Resume Parsing: `phpoffice/phpword`, `smalot/pdfparser`
- PDF Generation: `mpdf/mpdf`
- Version Control: Git & GitHub
- UI/UX: Minimalist and functional (designed for future responsive upgrades)

## 📂 Repository Structure

| File          | Role                                   |
| ------------- | ------------------------------------ |
| `index.php`   | User upload form (minimal, clean UI) |
| `upload.php`  | Core processing, AI interactions      |
| `export.php`  | Feedback PDF export                    |
| `/uploads/`   | Temporary file storage                 |
| `/vendor/`    | Composer dependencies                  |

## ⏳ Roadmap

- **Phase 1 (Current):**  
  Full AI-powered resume analysis with detailed feedback and export.

- **Phase 2:**  
  Sophisticated, minimalist black & white UI/UX redesign for enhanced user experience and accessibility.

- **Phase 3 & Beyond:**  
  User accounts, history tracking, multi-language support, integration with job boards, and mobile app companion.

## 💡 How to Use

1. Clone the repository.
2. Run `composer install` to pull dependencies.
3. Add your Google Gemini API key to `upload.php`.
4. Launch on a local or web server.
5. Upload your resume and paste the job description.
6. Review the AI-powered analysis and take next steps with mock interviews & cover letters.

## 🤝 Contribution

Contributions and suggestions are welcome! Feel free to fork, raise issues, or submit pull requests, especially for UI/UX improvements and AI prompt tuning.

## 📜 License

MIT License — free to use, customize, and share.

**Ready to advance your career with AI? Start tailoring smarter, practicing better, and applying with confidence today!**
