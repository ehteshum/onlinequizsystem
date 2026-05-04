<?php
// student/result.php
// Show the final result for a quiz attempt
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;
if ($attempt_id <= 0) {
    die('Invalid result request.');
}

// Fetch attempt details and total number of questions for percentage calculation
$stmt = $mysqli->prepare('SELECT a.score, q.duration, q.title, a.quiz_id FROM attempts a JOIN quizzes q ON a.quiz_id = q.id WHERE a.id = ? AND a.user_id = ? LIMIT 1');
$stmt->bind_param('ii', $attempt_id, $_SESSION['user_id']);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();
if (!$attempt) {
    die('Result not found.');
}

$count_stmt = $mysqli->prepare('SELECT COUNT(*) AS total_questions FROM questions WHERE quiz_id = ?');
$count_stmt->bind_param('i', $attempt['quiz_id']);
$count_stmt->execute();
$total_questions = (int)($count_stmt->get_result()->fetch_assoc()['total_questions'] ?? 0);
$score = (int)$attempt['score'];
$percentage = $total_questions > 0 ? round(($score / $total_questions) * 100, 2) : 0;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quiz Result</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:700px;margin:50px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.08);text-align:center}
    .score{font-size:42px;font-weight:bold;color:#007bff}
    .btn{display:inline-block;margin-top:18px;padding:10px 14px;border-radius:6px;text-decoration:none;color:#fff;background:#6c757d}
  </style>
</head>
<body>
  <div class="wrap">
    <h1><?=htmlspecialchars($attempt['title'])?> Result</h1>
    <p class="score"><?=$score?> / <?=$total_questions?></p>
    <p>Percentage: <strong><?=$percentage?>%</strong></p>
    <a class="btn" href="quizzes.php">Back to Quizzes</a>
  </div>
</body>
</html>
