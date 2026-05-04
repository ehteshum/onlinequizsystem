<?php
// student/leaderboard.php
// Show top scores across quizzes
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Join users and attempts to show student names ordered by score descending
$sql = 'SELECT u.name AS student_name, a.score, q.title AS quiz_title
        FROM attempts a
        JOIN users u ON a.user_id = u.id
        JOIN quizzes q ON a.quiz_id = q.id
        ORDER BY a.score DESC, a.end_time DESC';
$result = $mysqli->query($sql);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Leaderboard</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:900px;margin:30px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    .btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;color:#fff;background:#6c757d}
    .top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:14px}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <h2 style="margin:0">Leaderboard</h2>
      <a class="btn" href="dashboard.php">Back to Dashboard</a>
    </div>
    <table>
      <thead>
        <tr>
          <th>Student Name</th>
          <th>Quiz</th>
          <th>Score</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?=htmlspecialchars($row['student_name'])?></td>
            <td><?=htmlspecialchars($row['quiz_title'])?></td>
            <td><?=htmlspecialchars($row['score'])?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="3">No results yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
