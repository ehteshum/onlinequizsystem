<?php
// teacher/create_quiz.php
session_start();
// Only teachers can access
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    if ($title === '') $errors[] = 'Title is required.';
    if ($duration <= 0) $errors[] = 'Duration must be a positive number.';

    if (empty($errors)) {
        $stmt = $mysqli->prepare('INSERT INTO quizzes (title, created_by, duration) VALUES (?, ?, ?)');
        $stmt->bind_param('sii', $title, $_SESSION['user_id'], $duration);
        if ($stmt->execute()) {
            header('Location: quizzes.php');
            exit;
        } else {
            $errors[] = 'Database error while creating quiz.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Quiz</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5}
    .wrap{width:420px;margin:60px auto;background:#fff;padding:20px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.1)}
    input{width:100%;padding:8px;margin:6px 0;border:1px solid #ccc;border-radius:4px}
    button{width:100%;padding:10px;background:#007bff;color:#fff;border:0;border-radius:4px}
    .errors{color:#b00020}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Create Quiz</h2>
    <?php if (!empty($errors)): ?>
      <div class="errors"><?=htmlspecialchars(implode("<br>", $errors))?></div>
    <?php endif; ?>
    <form method="post" action="">
      <!-- Quiz Title -->
      <input type="text" name="title" placeholder="Quiz title" value="<?=htmlspecialchars($_POST['title'] ?? '')?>" required>
      <!-- Duration in minutes -->
      <input type="number" name="duration" placeholder="Duration (minutes)" value="<?=htmlspecialchars($_POST['duration'] ?? '30')?>" required>
      <button type="submit">Create Quiz</button>
    </form>
    <p style="margin-top:10px"><a href="quizzes.php">Back to My Quizzes</a></p>
  </div>
</body>
</html>
