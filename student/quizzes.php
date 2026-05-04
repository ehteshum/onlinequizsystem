<?php
// student/quizzes.php
// Show all available quizzes for students
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Fetch all quizzes with teacher names for display
$sql = 'SELECT q.id, q.title, q.duration, u.name AS teacher_name, q.created_at
        FROM quizzes q
        LEFT JOIN users u ON q.created_by = u.id
        ORDER BY q.created_at DESC';
$result = $mysqli->query($sql);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Available Quizzes</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:1000px;margin:30px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
    .btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;color:#fff;background:#007bff}
    .top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:14px}
    .gray{background:#6c757d}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <h2 style="margin:0">Available Quizzes</h2>
      <div>
        <a class="btn gray" href="dashboard.php">Back to Dashboard</a>
        <a class="btn gray" href="../auth/logout.php">Logout</a>
      </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Teacher</th>
          <th>Duration</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?=htmlspecialchars($row['title'])?></td>
            <td><?=htmlspecialchars($row['teacher_name'] ?? 'Unknown')?></td>
            <td><?=htmlspecialchars($row['duration'])?> min</td>
            <td><a class="btn" href="start_quiz.php?quiz_id=<?=$row['id']?>">Start Quiz</a></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4">No quizzes available yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
