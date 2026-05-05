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
    .wrap{max-width:1100px;margin:30px auto;background:#fff;padding:22px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.06)}
    .top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:14px}
    table{width:100%;border-collapse:separate;border-spacing:0}
    thead th{background:#f8fafc;color:#334155;font-weight:700;border-bottom:1px solid #e5e7eb}
    th,td{padding:14px 12px;border-bottom:1px solid #eef2f7;text-align:left;vertical-align:middle}
    tbody tr:hover{background:#fcfcfd}
    .btn{padding:6px 12px;background:#007bff;color:#fff;text-decoration:none;border-radius:999px;display:inline-flex;align-items:center;gap:6px;font-size:14px;line-height:1;transition:transform .15s ease,opacity .15s ease}
    .btn:hover{transform:translateY(-1px);opacity:.95}
    .btn-warning{background:#f59e0b;color:#111827}
    .btn-danger{background:#ef4444}
    .btn-info{background:#0ea5e9}
    .btn-success{background:#10b981}
    .btn-ghost{background:#475569}
    .actions{display:flex;flex-wrap:wrap;gap:8px}
    .icon{font-size:13px;line-height:1}
    .top .btn{padding:10px 14px}
    .empty{padding:18px 12px;text-align:center;color:#64748b}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <div class="top">
      <h2>My Quizzes</h2>
      <div>
        <a class="btn" href="create_quiz.php">Create New Quiz</a>
      </div>
    </div>
    <table>
      <thead>
        <tr><th>Title</th><th>Duration (min)</th><th>Created</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?=htmlspecialchars($row['title'])?></td>
            <td><?=htmlspecialchars($row['duration'])?></td>
            <td><?=htmlspecialchars($row['created_at'])?></td>
            <td>
              <div class="actions">
                <a class="btn btn-info" href="manage_questions.php?quiz_id=<?=$row['id']?>"><span class="icon">👁</span>View Questions</a>
                <a class="btn btn-warning" href="edit_quiz.php?id=<?=$row['id']?>"><span class="icon">✎</span>Edit</a>
                <a class="btn btn-danger" href="delete_quiz.php?id=<?=$row['id']?>" onclick="return confirm('Are you sure you want to delete this quiz? This cannot be undone.');"><span class="icon">🗑</span>Delete</a>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td class="empty" colspan="4">No quizzes found. Create your first quiz to start adding questions.</td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
