# AI Resume Reviewer

An AI-powered resume analysis tool designed to help job seekers optimize their resumes against specific job descriptions. Built with PHP and integrated with Google's Gemini API, this project offers:

- Extraction of resume owner's details from uploaded PDFs, DOCX, or TXT files.
- Intelligent matching between resume content and job description qualifications.
- Clear display of qualifications present in the resume and those missing.
- A visible interview probability score.
- Practical suggestions to improve resume alignment.
- Additional features including AI-driven mock interview question generation, personalized cover letter drafting, and downloadable PDF feedback reports.

## Features

- Upload resumes in popular formats (PDF, DOCX, TXT).
- Paste job descriptions to tailor analysis.
- Detailed side-by-side qualification match and gap tables.
- Extract and display basic owner contact details.
- Interactive mock interview and cover letter generation.
- Download feedback as PDF for easy sharing and review.
- Minimalist, user-friendly design planned for upcoming UI/UX enhancements.

## Technology Stack

- PHP 8+
- [Google Gemini API](https://ai.google.dev/gemini-api)
- `smalot/pdfparser` for PDFs
- `phpoffice/phpword` for DOCX parsing
- `mpdf/mpdf` for PDF generation

## Usage

1. Clone the repository.
2. Run `composer install` to install dependencies.
3. Add your Gemini API key in `upload.php`.
4. Use a local PHP server or LAMP/WAMP/XAMPP stack to serve the project.
5. Access `index.php` to upload resumes and job descriptions, then view detailed AI feedback.

## Roadmap

- **Stage 1:** Core functionality with analytical backend and basic UI.
- **Stage 2:** Sophisticated black & white minimalist UI/UX to enhance user experience without distractions.
- Future plans include interactive feedback visualization, user accounts, and extended export/share options.

## Notes

- Feedback downloads are provided only as PDFs in line with project preferences.
- No email notifications or sharing from the system to maintain user privacy and simplicity.
- Tailored primarily for software engineering roles focusing on skills like Java, REST, SQL, Agile/Scrum, PHP, MySQL, and Full Stack development.

## License

MIT License — feel free to use, modify, and contribute!

---

© 2025 AI Resume Reviewer Project  
