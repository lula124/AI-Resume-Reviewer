<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content']) && isset($_POST['format'])) {
    $content = $_POST['content'];
    $format = $_POST['format'];

    if ($format === 'pdf') {
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML('<h2>Resume Reviewer AI Feedback</h2><pre style="font-family: Arial, sans-serif;">' . htmlspecialchars($content) . '</pre>');
        $mpdf->Output('AI_Feedback.pdf', 'D');
        exit;
    } else {
        echo "Invalid format.";
    }
} else {
    echo "Invalid request.";
}
?>
