<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $register_error = "Passwords do not match. Please try again.";
    } else {
        $password_hashed = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'front_desk')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password_hashed);
        if ($stmt->execute()) {
            $register_success = true;
            $registered_username = $username;
        } else {
            $register_error = "Registration failed. Username may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Front Desk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-green-100 min-h-screen flex">
    <!-- Sidebar -->
    <div id="sidebar" class="bg-white w-64 h-screen fixed top-0 left-0 shadow-lg flex flex-col p-4 sm:flex sm:w-64">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">Gym Management</h2>
            <button class="sm:hidden text-gray-600 focus:outline-none" onclick="toggleSidebar()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <nav class="flex flex-col space-y-2">
            <a href="./dashboard.php" class="text-blue-500 hover:bg-blue-100 px-4 py-2 rounded-lg font-medium">Dashboard</a>
            <a href="./register_front_desk.php" class="text-blue-500 hover:bg-blue-100 px-4 py-2 rounded-lg font-medium">Register Front Desk</a>
            <a href="./logout.php" class="text-blue-500 hover:bg-blue-100 px-4 py-2 rounded-lg font-medium">Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 sm:ml-64">
        <!-- Navbar -->
        <div class="bg-white shadow p-4 flex justify-between items-center">
            <button class="sm:hidden text-gray-600 focus:outline-none" onclick="toggleSidebar()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <div class="text-gray-800 font-medium">
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
            </div>
            <a href="./logout.php" class="text-blue-500 hover:underline font-medium">Logout</a>
        </div>

        <!-- Registration Content -->
        <div class="container mx-auto max-w-3xl p-4 sm:p-8">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <?php if (isset($register_success) && $register_success) { ?>
                    <div class="text-center">
                        <h2 class="text-3xl font-bold mb-4 text-green-600">Front Desk Registration Successful!</h2>
                        <p class="text-lg mb-4">Username: <strong class="text-blue-600"><?php echo htmlspecialchars($registered_username); ?></strong></p>
                        <div class="flex justify-center gap-4">
                            <a href="./dashboard.php" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition duration-300">Back to Dashboard</a>
                            <a href="./register_front_desk.php" class="bg-gray-500 text-white px-6 py-2 rounded-full hover:bg-gray-600 transition duration-300">Register Another</a>
                        </div>
                    </div>
                <?php } else { ?>
                    <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Register Front Desk</h2>
                    <?php if (isset($register_error)) { ?>
                        <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($register_error); ?></p>
                    <?php } ?>
                    <form method="POST" action="" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" name="username" id="username" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Enter username" required>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" name="password" id="password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Enter password" required>
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Confirm password" required>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">Register Front Desk</button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>