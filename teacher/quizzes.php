<?php
// teacher/quizzes.php
session_start();
// Only teachers
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$teacher_id = $_SESSION['user_id'];
// Fetch quizzes created by this teacher
$stmt = $mysqli->prepare('SELECT id, title, duration, created_at FROM quizzes WHERE created_by = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Quizzes</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:900px;margin:30px auto;background:#fff;padding:20px;border-radius:6px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}
    .btn{padding:6px 10px;background:#007bff;color:#fff;text-decoration:none;border-radius:4px;display:inline-block;font-size:14px;margin-right:4px;}
    .btn-warning{background:#ffc107;color:#000}
    .btn-danger{background:#dc3545}
    .btn-info{background:#17a2b8}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <div class="top">
      <h2>My Quizzes</h2>
      <div>
        <a class="btn" href="create_quiz.php">Create New Quiz</a>
        <a class="btn" href="../auth/logout.php">Logout</a>
      </div>
    </div>
    <table>
      <thead>
        <tr><th>Title</th><th>Duration (min)</th><th>Created</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?=htmlspecialchars($row['title'])?></td>
          <td><?=htmlspecialchars($row['duration'])?></td>
          <td><?=htmlspecialchars($row['created_at'])?></td>
          <td>
            <!-- Action Buttons -->
            <a class="btn btn-info" href="manage_questions.php?quiz_id=<?=$row['id']?>">Manage Questions</a>
            <a class="btn btn-warning" href="edit_quiz.php?id=<?=$row['id']?>">Edit Quiz</a>
            <a class="btn btn-danger" href="delete_quiz.php?id=<?=$row['id']?>" onclick="return confirm('Are you sure you want to delete this completely?');">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
