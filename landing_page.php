<?php
    session_start();

    include("connection.php");
    include("functions.php");

    $user_data = check_login($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Uni Finds - Scholarship/Internship Finder</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
    rel="stylesheet"/>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap"
    rel="stylesheet"/>
  <link href="styles.css" rel="stylesheet" />
</head>
<body class="relative min-h-screen flex flex-col">
  <div class="absolute inset-0 z-0">
    <img
      alt="A placeholder image of a university campus with students walking and buildings in the background"
      class="w-full h-full object-cover"
      height="1080"
      src="https://storage.googleapis.com/a1aa/image/zT52ObljyxYZMBccYUItvYKXup1sdA1mi2b5eA5NanleMk6TA.jpg"
      width="1920"
    />
    <div class="absolute inset-0 bg-black opacity-50"></div>
  </div>
  <div class="relative z-10 flex-grow flex items-center justify-center text-center px-4">
    <div class="text-white">
      <h1 class="text-4xl md:text-6xl font-bold drop-shadow-lg">Welcome to Uni Finds</h1>
      <p class="mt-4 text-lg md:text-2xl drop-shadow-lg">
        Discover the best scholarships and internships tailored for you
      </p>
      <div class="mt-8 flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-4">
        <a href="signup.php"><button class="btn-blue">Get Started</button></a>
        <a href="about_us.php"><button class="btn-green">Learn More</button></a>
      </div>
    </div>
  </div>
  <footer class="relative z-10 bg-gray-800 text-white text-center py-4">
    <p>© 2024 Uni Finds. All rights reserved.</p>
  </footer>
</body>
</html>
