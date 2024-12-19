<?php
include('includes/db_connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password match
    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if user already exists
    $check_query = "SELECT * FROM users WHERE name = '$name' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "User with this name or email already exists!";
        exit();
    }

    // Insert new user
    $insert_query = "INSERT INTO users (name, email, password_hash) VALUES ('$name', '$email', '$hashed_password')";
    
    if (mysqli_query($conn, $insert_query)) {
        // Get the newly inserted user's ID
        $user_id = mysqli_insert_id($conn);
        
        // Start session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $name;
        
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uni Finds - Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cover bg-center h-screen" style="background-image: url('https://storage.googleapis.com/a1aa/image/zT52ObljyxYZMBccYUItvYKXup1sdA1mi2b5eA5NanleMk6TA.jpg');">
    <div class="flex items-center justify-center h-full">
        <div class="bg-white bg-opacity-75 p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Sign Up for Uni Finds</h2>
            <form action="signup.php" method="POST" class="space-y-6">
                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Your Full Name"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="your.email@example.com"
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

                <!-- Confirm Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
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
                        Sign Up
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Log In</a>
            </p>
        </div>
    </div>
</body>
</html>