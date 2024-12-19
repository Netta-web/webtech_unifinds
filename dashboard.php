<?php
session_start();

// Database connection
require_once 'includes/db_connection.php';
define('SITE_ROOT', realpath(dirname(__FILE__)));
include 'includes/profile_tracking.php';
require_once 'includes/profile_tracking.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_name = $user['name'];

// Fetch Saved Opportunities
$saved_opportunities_query = "
    SELECT o.title, o.type, o.deadline
    FROM opportunities o
    JOIN saved_opportunities so ON o.opportunity_id = so.opportunity_id
    WHERE so.user_id = ?
    ORDER BY o.deadline ASC
    LIMIT 3
";
$stmt = $conn->prepare($saved_opportunities_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$saved_opportunities = $stmt->get_result();

// Fetch Notifications
$notifications_query = "
    SELECT type, message, related_opportunity_id, created_at
    FROM notifications 
    WHERE user_id = ? AND is_read = FALSE 
    ORDER BY created_at DESC 
    LIMIT 3
";
$stmt = $conn->prepare($notifications_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();

// Fetch Profile Tracking
$profile_query = "
    SELECT total_profile_fields, completed_fields, 
           ROUND((completed_fields * 100.0 / total_profile_fields), 2) as completion_percentage
    FROM user_profile_tracking
    WHERE user_id = ?
";
$stmt = $conn->prepare($profile_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile_details = $stmt->get_result()->fetch_assoc();

// Fetch user activity summary
$activityQuery = "SELECT total_opportunities_shared, total_opportunities_applied, last_activity_date FROM user_activities WHERE user_id = ?";
$stmt = $conn->prepare($activityQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userActivity = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Uni Finds - Home Dashboard</title>
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
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div>
                        <p class="font-semibold">Welcome,</p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($user_name); ?></p>
                    </div>
                </div>
                <nav>
                    <ul class="space-y-2">
                        <li><a class="flex items-center space-x-2 bg-blue-50 text-blue-700 p-3 rounded-lg" href="dashboard.php">
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
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Dashboard Overview</h2>
                <p class="text-gray-600">Here's what's happening with your account.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="dashboard-card bg-white rounded-lg p-6 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800">Profile Completion</h3>
                        <i class="fas fa-user-check text-blue-600"></i>
                    </div>
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200">
                                    Progress
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-blue-600">
                                    <?php echo $profile_details['completion_percentage'] ?? 0; ?>%
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                            <div style="width:<?php echo $profile_details['completion_percentage'] ?? 0; ?>%" 
                                 class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-600">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card bg-white rounded-lg p-6 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800">Opportunities</h3>
                        <i class="fas fa-bookmark text-blue-600"></i>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Shared</span>
                            <span class="font-semibold"><?php echo $userActivity['total_opportunities_shared'] ?? 0; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Applied</span>
                            <span class="font-semibold"><?php echo $userActivity['total_opportunities_applied'] ?? 0; ?></span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card bg-white rounded-lg p-6 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800">Recent Activity</h3>
                        <i class="fas fa-clock text-blue-600"></i>
                    </div>
                    <p class="text-gray-600">Last active:</p>
                    <p class="font-semibold">
                        <?php 
                        $last_activity = new DateTime($userActivity['last_activity_date'] ?? 'now');
                        echo $last_activity->format('M d, Y H:i');
                        ?>
                    </p>
                </div>
            </div>

            <!-- Notifications and Saved Opportunities -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Notifications -->
                <div class="dashboard-card bg-white rounded-lg p-6 shadow">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Recent Notifications</h3>
                        <span class="text-sm text-blue-600 hover:underline cursor-pointer">View All</span>
                    </div>
                    <div class="space-y-4">
                        <?php while($notification = $notifications->fetch_assoc()): ?>
                        <div class="flex items-start space-x-4 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-bell text-blue-600"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($notification['type']); ?></p>
                                <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php 
                                    $created_at = new DateTime($notification['created_at']);
                                    echo $created_at->format('M d, Y H:i');
                                    ?>
                                </p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Saved Opportunities -->
                <div class="dashboard-card bg-white rounded-lg p-6 shadow">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Saved Opportunities</h3>
                        <span class="text-sm text-blue-600 hover:underline cursor-pointer">View All</span>
                    </div>
                    <div class="space-y-4">
                        <?php while($opportunity = $saved_opportunities->fetch_assoc()): ?>
                        <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                            <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($opportunity['title']); ?></h4>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($opportunity['type']); ?></p>
                            <div class="flex items-center mt-2 text-sm">
                                <i class="far fa-calendar-alt text-blue-600 mr-2"></i>
                                <span class="text-gray-500">Deadline: 
                                    <?php 
                                    $deadline = new DateTime($opportunity['deadline']);
                                    echo $deadline->format('M d, Y');
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
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

<?php
$stmt-> close();
$conn-> close();
?>