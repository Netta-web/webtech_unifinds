<?php
include('includes/db_connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the name and password from the login form
    $name = mysqli_real_escape_string($conn, $_POST['name']); 
    $password = $_POST['password'];

    // Query to check if the name exists
    $query = "SELECT * FROM users WHERE name = '$name'";
    $result = mysqli_query($conn, $query);

    // Check if the name exists
    if (mysqli_num_rows($result) > 0) {
        // Fetch the user record
        $user = mysqli_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, start session and redirect
            $_SESSION['user_id'] = $user['id']; // Store user_id in session
            $_SESSION['name'] = $user['name']; // Store name in session
            header('Location: dashboard.php');  // Redirect to the dashboard
            exit();
        } else {
            // Password is incorrect
            $error_message = "Incorrect password!";
        }
    } else {
        // Name does not exist
        $error_message = "Name does not exist!";
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uni Finds - Log In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen" style="background-image: url('https://storage.googleapis.com/a1aa/image/zT52ObljyxYZMBccYUItvYKXup1sdA1mi2b5eA5NanleMk6TA.jpg');">
    <div class="flex items-center justify-center h-full">
        <div class="bg-white bg-opacity-75 p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Log In for Uni Finds</h2>
            
            <?php if(isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Your Name"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="********"
                    >
                </div>
            
                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded-md shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
                    >
                        Log In
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Do not have an account? <a href="signup.php" class="text-blue-500 hover:underline">Sign Up</a>
            </p>
        </div>
    </div>
</body>
</html>