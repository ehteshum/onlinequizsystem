<?php
// student/submit_quiz.php
// Save answers, grade automatically and update the attempt
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$attempt_id = (int)($_SESSION['attempt_id'] ?? 0);
$quiz_id = (int)($_POST['quiz_id'] ?? 0);
if ($attempt_id <= 0 || $quiz_id <= 0) {
    die('Invalid attempt.');
}

$submitted_answers = $_POST['answers'] ?? [];

// Fetch all questions for this quiz
$qstmt = $mysqli->prepare('SELECT id FROM questions WHERE quiz_id = ?');
$qstmt->bind_param('i', $quiz_id);
$qstmt->execute();
$questions = $qstmt->get_result();

$score = 0;

// Save selected answers and calculate score by comparing the chosen option with the correct option
$save_stmt = $mysqli->prepare('INSERT INTO answers (attempt_id, question_id, selected_option_id) VALUES (?, ?, ?)');
$correct_stmt = $mysqli->prepare('SELECT is_correct FROM options WHERE id = ? LIMIT 1');

while ($question = $questions->fetch_assoc()) {
    $question_id = (int)$question['id'];
    $selected_option_id = isset($submitted_answers[$question_id]) ? (int)$submitted_answers[$question_id] : null;

    $save_stmt->bind_param('iii', $attempt_id, $question_id, $selected_option_id);
    if (!$save_stmt->execute()) {
        die('Answer insert failed: ' . mysqli_error($mysqli));
    }

    if ($selected_option_id) {
        $correct_stmt->bind_param('i', $selected_option_id);
        $correct_stmt->execute();
        $result = $correct_stmt->get_result()->fetch_assoc();
        if (!empty($result) && (int)$result['is_correct'] === 1) {
            $score += 1;
        }
    }
}

// Update the attempt with final score and end time
$end_time = date('Y-m-d H:i:s');
$update_stmt = $mysqli->prepare('UPDATE attempts SET score = ?, end_time = ? WHERE id = ?');
$update_stmt->bind_param('isi', $score, $end_time, $attempt_id);
$update_stmt->execute();

// Clear attempt info from session so a new quiz can start cleanly
unset($_SESSION['attempt_id'], $_SESSION['attempt_quiz_id']);

// Redirect to the detailed review page after submission
header('Location: result_review.php?attempt_id=' . $attempt_id);
exit;
