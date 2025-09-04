<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_number = $_POST['id_number'];
    $password = $_POST['password'];
    
    // Check users (owner or front desk)
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'owner') {
                header("Location: pages/owner/dashboard.php");
            } else {
                header("Location: pages/front_desk/dashboard.php");
            }
            exit();
        }
    }
    
    // Check clients
    $sql = "SELECT * FROM clients WHERE id_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $client = $result->fetch_assoc();
        if (password_verify($password, $client['password'])) {
            $_SESSION['user_id'] = $client['id'];
            $_SESSION['username'] = $client['id_number'];
            $_SESSION['role'] = 'client';
            header("Location: pages/client/dashboard.php");
            exit();
        }
    }
    
    $error = "Invalid ID Number/Username or Password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-device-width, initial-scale=1.0">
    <title>Gym Management Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-100 to-green-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl max-w-7xl w-full flex flex-col lg:flex-row overflow-hidden mx-4">
            <!-- Left Side: Image -->
            <div class="lg:w-1/2 hidden lg:block">
                <img src="./assets/img/gym-banner.jpg" alt="Gym Image" class="object-cover w-full h-full">
            </div>
            <!-- Right Side: Login Form -->
            <div class="lg:w-1/2 p-10">
                <h2 class="text-4xl font-bold mb-8 text-center text-gray-800">Gym Management Login</h2>
                <?php if (isset($error)) { ?>
                    <div class="flex items-center bg-red-50 border-l-4 border-red-500 p-4 mb-6 text-red-700 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                        <p class="text-base font-medium"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php } ?>
                <form method="POST" action="" class="space-y-8">
                    <div>
                        <label for="id_number" class="block text-base font-medium text-gray-700">ID Number / Username</label>
                        <input type="text" name="id_number" id="id_number" class="mt-2 block w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-base" placeholder="Enter ID Number or Username" required>
                    </div>
                    <div>
                        <label for="password" class="block text-base font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password" class="mt-2 block w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-base" placeholder="Enter password" required>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white p-4 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold text-lg">Login</button>
                </form>
                <p class="mt-6 text-center text-gray-600 text-base">New client? <a href="registration.php" class="text-blue-500 hover:underline">Register here</a></p>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="w-full bg-gray-800 text-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="portal.php" class="hover:text-blue-400 transition duration-200">Home</a></li>
                        <li><a href="portal.php#about" class="hover:text-blue-400 transition duration-200">About</a></li>
                        <li><a href="portal.php#services" class="hover:text-blue-400 transition duration-200">Services</a></li>
                        <li><a href="portal.php#contact" class="hover:text-blue-400 transition duration-200">Contact</a></li>
                    </ul>
                </div>
                <!-- Quick Contact -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Contact</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="tel:+1234567890" class="hover:text-blue-400 transition duration-200 flex items-center">
                                <i class="fas fa-phone-alt mr-2"></i> +1 (234) 567-890
                            </a>
                        </li>
                        <li>
                            <a href="mailto:info@gymmanagement.com" class="hover:text-blue-400 transition duration-200 flex items-center">
                                <i class="fas fa-envelope mr-2"></i> info@gymmanagement.com
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Operating Hours -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Operating Hours</h3>
                    <ul class="space-y-2">
                        <li>Mon - Fri: 6:00 AM - 10:00 PM</li>
                        <li>Sat: 8:00 AM - 8:00 PM</li>
                        <li>Sun: 10:00 AM - 6:00 PM</li>
                    </ul>
                </div>
            </div>
            <p class="mt-8 text-center text-sm">&copy; <?php echo date("Y"); ?> Gym Management. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>