<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: ../../index.php");
    exit();
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch total front desk users for pagination
$sql_total = "SELECT COUNT(*) as total FROM users WHERE role = 'front_desk'";
$total_result = $conn->query($sql_total);
$total_front_desk = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_front_desk / $records_per_page);

// Fetch front desk users for current page
$sql = "SELECT username, role FROM users WHERE role = 'front_desk' LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $records_per_page, $offset);
$stmt->execute();
$front_desk_users = $stmt->get_result();

// Get first letter of username for avatar
$avatar_letter = strtoupper(substr($_SESSION['username'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Front Desk Users</title>
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

        <!-- Front Desk Users Content -->
        <div class="container mx-auto max-w-6xl p-4 sm:p-8">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-3xl font-bold mb-6 text-gray-800">Front Desk Users</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                        <thead>
                            <tr class="text-left bg-gray-50">
                                <th class="border px-4 py-3 text-sm font-medium text-gray-700">Username</th>
                                <th class="border px-4 py-3 text-sm font-medium text-gray-700">Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $front_desk_users->fetch_assoc()) { ?>
                                <tr>
                                    <td class="border px-4 py-3 text-gray-600"><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td class="border px-4 py-3 text-gray-600"><?php echo htmlspecialchars($row['role']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1) { ?>
                    <div class="mt-6 flex justify-center space-x-2">
                        <?php if ($page > 1) { ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Previous</a>
                        <?php } ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                            <a href="?page=<?php echo $i; ?>" class="px-4 py-2 rounded-lg <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition duration-300"><?php echo $i; ?></a>
                        <?php } ?>
                        <?php if ($page < $total_pages) { ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Next</a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>