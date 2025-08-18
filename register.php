<?php
require_once 'db/config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $error = "All fields are required";
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $error = "Username already exists";
        } else {
            // Generate initial secret key
            $secret_key = bin2hex(random_bytes(8));

            // Create new user
            $stmt = $pdo->prepare("INSERT INTO users (username, password, secret_key) VALUES (?, ?, ?)");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            if ($stmt->execute([$username, $hashed_password, $secret_key])) {
                $_SESSION['success'] = "Registration successful! Please log in.";
                header("Location: index.php");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - InternLog</title>
    <link href="css/output.css" rel="stylesheet">
    <script src="js/alpine.min.js" defer></script>
</head>

<body class="bg-background">
    <div class="min-h-screen flex">
        <!-- Left side with background image -->
        <div class="hidden lg:flex lg:w-1/2 bg-primary relative">
            <div class="absolute inset-0 bg-gradient-to-r from-primary/90 to-primary/70"></div>
            <div class="relative w-full flex items-center justify-center px-12">
                <div class="max-w-lg">
                    <h1 class="text-4xl font-bold text-white mb-6">Start Your Journey</h1>
                    <p class="text-lg text-primary-foreground/90">Create your InternLog account to begin documenting
                        your internship experience and sharing your progress with supervisors.</p>
                </div>
            </div>
        </div>

        <!-- Right side with registration form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="max-w-md w-full">
                <div class="text-center mb-8">
                    <div class="bg-primary p-3 rounded-xl inline-flex mb-4">
                        <svg class="w-8 h-8 text-primary-foreground" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-foreground">Create your account</h2>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-lg mb-6">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="username"
                            class="block text-sm font-medium text-muted-foreground mb-2">Username</label>
                        <input type="text" id="username" name="username" required
                            class="bg-input shadow-sm border border-border rounded-lg w-full px-4 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>

                    <div>
                        <label for="password"
                            class="block text-sm font-medium text-muted-foreground mb-2">Password</label>
                        <input type="password" id="password" name="password" required
                            class="bg-input shadow-sm border border-border rounded-lg w-full px-4 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                    </div>

                    <button type="submit"
                        class="w-full bg-primary text-primary-foreground py-2 px-4 rounded-lg hover:bg-primary/90 transition duration-200">
                        Create Account
                    </button>

                    <p class="text-center text-sm text-muted-foreground">
                        Already have an account? <a href="index.php" class="text-primary hover:underline">Sign in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>

</html>