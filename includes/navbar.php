<?php
// Reusable navbar component.
// Start session only if it is not already started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePath = '/onlinequizsystem';
$isLoggedIn = !empty($_SESSION['user_id']);
$role = $_SESSION['role'] ?? '';

// Select links based on logged-in role.
$dashboardLink = $basePath . '/student/dashboard.php';
$quizzesLink = $basePath . '/student/quizzes.php';
$leaderboardLink = $basePath . '/student/leaderboard.php';

if ($role === 'teacher') {
    $dashboardLink = $basePath . '/teacher/dashboard.php';
  $quizzesLink = $basePath . '/teacher/quizzes.php';
  // Teacher leaderboard page
  $leaderboardLink = $basePath . '/teacher/leaderboard.php';
}
?>

<style>
  /* Simple top navbar with flexbox layout */
  .site-navbar {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    padding: 12px 20px;
    margin-bottom: 18px;
  }

  .site-navbar__inner {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .site-navbar__brand {
    color: #111827;
    text-decoration: none;
    font-size: 18px;
    font-weight: 700;
  }

  .site-navbar__links {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .site-navbar__link {
    color: #1f2937;
    text-decoration: none;
    font-size: 14px;
    padding: 8px 12px;
    border-radius: 6px;
  }

  .site-navbar__link:hover {
    background: #f3f4f6;
  }

  .site-navbar__link--logout {
    background: #ef4444;
    color: #ffffff;
  }

  .site-navbar__link--logout:hover {
    background: #dc2626;
  }
</style>

<nav class="site-navbar">
  <div class="site-navbar__inner">
    <a class="site-navbar__brand" href="<?=$basePath?>/index.php">Online Quiz</a>

    <div class="site-navbar__links">
      <?php if ($isLoggedIn): ?>
        <!-- Logged-in links -->
        <a class="site-navbar__link" href="<?=$dashboardLink?>">Dashboard</a>
        <a class="site-navbar__link" href="<?=$quizzesLink?>">Quizzes</a>
        <a class="site-navbar__link" href="<?=$leaderboardLink?>">Leaderboard</a>
        <a class="site-navbar__link site-navbar__link--logout" href="<?=$basePath?>/auth/logout.php">Logout</a>
      <?php else: ?>
        <!-- Guest links -->
        <a class="site-navbar__link" href="<?=$basePath?>/index.php">Home</a>
        <a class="site-navbar__link" href="<?=$basePath?>/auth/login.php">Login</a>
        <a class="site-navbar__link" href="<?=$basePath?>/auth/register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>