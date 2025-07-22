<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AI Resume Reviewer</title>
</head>
<body>
    <h2>AI Resume & Job Description Analyzer</h2>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <label for="resume">Upload Resume (PDF, DOCX, TXT):</label><br>
        <input type="file" id="resume" name="resume" accept=".pdf,.docx,.txt" required /><br><br>

        <label for="job_description">Paste Job Description:</label><br>
        <textarea id="job_description" name="job_description" rows="8" cols="70" required placeholder="Paste the complete job description here, including skills, responsibilities, and qualifications."></textarea><br><br>

        <button type="submit" name="analyze" value="1">Analyze</button>
    </form>
</body>
</html>
