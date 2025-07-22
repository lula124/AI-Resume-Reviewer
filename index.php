<!DOCTYPE html>
<html>
<head>
    <title>AI Resume Reviewer — Advanced Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        label { font-weight: bold; }
        textarea, input[type="file"] { width: 100%; }
        .tools { margin: 20px 0; }
        button { margin-right: 10px; }
    </style>
    
</head>
<body>
    <h2>AI Resume & Job Description Analyzer</h2>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <label>Upload Resume (PDF, DOCX, TXT):</label><br>
        <input type="file" name="resume" accept=".pdf,.docx,.txt" required><br><br>
        <label>Paste Target Job Description:</label><br>
        <textarea name="job_description" rows="8" required></textarea><br><br>
        <button type="submit" name="analyze" value="1">Analyze</button>
    </form>
    <script>
        // This function allows the user to reuse the last uploaded file/JD for tools if desired.
        function triggerTool(tool) {
            alert('Please run your analysis first, then use the export tools from the results page.');
        }
    </script>
    <p style="color: #666;">
        <small>After uploading, you’ll see options for mock interview, cover letter generation, PDF/Word feedback export, and sharing.</small>
    </p>
</body>
</html>

