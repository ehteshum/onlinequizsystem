<?php
// teacher/edit_quiz.php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    die('Invalid quiz id.');
}

$teacher_id = $_SESSION['user_id'];
// Verify ownership
$stmt = $mysqli->prepare('SELECT id, title, duration FROM quizzes WHERE id = ? AND created_by = ? LIMIT 1');
$stmt->bind_param('ii', $quiz_id, $teacher_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
if (!$quiz) {
    die('Quiz not found or access denied.');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    
    $hours = (int)($_POST['duration_hours'] ?? 0);
    $minutes = (int)($_POST['duration_minutes'] ?? 0);
    
    $duration = ($hours * 60) + $minutes;
    
    if ($title === '') $errors[] = 'Title is required.';
    if ($duration <= 0) $errors[] = 'Duration must be greater than 0 minutes.';

    if (empty($errors)) {
        $update = $mysqli->prepare('UPDATE quizzes SET title = ?, duration = ? WHERE id = ? AND created_by = ?');
        $update->bind_param('siii', $title, $duration, $quiz_id, $teacher_id);
        if ($update->execute()) {
            header('Location: quizzes.php');
            exit;
        } else {
            $errors[] = 'Database error while updating quiz.';
        }
    }
// Calculate existing time for default display
$existing_hours = floor($quiz['duration'] / 60);
$existing_minutes = $quiz['duration'] % 60;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Quiz</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5}
    .wrap{width:420px;margin:60px auto;background:#fff;padding:20px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.1)}
    input{width:100%;padding:8px;margin:6px 0;border:1px solid #ccc;border-radius:4px;box-sizing:border-box}
    button{width:100%;padding:10px;background:#007bff;color:#fff;border:0;border-radius:4px;cursor:pointer}
    .errors{color:#b00020;margin-bottom:10px;}
    .back{display:block;margin-top:15px;text-align:center;text-decoration:none;color:#007bff}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <h2>Edit Quiz</h2>
    <?php if (!empty($errors)): ?>
      <div class="errors"><?=implode("<br>", array_map('htmlspecialchars', $errors))?></div>
    <?php endif; ?>
    <form method="post" action="">
      <label style="font-weight:bold;display:block;margin-top:10px;">Quiz Title</label>
      <input type="text" name="title" value="<?=htmlspecialchars($_POST['title'] ?? $quiz['title'])?>" required>
      
      <label style="font-weight:bold;display:block;margin-top:15px;margin-bottom:6px;">Duration Time</label>
      <div style="display:flex;gap:10px;align-items:center;">
        <input type="number" name="duration_hours" min="0" value="<?=htmlspecialchars($_POST['duration_hours'] ?? $existing_hours)?>" style="width:80px;">
        <span>Hours</span>
        <input type="number" name="duration_minutes" min="0" max="59" value="<?=htmlspecialchars($_POST['duration_minutes'] ?? $existing_minutes)?>" style="width:80px;">
        <span>Minutes</span>
      </div>

      <button style="margin-top:20px;" type="submit">Update Quiz</button>
    </form>
    <a href="quizzes.php" class="back">Back to My Quizzes</a>
  </div>
</body>
</html>