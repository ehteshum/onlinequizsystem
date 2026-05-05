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
    /* Simple page styling for a clean, professional dashboard */
    body{font-family:Arial, sans-serif;background:#f4f4f4;margin:0;color:#222}
    .wrap{max-width:880px;margin:40px auto;background:#fff;padding:30px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.08)}
    .top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;padding-bottom:16px;border-bottom:1px solid #e9e9e9}
    .top h1{margin:0;font-size:28px;line-height:1.2}
    .top p{margin:8px 0 0;color:#666;line-height:1.5}
    .actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:22px}
    .btn{display:inline-block;padding:10px 16px;border-radius:6px;text-decoration:none;color:#fff;background:#2563eb;font-weight:600}
    .btn.secondary{background:#2f855a}
    .btn.gray{background:#6b7280}
    .panel{margin-top:22px;padding:18px;background:#fafafa;border:1px solid #eee;border-radius:8px}
    .panel strong{display:block;margin-bottom:8px}
    ul{margin:10px 0 0 20px;padding:0;line-height:1.7}
    .logout-btn{display:inline-block;padding:10px 16px;border-radius:6px;text-decoration:none;color:#fff;background:#6b7280;font-weight:600}
    .logout-btn:hover,.btn:hover{opacity:.95}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <!-- Header section: title on the left and logout action on the right -->
    <div class="top">
      <div>
        <h1>Welcome Teacher</h1>
        <p>Manage quizzes and questions from one organized place.</p>
      </div>
      <a class="logout-btn" href="../auth/logout.php">Logout</a>
    </div>

    <!-- Primary action buttons with consistent spacing and style -->
    <div class="actions">
      <a class="btn secondary" href="create_quiz.php">Create New Quiz</a>
      <a class="btn" href="quizzes.php">View My Quizzes</a>
    </div>

    <!-- Overview panel explaining what the teacher can do -->
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
