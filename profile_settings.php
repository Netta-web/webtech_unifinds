<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {  // We can keep this as user_id in the session
    header('Location: login.php');
    exit();
}

// Database connection
require_once 'includes/db_connection.php';

// Initialize variables for error/success messages
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Start transaction
        $conn->begin_transaction();

        // Update name and email - Changed user_id to id to match database schema
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        // Handle password update if provided
        if (!empty($password)) {
            if ($password !== $confirm_password) {
                throw new Exception("Passwords do not match");
            }
            if (strlen($password) < 8) {
                throw new Exception("Password must be at least 8 characters long");
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Changed user_id to id to match database schema
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        $success_message = "Profile updated successfully!";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

// Fetch current user data - Changed user_id to id to match database schema
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Uni Finds</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="font-roboto bg-gray-100 flex flex-col min-h-screen">
    <!-- Header Section -->
    <header class="bg-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Uni Finds</h1>
            <nav>
                <ul class="flex space-x-6">
                    <li><a class="hover:bg-blue-700 px-3 py-2 rounded transition-colors" href="dashboard.php">Home</a></li>
                    <li><a class="hover:bg-blue-700 px-3 py-2 rounded transition-colors" href="about_us.html">About</a></li>
                    <li><a class="hover:bg-blue-700 px-3 py-2 rounded transition-colors" href="contact.html">Contact</a></li>
                    <li><a class="hover:bg-blue-700 px-3 py-2 rounded transition-colors" href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="flex flex-1">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg hidden lg:block">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <p class="font-semibold">Welcome,</p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                    </div>
                </div>
                <nav>
                    <ul class="space-y-2">
                        <li><a class="flex items-center space-x-2 hover:bg-gray-50 p-3 rounded-lg transition-colors" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                        </li>
                        <li><a class="flex items-center space-x-2 hover:bg-gray-50 p-3 rounded-lg transition-colors" href="index.php">
                            <i class="fas fa-search"></i><span>Explore Opportunities</span></a>
                        </li>
                        <li><a class="flex items-center space-x-2 hover:bg-gray-50 p-3 rounded-lg transition-colors" href="shared_by_me.php">
                            <i class="fas fa-share-alt"></i><span>Shared by Me</span></a>
                        </li>
                        <li><a class="flex items-center space-x-2 hover:bg-gray-50 p-3 rounded-lg transition-colors" href="share.php">
                            <i class="fas fa-plus"></i><span>Share an Opportunity</span></a>
                        </li>
                        <li><a class="flex items-center space-x-2 bg-blue-50 text-blue-700 p-3 rounded-lg" href="profile_settings.php">
                            <i class="fas fa-user-cog"></i><span>Profile Settings</span></a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="dashboard-card bg-white rounded-lg p-6 shadow">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Profile Settings</h2>

                <!-- Display Success/Failure Messages -->
                <?php if ($success_message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="profile_settings.php" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" id="name" name="name"
                               value="<?php echo htmlspecialchars($user['name']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" id="password" name="password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Leave blank to keep your current password">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Save Button -->
                    <button type="submit"
                            class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Save Changes
                    </button>
                </form>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-8">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <p class="text-gray-600">© 2024 Uni Finds. All rights reserved.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-600 hover:text-blue-600"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-600 hover:text-blue-600"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-gray-600 hover:text-blue-600"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
