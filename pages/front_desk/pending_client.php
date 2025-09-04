<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'front_desk') {
    header("Location: ../../index.php");
    exit();
}

// Handle approve reservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $activation_date = date('Y-m-d', strtotime($_POST['activation_date']));
    $sql = "SELECT * FROM pending_clients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $client = $result->fetch_assoc();
        $expiry_date = date('Y-m-d', strtotime($activation_date . ' + ' . ($client['membership_availed'] === '1 month' ? '1 month' : '6 months')));
        $membership_status = (strtotime($expiry_date) >= strtotime(date('Y-m-d'))) ? 'Active' : 'Expired';
        $sales_person = $_SESSION['username'];
        $sql = "INSERT INTO clients (id_number, family_name, given_name, birthday, contact_number, membership_availed, membership_rate, payment_mode, password, sales_person, activation_date, expiry_date, membership_status, entry_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssdsssssss", $client['id_number'], $client['family_name'], $client['given_name'], $client['birthday'], $client['contact_number'], $client['membership_availed'], $client['membership_rate'], $client['payment_mode'], $client['password'], $sales_person, $activation_date, $expiry_date, $membership_status, $client['entry_date']);
        if ($stmt->execute()) {
            $sql = "DELETE FROM pending_clients WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $reservation_id);
            $stmt->execute();
            $success = "Client approved successfully.";
        } else {
            $error = "Failed to approve client. Please try again.";
        }
    }
}

// Handle reject reservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reject_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $sql = "DELETE FROM pending_clients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
    if ($stmt->execute()) {
        $success = "Client reservation rejected successfully.";
    } else {
        $error = "Failed to reject reservation. Please try again.";
    }
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch total pending clients
$sql_total = "SELECT COUNT(*) as total FROM pending_clients WHERE membership_status = 'Pending'";
$total_result = $conn->query($sql_total);
$total_pending_clients = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_pending_clients / $records_per_page);

// Fetch pending clients
$sql = "SELECT * FROM pending_clients WHERE membership_status = 'Pending' LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $records_per_page, $offset);
$stmt->execute();
$pending_clients = $stmt->get_result();

function formatDate($date) {
    return $date ? date('F j, Y', strtotime($date)) : 'N/A';
}

$avatar_letter = strtoupper(substr($_SESSION['username'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Clients</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            box-sizing: border-box;
        }
        th.id-number, td.id-number { width: 150px; }
        th.family-name, td.family-name { width: 150px; }
        th.given-name, td.given-name { width: 150px; }
        th.birthday, td.birthday { width: 150px; }
        th.contact, td.contact { width: 150px; }
        th.membership, td.membership { width: 150px; }
        th.rate, td.rate { width: 120px; }
        th.payment, td.payment { width: 120px; }
        th.status, td.status { width: 120px; }
        th.action, td.action { width: 250px; }
    </style>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('hidden');
        }
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.querySelector('p').textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }
        <?php if (isset($success)) { ?>
            window.onload = function() { showToast('<?php echo htmlspecialchars($success); ?>'); };
        <?php } ?>
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
                        <p class="text-gray-600 text-sm">Front Desk</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Toast Notification -->
        <div id="toast" class="hidden fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <p>Operation successful</p>
        </div>

        <!-- Pending Clients Content -->
        <div class="container mx-auto max-w-7xl p-4 sm:p-8">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">Pending Clients</h2>
                <?php if (isset($error)) { ?>
                    <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
                <?php } ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-blue-50 sticky top-0">
                            <tr class="text-blue-800">
                                <th class="id-number border px-4 py-3 text-sm font-semibold text-center">ID Number</th>
                                <th class="family-name border px-4 py-3 text-sm font-semibold text-center">Family Name</th>
                                <th class="given-name border px-4 py-3 text-sm font-semibold text-center">Given Name</th>
                                <th class="birthday border px-4 py-3 text-sm font-semibold text-center">Birthday</th>
                                <th class="contact border px-4 py-3 text-sm font-semibold text-center">Contact</th>
                                <th class="membership border px-4 py-3 text-sm font-semibold text-center">Membership</th>
                                <th class="rate border px-4 py-3 text-sm font-semibold text-center">Rate</th>
                                <th class="payment border px-4 py-3 text-sm font-semibold text-center">Payment</th>
                                <th class="status border px-4 py-3 text-sm font-semibold text-center">Status</th>
                                <th class="action border px-4 py-3 text-sm font-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pending_clients->fetch_assoc()) { ?>
                                <tr class="odd:bg-gray-50 hover:bg-gray-100">
                                    <td class="id-number border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['id_number']); ?></td>
                                    <td class="family-name border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['family_name']); ?></td>
                                    <td class="given-name border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['given_name']); ?></td>
                                    <td class="birthday border px-4 py-3 text-gray-600 text-sm text-left"><?php echo formatDate($row['birthday']); ?></td>
                                    <td class="contact border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                    <td class="membership border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['membership_availed']); ?></td>
                                    <td class="rate border px-4 py-3 text-gray-600 text-sm text-left">₱<?php echo number_format($row['membership_rate'], 2); ?></td>
                                    <td class="payment border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['payment_mode']); ?></td>
                                    <td class="status border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['membership_status']); ?></td>
                                    <td class="action border px-4 py-3 text-gray-600 text-sm text-center">
                                        <form method="POST" action="" class="inline">
                                            <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                                            <input type="date" name="activation_date" class="border p-1 rounded-md mr-2" required>
                                            <button type="submit" name="approve_reservation" class="bg-green-500 text-white px-2 py-1 rounded-md hover:bg-green-600 mr-2">Approve</button>
                                        </form>
                                        <form method="POST" action="" class="inline">
                                            <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="reject_reservation" class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-600">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1) { ?>
                    <div class="mt-6 flex justify-center space-x-2">
                        <a href="?page=<?php echo $page - 1; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 <?php echo $page == 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>">Previous</a>
                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                            <a href="?page=<?php echo $i; ?>" class="px-4 py-2 rounded-lg <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition duration-300"><?php echo $i; ?></a>
                        <?php } ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 <?php echo $page == $total_pages ? 'opacity-50 cursor-not-allowed' : ''; ?>">Next</a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>