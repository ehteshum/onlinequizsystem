<?php
// teacher/delete_quiz.php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id > 0) {
    $teacher_id = $_SESSION['user_id'];
    
    // Check ownership first
    $check = $mysqli->prepare('SELECT id FROM quizzes WHERE id = ? AND created_by = ?');
    $check->bind_param('ii', $quiz_id, $teacher_id);
    $check->execute();
    if ($check->get_result()->fetch_assoc()) {
        // Delete the quiz (cascading deletes for questions/options/attempts if configured in DB, otherwise might need manual delete)
        $del = $mysqli->prepare('DELETE FROM quizzes WHERE id = ?');
        $del->bind_param('i', $quiz_id);
        $del->execute();
    }
}
header('Location: quizzes.php');
exit;
