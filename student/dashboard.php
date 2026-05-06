<?php
// student/dashboard.php
// Simple student dashboard with session and role check
session_start();
// Redirect to login if not authenticated
if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: ../auth/login.php');
    exit;
}
// Ensure the user is a student
if ($_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Dashboard</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:900px;margin:40px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    .top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
    .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
    .btn{display:inline-block;padding:10px 14px;border-radius:6px;text-decoration:none;color:#fff;background:#007bff}
    .btn.gray{background:#6c757d}
    .panel{margin-top:20px;padding:16px;background:#f8f9fa;border-radius:6px}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <div class="top">
      <div>
        <h1 style="margin:0">Welcome Student</h1>
        <p style="margin:6px 0 0">Choose a quiz and start your attempt.</p>
      </div>
    </div>

    <div class="actions">
      <a class="btn" href="quizzes.php">View Available Quizzes</a>
      <a class="btn" href="leaderboard.php">View Leaderboard</a>
    </div>

    <div class="panel">
      <strong>How it works:</strong>
      <ol>
        <li>Open a quiz</li>
        <li>Answer all questions before the timer ends</li>
        <li>Submit and see your result</li>
      </ol>
    </div>
  </div>
</body>
</html>

