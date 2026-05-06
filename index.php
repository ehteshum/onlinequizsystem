<?php
// index.php
// Public landing page for the quiz system
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Online Quiz System</title>
  <style>
    /* Page reset and background */
    body {
      margin: 0;
      min-height: 100vh;
      font-family: Arial, sans-serif;
      background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.18), transparent 30%),
        radial-gradient(circle at bottom right, rgba(16, 185, 129, 0.14), transparent 28%),
        linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      color: #0f172a;
      padding: 0;
      box-sizing: border-box;
    }

    .page {
      min-height: calc(100vh - 78px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      box-sizing: border-box;
    }

    /* Center hero container */
    .hero {
      width: 100%;
      max-width: 760px;
      text-align: center;
      background: rgba(255, 255, 255, 0.78);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.6);
      border-radius: 24px;
      padding: 56px 32px;
      box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
    }

    /* Big heading and supporting copy */
    .hero h1 {
      margin: 0;
      font-size: clamp(2.6rem, 6vw, 4.8rem);
      line-height: 1.05;
      letter-spacing: -0.03em;
    }

    .hero p {
      max-width: 620px;
      margin: 18px auto 0;
      font-size: clamp(1rem, 2.2vw, 1.2rem);
      line-height: 1.7;
      color: #475569;
    }

    /* Button group layout */
    .actions {
      display: flex;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
      margin-top: 34px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 140px;
      padding: 14px 20px;
      border-radius: 999px;
      text-decoration: none;
      font-weight: 700;
      font-size: 1rem;
      transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }

    .btn:hover {
      transform: translateY(-2px);
    }

    .btn-login {
      background: #0f172a;
      color: #fff;
      box-shadow: 0 10px 20px rgba(15, 23, 42, 0.18);
    }

    .btn-login:hover {
      background: #020617;
    }

    .btn-register {
      background: #38bdf8;
      color: #082f49;
      box-shadow: 0 10px 20px rgba(56, 189, 248, 0.22);
    }

    .btn-register:hover {
      background: #0ea5e9;
      color: #fff;
    }

    /* Responsive spacing on smaller screens */
    @media (max-width: 640px) {
      .hero {
        padding: 44px 22px;
        border-radius: 20px;
      }

      .btn {
        width: 100%;
      }

      .actions {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/includes/navbar.php'; ?>
  <div class="page">
    <!-- Hero section: main landing content -->
    <main class="hero">
      <!-- Main heading -->
      <h1>Online Quiz System</h1>

      <!-- Short description -->
      <p>
        Create quizzes, manage questions, and let students attempt tests in a clean and simple learning platform.
      </p>

      <!-- Primary actions -->
      <div class="actions">
        <a class="btn btn-login" href="auth/login.php">Login</a>
        <a class="btn btn-register" href="auth/register.php">Register</a>
      </div>
    </main>
  </div>
</body>
</html>
