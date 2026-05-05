<?php
// auth/login.php
// Simple login page with password verification and session handling
session_start();

// If the user is already logged in, send them to the correct dashboard immediately.
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
  if ($_SESSION['role'] === 'teacher') {
    header('Location: ../teacher/dashboard.php');
    exit;
  }

  header('Location: ../student/dashboard.php');
  exit;
}

require_once __DIR__ . '/../config/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($password === '') $errors[] = 'Password is required.';

    if (empty($errors)) {
        // Lookup user by email
        $stmt = $mysqli->prepare('SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            // Verify password using password_verify
            if (password_verify($password, $user['password'])) {
                // Start session and store user id and role
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
          $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'teacher') {
                    header('Location: ../teacher/dashboard.php');
                    exit;
                } else {
                    header('Location: ../student/dashboard.php');
                    exit;
                }
            } else {
                $errors[] = 'Invalid credentials.';
            }
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Quiz System</title>
  <style>
    body{font-family:Arial, sans-serif;background:#f5f5f5;margin:0}
    .wrap{width:320px;margin:60px auto;background:#fff;padding:20px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,.1)}
    h2{text-align:center;margin:0 0 12px}
    input{width:100%;padding:8px;margin:6px 0;border:1px solid #ccc;border-radius:4px}
    button{width:100%;padding:10px;background:#007bff;color:#fff;border:0;border-radius:4px;cursor:pointer}
    .errors{color:#b00020;margin-bottom:8px}
    .small{font-size:90%;text-align:center;margin-top:10px}
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
  <div class="wrap">
    <h2>Login</h2>
    <?php if (!empty($_GET['registered'])): ?>
      <div style="color:green;text-align:center;margin-bottom:8px">Registration successful. Please login.</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="errors"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php endif; ?>
    <form method="post" action="">
      <!-- Email -->
      <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
      <!-- Password -->
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <div class="small">Don't have an account? <a href="register.php">Register</a></div>
  </div>
</body>
</html>
