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
    
    $hours = (int)($_POST['duration_hours'] ?? 0);
    $minutes = (int)($_POST['duration_minutes'] ?? 0);
    
    // Calculate total duration in minutes
    $duration = ($hours * 60) + $minutes;

    if ($title === '') $errors[] = 'Title is required.';
    if ($duration <= 0) $errors[] = 'Duration must be greater than 0 minutes.';

    if (empty($errors)) {
        $stmt = $mysqli->prepare('INSERT INTO quizzes (title, created_by, duration) VALUES (?, ?, ?)');
        $stmt->bind_param('sii', $title, $_SESSION['user_id'], $duration);
        if ($stmt->execute()) {
            $new_quiz_id = $stmt->insert_id;
            header('Location: manage_questions.php?quiz_id=' . $new_quiz_id);
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
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <h2>Create Quiz</h2>
    <?php if (!empty($errors)): ?>
      <div class="errors"><?=htmlspecialchars(implode("<br>", $errors))?></div>
    <?php endif; ?>
    <form method="post" action="">
      <!-- Quiz Title -->
      <label style="font-weight:bold;display:block;margin-top:10px;">Quiz Title</label>
      <input type="text" name="title" placeholder="Quiz title" value="<?=htmlspecialchars($_POST['title'] ?? '')?>" required>
      
      <!-- Duration Picker -->
      <label style="font-weight:bold;display:block;margin-top:15px;margin-bottom:6px;">Duration Time</label>
      <div style="display:flex;gap:10px;align-items:center;">
        <input type="number" name="duration_hours" placeholder="0" min="0" value="<?=htmlspecialchars($_POST['duration_hours'] ?? '0')?>" style="width:80px;"> 
        <span>Hours</span>
        <input type="number" name="duration_minutes" placeholder="30" min="0" max="59" value="<?=htmlspecialchars($_POST['duration_minutes'] ?? '30')?>" style="width:80px;"> 
        <span>Minutes</span>
      </div>

      <button style="margin-top:20px;" type="submit">Create Quiz</button>
    </form>
    <p style="margin-top:10px"><a href="quizzes.php">Back to My Quizzes</a></p>
  </div>
</body>
</html>
