<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-500 text-white p-4 rounded-lg mb-4">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php elseif (isset($_SESSION['error'])): ?>
    <div class="bg-red-500 text-white p-4 rounded-lg mb-4">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>


<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once 'includes/db_connection.php';

// Fetch user's opportunities
$opportunities = [];
$stmt = $conn->prepare("SELECT * FROM opportunities WHERE user_id = ? AND is_deleted = 0 ORDER BY opportunity_id DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $opportunities[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shared Opportunities - Uni Finds</title>
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
                        <li><a class="flex items-center space-x-2 bg-blue-50 text-blue-700 p-3 rounded-lg" href="shared_by_me.php">
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
                <h2 class="text-2xl font-bold text-gray-800 mb-2">My Shared Opportunities</h2>
                <p class="text-gray-600">View and manage the opportunities you've shared.</p>
            </div>

            <!-- Opportunities List -->
            <?php if (empty($opportunities)): ?>
                <p class="text-gray-500">No opportunities shared yet.</p>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($opportunities as $opportunity): ?>
                        <div class="dashboard-card bg-white rounded-lg p-6 shadow">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                <?php echo htmlspecialchars($opportunity['title']); ?>
                            </h3>
                            <p class="text-gray-600 mb-4">
                                <?php echo htmlspecialchars($opportunity['description']); ?>
                            </p>
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <i class="far fa-calendar-alt text-blue-600 mr-2"></i>
                                <span>Deadline: <?php echo htmlspecialchars($opportunity['deadline'] ?? 'Not specified'); ?></span>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <!-- Edit Button -->
                                <a href="share.php?edit=1&id=<?php echo htmlspecialchars($opportunity['opportunity_id']); ?>&title=<?php echo urlencode($opportunity['title']); ?>&description=<?php echo urlencode($opportunity['description']); ?>&type=<?php echo urlencode($opportunity['type']); ?>&link=<?php echo urlencode($opportunity['link']); ?>&deadline=<?php echo urlencode($opportunity['deadline']); ?>" 
                                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <!-- Delete Form -->
                                <form method="POST" action="opportunity_actions.php" 
                                      onsubmit="return confirm('Are you sure you want to delete this opportunity?');">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($opportunity['opportunity_id'] ?? ''); ?>">
                                    <button type="submit" name="action" value="soft_delete" 
                                            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
