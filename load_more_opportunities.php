<?php
session_start();
require_once 'includes/db_connection.php';

// Ensure user is logged in
if (!isset($_SESSION['name'])) {
    echo "Unauthorized access";
    exit();
}

// Get start point and type filter
$start = isset($_GET['start']) ? intval($_GET['start']) : 10;
$type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

// Base query
$sql = "SELECT * FROM opportunities";

// Add type filter if specified
if (!empty($type)) {
    $sql .= " WHERE type = '$type'";
}

// Add limit and offset
$sql .= " LIMIT 18446744073709551615 OFFSET $start";

$result = mysqli_query($conn, $sql);

while ($opportunity = mysqli_fetch_assoc($result)):
?>
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">
            <?php echo htmlspecialchars($opportunity['title']); ?>
        </h2>
        <div class="mb-4">
            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                <?php echo htmlspecialchars($opportunity['type']); ?>
            </span>
        </div>
        <p class="text-gray-600 mb-4">
            <?php echo htmlspecialchars($opportunity['description'] ?? 'No description available'); ?>
        </p>
        
        <form method="POST" class="flex space-x-4">
            <input type="hidden" name="opportunity_id" value="<?php echo htmlspecialchars($opportunity['id']); ?>">
            <button type="submit" name="save" class="flex-1 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                Save Opportunity
            </button>
            <button type="submit" name="apply" class="flex-1 bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">
                Mark as Applied
            </button>
        </form>
    </div>
<?php 
endwhile;

mysqli_close($conn);
?>