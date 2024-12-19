
    function validatePassword() {
        // Get the password and confirm password values
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        // Check if passwords match
        const errorElement = document.getElementById('password-error');
        const submitButton = document.getElementById('submit-button');

        if (password !== confirmPassword) {
            errorElement.classList.remove('hidden');
            submitButton.disabled = true;
        } else {
            errorElement.classList.add('hidden');
            submitButton.disabled = false;
        }
    }

    function retrieveUserDetails(event) {
        // Prevent form submission (for demonstration purposes)
        event.preventDefault();

        // Retrieve user input values
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        // You can display these values in the console (or use them elsewhere)
        console.log('User Details:');
        console.log('Name:', name);
        console.log('Email:', email);
        console.log('Password:', password);

        // Optionally, you can return the details for further processing
        return {
            name: name,
            email: email,
            password: password
        };
    }


    function validateLoginForm() {
        // Hide error messages initially
        document.getElementById('name-error').classList.add('hidden');
        document.getElementById('password-error').classList.add('hidden');
        document.getElementById('response-message').classList.add('hidden');
        
        // Get form inputs
        var name = document.getElementById('name').value;
        var password = document.getElementById('password').value;
        
        // Validate Name
        if (name.trim() === "") {
            document.getElementById('name-error').classList.remove('hidden');
            return false; // Prevent form submission
        }
        
        // Validate Password
        if (password.trim() === "") {
            document.getElementById('password-error').classList.remove('hidden');
            return false; // Prevent form submission
        }

        // Simulate form submission result
        simulateLogin(name, password);
        
        // Prevent the form from submitting to avoid page refresh
        return false;
    }

    function simulateLogin(name, password) {
        // Mock success or failure
        var isLoginSuccessful = (name === "test" && password === "password123");  // Example condition for success

        var responseMessage = document.getElementById('response-message');
        responseMessage.classList.remove('hidden');

        if (isLoginSuccessful) {
            responseMessage.textContent = "Login Successful!";
            responseMessage.classList.remove('bg-red-500', 'text-white');
            responseMessage.classList.add('bg-green-500', 'text-white');
        } else {
            responseMessage.textContent = "Invalid credentials. Please try again.";
            responseMessage.classList.remove('bg-green-500', 'text-white');
            responseMessage.classList.add('bg-red-500', 'text-white');
        }
    }

    

