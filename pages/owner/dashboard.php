<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: ../../index.php");
    exit();
}

// Fetch total front desk users
$sql_total = "SELECT COUNT(*) as total FROM users WHERE role = 'front_desk'";
$total_result = $conn->query($sql_total);
$total_front_desk = $total_result->fetch_assoc()['total'];

// Fetch total active clients
$sql_active_clients = "SELECT COUNT(*) as active FROM clients WHERE membership_status = 'Active'";
$active_result = $conn->query($sql_active_clients);
$total_active_clients = $active_result->fetch_assoc()['active'];

// Fetch total pending clients
$sql_pending_clients = "SELECT COUNT(*) as pending FROM pending_clients WHERE membership_status = 'Pending'";
$pending_result = $conn->query($sql_pending_clients);
$total_pending_clients = $pending_result->fetch_assoc()['pending'];

// Fetch total expired clients
$sql_expired_clients = "SELECT COUNT(*) as expired FROM clients WHERE membership_status = 'Expired'";
$expired_result = $conn->query($sql_expired_clients);
$total_expired_clients = $expired_result->fetch_assoc()['expired'];

// Get first letter of username for avatar
$avatar_letter = strtoupper(substr($_SESSION['username'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>
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
            <div class="flex justify-end items-center">
                <a href="./edit_account.php" class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl font-bold">
                        <?php echo htmlspecialchars($avatar_letter); ?>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="text-gray-600 text-sm">Owner</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="container mx-auto max-w-6xl p-4 sm:p-8">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-3xl font-bold mb-6 text-gray-800">Owner Dashboard</h2>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-8">
                    <a href="./front_desk_users.php" class="block bg-blue-50 p-6 rounded-lg shadow text-center hover:bg-blue-100 transition duration-300">
                        <h3 class="text-xl font-semibold text-gray-700">Total Front Desk</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $total_front_desk; ?></p>
                    </a>
                    <a href="./active_client.php" class="block bg-green-50 p-6 rounded-lg shadow text-center hover:bg-green-100 transition duration-300">
                        <h3 class="text-xl font-semibold text-gray-700">Total Active Clients</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo $total_active_clients; ?></p>
                    </a>
                    <a href="./pending_client.php" class="block bg-yellow-50 p-6 rounded-lg shadow text-center hover:bg-yellow-100 transition duration-300">
                        <h3 class="text-xl font-semibold text-gray-700">Total Pending Clients</h3>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo $total_pending_clients; ?></p>
                    </a>
                    <a href="./expired_client.php" class="block bg-red-50 p-6 rounded-lg shadow text-center hover:bg-red-100 transition duration-300">
                        <h3 class="text-xl font-semibold text-gray-700">Total Expired Clients</h3>
                        <p class="text-3xl font-bold text-red-600"><?php echo $total_expired_clients; ?></p>
                    </a>
                </div>
                <a href="./register_front_desk.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">Register Front Desk</a>
            </div>
        </div>
    </div>
</body>
</html>