<?php
// teacher/dashboard.php
// Simple teacher dashboard with session and role check
session_start();
// Redirect to login if not authenticated
if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: ../auth/login.php');
    exit;
}
// Ensure the user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    // Optional: you might show an error or redirect to student area
    header('Location: ../auth/login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Teacher Dashboard</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:900px;margin:40px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    .top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
    .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
    .btn{display:inline-block;padding:10px 14px;border-radius:6px;text-decoration:none;color:#fff;background:#007bff}
    .btn.secondary{background:#28a745}
    .btn.gray{background:#6c757d}
    .panel{margin-top:20px;padding:16px;background:#f8f9fa;border-radius:6px}
    ul{margin:10px 0 0 20px}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <div class="top">
      <div>
        <h1 style="margin:0">Welcome Teacher</h1>
        <p style="margin:6px 0 0">Manage quizzes and questions from here.</p>
      </div>
      <a class="btn gray" href="../auth/logout.php">Logout</a>
    </div>

    <div class="actions">
      <a class="btn secondary" href="create_quiz.php">Create New Quiz</a>
      <a class="btn" href="quizzes.php">View My Quizzes</a>
    </div>

    <div class="panel">
      <strong>What you can do now:</strong>
      <ul>
        <li>Create a quiz</li>
        <li>See all your quizzes</li>
        <li>Add questions to each quiz</li>
      </ul>
    </div>
  </div>
</body>
</html>
