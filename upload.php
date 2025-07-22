<?php
require 'vendor/autoload.php';
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;

$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) { mkdir($uploadDir, 0755, true); }

function extractResumeText($filePath, $ext) {
    if ($ext === 'txt') return file_get_contents($filePath);
    if ($ext === 'pdf') {
        $p = new PdfParser();
        return $p->parseFile($filePath)->getText();
    }
    if ($ext === 'docx') {
        $doc = WordIOFactory::load($filePath, 'Word2007');
        $txt = '';
        foreach ($doc->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof Text) {
                    $txt .= $element->getText() . "\n";
                } elseif ($element instanceof TextRun) {
                    foreach ($element->getElements() as $subelement) {
                        if ($subelement instanceof Text) {
                            $txt .= $subelement->getText() . " ";
                        }
                    }
                    $txt .= "\n";
                }
            }
        }
        if (trim($txt) === '') {
            $xml = file_get_contents("zip://".$filePath."#word/document.xml");
            $txt .= strip_tags($xml);
        }
        return $txt;
    }
    return "Unsupported file type.";
}

function callGeminiAPI($prompt, $apiKey, $model='gemini-2.0-flash') {
    $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";
    $data = ['contents' => [[ "parts" => [[ "text" => $prompt ]]]]];
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) return $result['candidates'][0]['content']['parts'][0]['text'];
    if (isset($result['error']['message'])) return "Gemini API Error: " . $result['error']['message'];
    return "No feedback. Raw: <pre>".htmlspecialchars($response)."</pre>";
}

$geminiApiKey = 'AIzaSyCaIzqUC5o07hxhBQjlccq2YHQLxlCoBik';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tool = '';
    if (isset($_POST['analyze'])) $tool = 'analyze';
    if (isset($_POST['mock'])) $tool = 'mock';
    if (isset($_POST['cover'])) $tool = 'cover';

    $jd = trim($_POST['job_description'] ?? '');
    $resumeText = '';
    $ext = '';

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','docx','txt'];
        if (!in_array($ext, $allowed)) die("Error: Only PDF, DOCX, TXT files allowed.");
        $dest = $uploadDir . uniqid('resume_', true) . '.' . $ext;
        if (!move_uploaded_file($_FILES['resume']['tmp_name'], $dest)) die("Error moving uploaded file.");
        $resumeText = extractResumeText($dest, $ext);
    } elseif (!empty($_POST['resumeText'])) {
        $resumeText = $_POST['resumeText'];
    }

    if (!$resumeText) die("Couldn't extract the resume text.");

    if ($tool === 'analyze') {
        $mainPrompt = "
You are an expert resume/job application reviewer. Below is a user's resume, followed by a job description.
- Estimate as a percentage (0-100%) the candidate's likelihood of being called for an interview.
- List the top problems/gaps in the CV with respect to the job description.
- Provide actionable suggestions to improve the CV to better match the job and increase interview chances.
- Structure:
Interview Chance: X%
Main Problems: ...
Suggestions: ...
--- Resume ---
$resumeText
--- Job Description ---
$jd";
        $aiFeedback = callGeminiAPI($mainPrompt, $geminiApiKey);
        echo "<h2>Resume and JD Analysis</h2>";
        echo "<h3>Extracted Resume Text:</h3>
        <pre style='background:#f6f6f6; padding:10px; border-radius:5px; white-space:pre-wrap; max-height:300px; overflow:auto;'>"
        . htmlspecialchars($resumeText) . "</pre>";
        echo "<h3>Job Description:</h3>
        <pre style='background:#f6f6ff; padding:10px; border-radius:5px; white-space:pre-wrap;'>"
        . htmlspecialchars($jd) . "</pre>";
        echo "<h3>Gemini AI Feedback:</h3>
        <div style='background:#eef2f7; padding:15px; border-radius:8px; font-family:Arial;'>"
        . nl2br(htmlspecialchars($aiFeedback)) . "</div>";

        // NEXT ACTION BUTTONS
        ?>
        <form action="upload.php" method="POST">
            <input type="hidden" name="resumeText" value="<?php echo htmlspecialchars($resumeText, ENT_QUOTES); ?>">
            <input type="hidden" name="job_description" value="<?php echo htmlspecialchars($jd, ENT_QUOTES); ?>">
            <button type="submit" name="mock" value="1">Start Mock Interview</button>
            <button type="submit" name="cover" value="1">Generate Cover Letter</button>
        </form>
        <form action="export.php" method="POST">
            <input type="hidden" name="content" value="<?php echo htmlspecialchars($aiFeedback, ENT_QUOTES); ?>">
            <button type="submit" name="format" value="pdf">Download Feedback as PDF</button>
        </form>
        <?php
        exit;
    }

    // Mock Interview Handler
    if ($tool === 'mock' && $resumeText && $jd) {
        $prompt = "Based on the candidate's resume and job description, generate 7 realistic interview questions that target the most relevant skills, gaps, and job requirements for this application.";
        $output = callGeminiAPI($prompt."\nResume:\n$resumeText\nJob Description:\n$jd", $geminiApiKey);
        echo "<h2>Mock Interview Questions</h2>
        <pre style='background:#f8f8ef; padding:10px; border-radius:6px;'>" .
        htmlspecialchars($output)
        . "</pre>";
        echo '<a href="index.php" style="color:blue;">Back to Analysis</a>';
        exit;
    }
    // Cover Letter Handler
    if ($tool === 'cover' && $resumeText && $jd) {
        $prompt = "Using the following resume and job description, draft a professional, tailored cover letter for this role.";
        $output = callGeminiAPI($prompt."\nResume:\n$resumeText\nJob Description:\n$jd", $geminiApiKey);
        echo "<h2>Generated Cover Letter</h2>
        <pre style='background:#f0f6ff; padding:10px; border-radius:6px;'>" .
        htmlspecialchars($output)
        . "</pre>";
        echo '<a href="index.php" style="color:blue;">Back to Analysis</a>';
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>