<?php
// student/submit_quiz.php
// Finalize a quiz attempt after all answers have already been stored.
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$attempt_id = (int)($_SESSION['attempt_id'] ?? 0);
$quiz_id = (int)($_POST['quiz_id'] ?? $_GET['quiz_id'] ?? ($_SESSION['attempt_quiz_id'] ?? 0));
if ($attempt_id <= 0 || $quiz_id <= 0) {
    die('Invalid attempt.');
}

// Confirm the attempt belongs to the logged-in student and quiz.
$attempt_stmt = $mysqli->prepare('SELECT id FROM attempts WHERE id = ? AND user_id = ? AND quiz_id = ? LIMIT 1');
$attempt_stmt->bind_param('iii', $attempt_id, $_SESSION['user_id'], $quiz_id);
$attempt_stmt->execute();
$attempt = $attempt_stmt->get_result()->fetch_assoc();
if (!$attempt) {
    die('Attempt not found.');
}

// Score is derived from the answers already saved in the answers table.
$score_stmt = $mysqli->prepare(
    'SELECT COUNT(*) AS score
     FROM answers a
     JOIN options o ON a.selected_option_id = o.id
     WHERE a.attempt_id = ? AND o.is_correct = 1'
);
$score_stmt->bind_param('i', $attempt_id);
$score_stmt->execute();
$score = (int)($score_stmt->get_result()->fetch_assoc()['score'] ?? 0);

// Update the attempt with the final score and end time.
$end_time = date('Y-m-d H:i:s');
$update_stmt = $mysqli->prepare('UPDATE attempts SET score = ?, end_time = ? WHERE id = ?');
$update_stmt->bind_param('isi', $score, $end_time, $attempt_id);
$update_stmt->execute();

// Clear quiz progress so the next quiz starts from the first question.
unset($_SESSION['attempt_id'], $_SESSION['attempt_quiz_id']);
unset($_SESSION['quiz_progress'][$quiz_id]);
if (empty($_SESSION['quiz_progress'])) {
    unset($_SESSION['quiz_progress']);
}

// Redirect to the detailed review page after submission.
header('Location: result_review.php?attempt_id=' . $attempt_id);
exit;
