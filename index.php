<?php
session_start();
include('includes/db_connection.php');

// Ensure user is logged in
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}

$user_name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];

// Initialize result variable
$result = null;

// Fetch opportunities with prepared statement
if (!isset($_POST['submit'])) {
    // Modify the query to exclude soft-deleted opportunities
    $query = "SELECT * FROM opportunities WHERE is_deleted = 0 ORDER BY deadline ASC";
    $result = mysqli_query($conn, $query);
} else {
    $type = $_POST['type'] ?? "";
    // Modify the prepared statement to exclude soft-deleted opportunities
    $stmt = mysqli_prepare($conn, "SELECT * FROM opportunities WHERE type LIKE ? AND is_deleted = 0 ORDER BY deadline ASC");
    $searchTerm = "%" . $type . "%";
    mysqli_stmt_bind_param($stmt, "s", $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uni Finds - Explore Opportunities</title>
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
                        <li><a class="flex items-center space-x-2 hover:bg-gray-50 p-3 rounded-lg transition-colors" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                        </li>
                        <li><a class="flex items-center space-x-2 bg-blue-50 text-blue-700 p-3 rounded-lg" href="index.php">
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
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Explore Opportunities</h2>
                <p class="text-gray-600">Search and discover opportunities that match your interests.</p>
            </div>

            <!-- Search Form -->
            <div class="dashboard-card bg-white rounded-lg p-6 shadow mb-6">
                <form method="POST" action="" class="space-y-4">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input 
                                type="text" 
                                name="type" 
                                placeholder="Search by type (e.g., Internship, Scholarship)" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                value="<?php echo htmlspecialchars($_POST['type'] ?? ''); ?>"
                            />
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" name="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                            <a href="?" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2">
                                <i class="fas fa-redo"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Opportunities List -->
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <div class="space-y-4">
                    <?php while ($opportunity = mysqli_fetch_assoc($result)): ?>
                        <div class="dashboard-card bg-white rounded-lg p-6 shadow">
                            <div class="flex flex-col md:flex-row md:items-center gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($opportunity['title']); ?>
                                        </h3>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm">
                                            <?php echo htmlspecialchars($opportunity['type']); ?>
                                        </span>
                                    </div>
                                    <p class="text-gray-600 mb-4">
                                        <?php echo htmlspecialchars($opportunity['description'] ?? 'No description available'); ?>
                                    </p>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="far fa-calendar-alt text-blue-600 mr-2"></i>
                                        <span>Deadline: <?php echo htmlspecialchars($opportunity['deadline'] ?? 'Not specified'); ?></span>
                                    </div>
                                </div>
                                <?php if (isset($opportunity['link'])): ?>
                                    <div class="flex-shrink-0">
                                        <a 
                                            href="<?php echo htmlspecialchars($opportunity['link']); ?>" 
                                            target="_blank"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                        >
                                            <span>View Details</span>
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-card bg-white rounded-lg p-12 shadow text-center">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-search text-6xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">No opportunities found</h3>
                    <p class="text-gray-600">Try adjusting your search criteria or check back later</p>
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