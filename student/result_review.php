<?php
/**
 * student/result_review.php
 * Detailed quiz review page
 * - Shows each question
 * - Shows the student's selected answer
 * - Shows the correct answer
 * - Highlights correct answers in green and wrong answers in red
 */

session_start();

// Check if user is authenticated and is a student
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Get attempt ID from the URL
$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;
if ($attempt_id <= 0) {
    die('Invalid review request.');
}

// Fetch attempt, quiz, and score details for the logged-in student
$attempt_stmt = $mysqli->prepare(
    'SELECT a.id, a.score, a.quiz_id, q.title AS quiz_title
     FROM attempts a
     JOIN quizzes q ON a.quiz_id = q.id
     WHERE a.id = ? AND a.user_id = ?
     LIMIT 1'
);
$attempt_stmt->bind_param('ii', $attempt_id, $_SESSION['user_id']);
$attempt_stmt->execute();
$attempt = $attempt_stmt->get_result()->fetch_assoc();

if (!$attempt) {
    die('Review not found.');
}

// Fetch total number of questions for summary display
$count_stmt = $mysqli->prepare('SELECT COUNT(*) AS total_questions FROM questions WHERE quiz_id = ?');
$count_stmt->bind_param('i', $attempt['quiz_id']);
$count_stmt->execute();
$total_questions = (int)($count_stmt->get_result()->fetch_assoc()['total_questions'] ?? 0);
$score = (int)$attempt['score'];
$percentage = $total_questions > 0 ? round(($score / $total_questions) * 100, 2) : 0;

// Fetch question review data using answers and options tables.
// Questions are the base list so unanswered questions still appear in the review.
$review_stmt = $mysqli->prepare(
  'SELECT 
    q.question_text,
    sel.option_text AS selected_answer,
    correct.option_text AS correct_answer,
    CASE 
      WHEN sel.id IS NOT NULL AND sel.id = correct.id THEN 1 
      ELSE 0 
    END AS is_correct
   FROM questions q
   LEFT JOIN answers ans ON ans.question_id = q.id AND ans.attempt_id = ?
   LEFT JOIN options sel ON ans.selected_option_id = sel.id
   LEFT JOIN options correct ON correct.question_id = q.id AND correct.is_correct = 1
   WHERE q.quiz_id = ?
   ORDER BY q.id ASC'
);
$review_stmt->bind_param('ii', $attempt_id, $attempt['quiz_id']);
$review_stmt->execute();
$review_result = $review_stmt->get_result();

$review_items = [];
while ($row = $review_result->fetch_assoc()) {
    $review_items[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quiz Review</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{max-width:900px;margin:30px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
    .top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #eee}
    .title{margin:0;color:#222}
    .meta{margin:6px 0 0;color:#666}
    .score{font-size:40px;font-weight:bold;color:#007bff;margin:0}
    .btn{display:inline-block;padding:10px 14px;border-radius:6px;text-decoration:none;color:#fff;background:#6c757d}
    .summary{display:flex;gap:18px;flex-wrap:wrap;margin:0 0 22px 0}
    .summary-card{flex:1 1 180px;background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:14px}
    .summary-card strong{display:block;color:#333;margin-bottom:6px}
    .review-list{display:flex;flex-direction:column;gap:16px}
    .question-card{border:1px solid #e5e7eb;border-radius:10px;padding:16px;background:#fff}
    .question-card.correct{border-left:5px solid #16a34a;background:#f0fdf4}
    .question-card.wrong{border-left:5px solid #dc2626;background:#fef2f2}
    .question-head{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px}
    .question-number{font-weight:bold;color:#111827}
    .status{font-size:13px;font-weight:bold;padding:5px 10px;border-radius:999px}
    .status.correct{background:#dcfce7;color:#166534}
    .status.wrong{background:#fee2e2;color:#991b1b}
    .question-text{margin:0 0 12px 0;color:#222;line-height:1.6}
    .answer-row{margin-top:8px;padding:10px 12px;border-radius:8px;background:#f9fafb;border:1px solid #e5e7eb}
    .answer-label{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;display:block;margin-bottom:4px}
    .answer-text{color:#111827;line-height:1.5}
    .empty{padding:18px;background:#f9fafb;border-radius:8px;color:#666;text-align:center}
  </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="wrap">
    <div class="top">
      <div>
        <h1 class="title"><?=htmlspecialchars($attempt['quiz_title'])?> Review</h1>
        <p class="meta">Detailed answer review for your submitted quiz.</p>
      </div>
      <a class="btn" href="quizzes.php">Back to Quizzes</a>
    </div>

    <!-- Summary section for overall performance -->
    <div class="summary">
      <div class="summary-card">
        <strong>Score</strong>
        <div class="score"><?=$score?></div>
      </div>
      <div class="summary-card">
        <strong>Total Questions</strong>
        <div class="score" style="font-size:32px"><?=$total_questions?></div>
      </div>
      <div class="summary-card">
        <strong>Percentage</strong>
        <div class="score" style="font-size:32px"><?=$percentage?>%</div>
      </div>
    </div>

    <!-- Detailed per-question review -->
    <?php if (!empty($review_items)): ?>
      <div class="review-list">
        <?php foreach ($review_items as $index => $item): ?>
          <div class="question-card <?=((int)$item['is_correct'] === 1) ? 'correct' : 'wrong'?>">
            <div class="question-head">
              <div class="question-number">Question <?=($index + 1)?></div>
              <div class="status <?=((int)$item['is_correct'] === 1) ? 'correct' : 'wrong'?>">
                <?=((int)$item['is_correct'] === 1) ? 'Correct' : 'Wrong'?>
              </div>
            </div>

            <p class="question-text"><?=htmlspecialchars($item['question_text'])?></p>

            <div class="answer-row">
              <span class="answer-label">Student Selected Answer</span>
              <div class="answer-text"><?=htmlspecialchars($item['selected_answer'] ?? 'No answer selected')?></div>
            </div>

            <div class="answer-row">
              <span class="answer-label">Correct Answer</span>
              <div class="answer-text"><?=htmlspecialchars($item['correct_answer'] ?? 'No correct answer found')?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty">No review data found for this attempt.</div>
    <?php endif; ?>
  </div>
</body>
</html>