<?php
define('SITE_ROOT', true);
session_start();

// Database connection
require_once 'includes/db_connection.php';
require_once 'includes/profile_tracking.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables for pre-filling
$title = '';
$description = '';
$type = '';
$link = '';
$deadline = '';

// Check if editing an existing opportunity
if (isset($_GET['edit']) && $_GET['edit'] == 1) {
    $title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : '';
    $description = isset($_GET['description']) ? htmlspecialchars($_GET['description']) : '';
    $type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : '';
    $link = isset($_GET['link']) ? htmlspecialchars($_GET['link']) : '';
    $deadline = isset($_GET['deadline']) ? htmlspecialchars($_GET['deadline']) : '';
}

// Handle opportunity sharing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $type = filter_var($_POST['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $link = filter_var($_POST['link'], FILTER_SANITIZE_URL);
    $deadline = filter_var($_POST['deadline'], FILTER_SANITIZE_STRING);

    // Validate required inputs
    if (empty($title) || empty($description) || empty($type) || empty($deadline)) {
        $_SESSION['error'] = "All fields are required.";
        header('Location: share.php');
        exit();
    } else {
        // Insert opportunity into the database
        $query = "INSERT INTO opportunities (user_id, title, description, type, link, deadline) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssss", $user_id, $title, $description, $type, $link, $deadline);

        if ($stmt->execute()) {
            // Track user's shared opportunities
            incrementOpportunityShared($conn, $user_id);

            // Notify user of successful sharing
            createNotification(
                $conn,
                $user_id,
                'opportunity_shared',
                "You've shared a new opportunity: " . $title,
                $stmt->insert_id
            );

            $_SESSION['success'] = "Opportunity shared successfully!";
            header('Location: shared_by_me.php');
            exit();
        } else {
            $_SESSION['error'] = "Error sharing opportunity. Please try again.";
            header('Location: share.php');
            exit();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uni Finds - Share Opportunity</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"/>
    <style>
        .dashboard-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
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
                        <li><a class="flex items-center space-x-2 bg-blue-50 text-blue-700 p-3 rounded-lg" href="share.php">
                            <i class="fas fa-plus"></i><span>Share an Opportunity</span></a>
                        </li>
                        <li><a class="flex items-center space-x-2 hover:bg-gray-50 p-3 rounded-lg transition-colors" href="profile_settings.php">
                            <i class="fas fa-user-cog"></i><span>Profile Settings</span></a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Share an Opportunity</h2>
                <p class="text-gray-600">Fill out the form below to share an opportunity with others.</p>
            </div>

            <!-- Sharing Form -->
            <div class="dashboard-card bg-white rounded-lg p-6 shadow">
                <form method="POST" action="share.php" class="space-y-4">
                    <div>
                        <label for="title" class="block font-medium text-gray-700">Opportunity Title</label>
                        <input 
                            type="text" 
                            name="title" 
                            id="title" 
                            value="<?php echo htmlspecialchars($title); ?>" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label for="description" class="block font-medium text-gray-700">Description</label>
                        <textarea 
                            name="description" 
                            id="description" 
                            rows="4" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    <div>
                        <label for="type" class="block font-medium text-gray-700">Type</label>
                        <select 
                            name="type" 
                            id="type" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Internship" <?php echo ($type == 'Internship') ? 'selected' : ''; ?>>Internship</option>
                            <option value="Scholarship" <?php echo ($type == 'Scholarship') ? 'selected' : ''; ?>>Scholarship</option>
                            <option value="Workshop" <?php echo ($type == 'Workshop') ? 'selected' : ''; ?>>Workshop</option>
                            <option value="Other" <?php echo ($type == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="deadline" class="block font-medium text-gray-700">Deadline</label>
                        <input 
                            type="date" 
                            name="deadline" 
                            id="deadline" 
                            value="<?php echo htmlspecialchars($deadline); ?>" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label for="link" class="block font-medium text-gray-700">Link (Optional)</label>
                        <input 
                            type="url" 
                            name="link" 
                            id="link" 
                            value="<?php echo htmlspecialchars($link); ?>" 
                            placeholder="https://example.com"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Share Opportunity
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

