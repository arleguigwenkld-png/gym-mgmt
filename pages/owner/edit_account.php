<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current user data
    $sql = "SELECT username, password FROM users WHERE id = ? AND role = 'owner'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Initialize update query and parameters
    $sql_update = "UPDATE users SET username = ?";
    $params = [$new_username];
    $types = "s";

    // Check if username is different and unique
    if ($new_username !== $user['username']) {
        $sql_check = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $new_username, $_SESSION['user_id']);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = "Username already exists.";
        }
    }

    // Handle password update if new password is provided
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $error = "Current password is required to update password.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
            $password_hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $sql_update .= ", password = ?";
            $params[] = $password_hashed;
            $types .= "s";
        }
    }

    // Proceed with update if no errors
    if (!isset($error)) {
        if ($new_username === $user['username'] && empty($new_password)) {
            $error = "No changes made to username or password.";
        } else {
            $sql_update .= " WHERE id = ? AND role = 'owner'";
            $params[] = $_SESSION['user_id'];
            $types .= "i";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param($types, ...$params);
            if ($stmt_update->execute()) {
                $_SESSION['username'] = $new_username;
                $success = "Account updated successfully.";
            } else {
                $error = "Failed to update account. Please try again.";
            }
        }
    }
}

// Get first letter of username for avatar
$avatar_letter = strtoupper(substr($_SESSION['username'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
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

        <!-- Edit Account Content -->
        <div class="container mx-auto max-w-3xl p-4 sm:p-8">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Edit Account</h2>
                <?php if (isset($success)) { ?>
                    <div class="text-center">
                        <p class="text-green-600 text-lg mb-4"><?php echo htmlspecialchars($success); ?></p>
                        <a href="./dashboard.php" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition duration-300">Back to Dashboard</a>
                    </div>
                <?php } else { ?>
                    <?php if (isset($error)) { ?>
                        <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
                    <?php } ?>
                    <form method="POST" action="" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Enter new username" required>
                            </div>
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input type="password" name="current_password" id="current_password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Enter current password (required to change password)">
                            </div>
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password (optional)</label>
                                <input type="password" name="new_password" id="new_password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Enter new password">
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Confirm new password">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">Update Account</button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>