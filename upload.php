<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    if (!$resumeText) { echo "<div>Couldn't extract the resume text.</div>"; exit; }
    if (!$jd && $tool !== 'mock' && $tool !== 'cover') { echo "<div>Job description is required.</div>"; exit; }

    if ($tool === 'analyze') {
        // Extract owner's basic info
        $promptSummary = "Extract the resume owner's full name, email, phone, and a brief summary from the following resume text. Output ONLY a JSON object:
{ \"name\": \"\", \"email\": \"\", \"phone\": \"\", \"summary\": \"\" }
Resume:
$resumeText";
        $ownerDetailsJson = callGeminiAPI($promptSummary, $geminiApiKey);
        $ownerDetails = [];
        if (preg_match('/\{.*\}/s', $ownerDetailsJson, $jsonOut)) {
            $ownerDetails = json_decode($jsonOut[0], true);
        }

        // Extract qualifications required by job present and missing in resume
        $qualPrompt = <<<EOD
        You are an expert resume analyzer. Given the job description and resume, your task is to extract the qualifications required by the job and organize them into two lists:  
        
        - **"present_in_resume"**: qualifications, skills, or requirements from the job description that are found in the resume.  
        - **"missing_in_resume"**: qualifications, skills, or requirements from the job description that are NOT found in the resume.  
        
        ⚠️ **Output ONLY a valid JSON object** with these two arrays.  
        ⚠️ Do **NOT** include explanations, formatting, or any text outside the JSON object.  
        
        Job Description:  
        $jd  
        
        Resume:  
        $resumeText
        EOD;
        
        $qualJson = callGeminiAPI($qualPrompt, $geminiApiKey);
        $qualData = [];
        if (preg_match('/\{.*\}/s', $qualJson, $qualOut)) {
            $qualData = json_decode($qualOut[0], true);
        }

        // Get interview chance and suggestions
        $suggestPrompt = <<<EOD
        You are an expert recruiter and career coach. Analyze the following resume and job description with absolute precision. 
        
        ⚠️ Your response **must strictly follow this exact format**:  
        
        Interview Chance: <number>%  
        Suggestions:  
        - <bullet list of the most impactful and actionable suggestions to improve this resume for the job>  
        
        ✅ Do **NOT** include anything outside this format.  
        ✅ Be direct, critical, and prioritize changes that will **maximize the candidate's chances**.  
        
        Resume:  
        $resumeText  
        
        Job Description:  
        $jd
        EOD;
        
        $suggestResult = callGeminiAPI($suggestPrompt, $geminiApiKey);
        preg_match('/Interview Chance:\s*(\d+)%/i', $suggestResult, $probMatch);
        $probability = $probMatch ? intval($probMatch[1]) : null;
        $suggestionOut = trim(str_replace($probMatch[0] ?? '', '', $suggestResult));

        // Display results
        echo "<h2>Resume Analysis</h2>";

        // Display Owner Details
        echo "<h3>Resume Owner Details</h3>";
        echo "<ul>";
        echo "<li><b>Name:</b> " . htmlspecialchars($ownerDetails['name'] ?? 'N/A') . "</li>";
        echo "<li><b>Email:</b> " . htmlspecialchars($ownerDetails['email'] ?? 'N/A') . "</li>";
        echo "<li><b>Phone:</b> " . htmlspecialchars($ownerDetails['phone'] ?? 'N/A') . "</li>";
        echo "<li><b>Summary:</b> " . htmlspecialchars($ownerDetails['summary'] ?? 'N/A') . "</li>";
        echo "</ul>";

        // Show interview chance prominently
        if ($probability !== null) {
            echo "<h3>Interview Probability</h3>";
            echo "<div style='padding: 10px; background-color: #ddd; font-weight: bold; font-size: 2rem; width: fit-content; border-radius: 8px; margin-bottom: 20px;'>$probability%</div>";
        }

        // Display qualifications table
        echo "<h3>Qualifications Comparison Table</h3>";
        echo "<table border='1' cellpadding='8' cellspacing='0' width='100%'>";
        echo "<tr style='background:#efefef'><th>Your Qualifications</th><th>Missing Qualifications</th></tr>";
        $present = !empty($qualData['present_in_resume']) ? $qualData['present_in_resume'] : [];
        $missing = !empty($qualData['missing_in_resume']) ? $qualData['missing_in_resume'] : [];
        $maxRows = max(count($present), count($missing));
        for($i = 0; $i < $maxRows; $i++) {
            echo "<tr>";
            echo "<td>" . ($present[$i] ?? '') . "</td>";
            echo "<td>" . ($missing[$i] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Suggestions
        echo "<h3>Suggestions to Improve Your CV</h3>";
        echo "<div>" . nl2br(htmlspecialchars($suggestionOut)) . "</div>";

        // Buttons for next actions and PDF export
        ?>
        <form action="upload.php" method="POST">
            <input type="hidden" name="resumeText" value="<?php echo htmlspecialchars($resumeText, ENT_QUOTES); ?>">
            <input type="hidden" name="job_description" value="<?php echo htmlspecialchars($jd, ENT_QUOTES); ?>">
            <button type="submit" name="mock" value="1">Start Mock Interview</button>
            <button type="submit" name="cover" value="1">Generate Cover Letter</button>
        </form>
        <form action="export.php" method="POST">
            <input type="hidden" name="content" value="<?php
                $exportText = "Owner Details:\nName: " . ($ownerDetails['name'] ?? 'N/A') . "\nEmail: " . ($ownerDetails['email'] ?? 'N/A') . "\nPhone: " . ($ownerDetails['phone'] ?? 'N/A') . "\nSummary: " . ($ownerDetails['summary'] ?? 'N/A') . "\n\n";
                $exportText .= "Interview Probability: " . ($probability !== null ? $probability . '%' : 'N/A') . "\n\n";
                $exportText .= "Present in Resume:\n" . implode("\n", $present) . "\n\n";
                $exportText .= "Missing from Resume:\n" . implode("\n", $missing) . "\n\n";
                $exportText .= "Suggestions:\n" . $suggestionOut;
                echo htmlspecialchars($exportText, ENT_QUOTES);
            ?>">
            <button type="submit" name="format" value="pdf">Download Feedback as PDF</button>
        </form>
        <?php
        exit;
    }

    // Mock interview questions
    if ($tool === 'mock' && $resumeText && $jd) {
        $prompt = "Generate 7 realistic interview questions based on this resume and job description.";
        $output = callGeminiAPI($prompt . "\nResume:\n$resumeText\nJob Description:\n$jd", $geminiApiKey);
        echo "<h2>Mock Interview Questions</h2><pre>" . htmlspecialchars($output) . "</pre>";
        echo '<a href="index.php">Back to Analysis</a>';
        exit;
    }

    // Cover letter generation
    if ($tool === 'cover' && $resumeText && $jd) {
        $prompt = "Generate a professional, tailored cover letter using this resume and job description.";
        $output = callGeminiAPI($prompt . "\nResume:\n$resumeText\nJob Description:\n$jd", $geminiApiKey);
        echo "<h2>Cover Letter</h2><pre>" . htmlspecialchars($output) . "</pre>";
        echo '<a href="index.php">Back to Analysis</a>';
        exit;
    }
} else {
    // Display upload form if GET or no POST data
    echo '<form method="POST" enctype="multipart/form-data">
        <h2>Upload Resume & Analyze</h2>
        <label>Upload Resume (PDF/DOCX/TXT): <input type="file" name="resume" required></label><br><br>
        <label>Paste Job Description:<br><textarea name="job_description" rows="8" cols="70" required></textarea></label><br><br>
        <button type="submit" name="analyze" value="1">Analyze</button>
    </form>';
}
?>
