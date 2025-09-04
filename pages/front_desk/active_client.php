<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'front_desk') {
    header("Location: ../../index.php");
    exit();
}

// Handle edit form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_client'])) {
    $client_id = $_POST['client_id'];
    $id_number = trim($_POST['id_number']);
    $family_name = trim($_POST['family_name']);
    $given_name = trim($_POST['given_name']);
    $birthday = $_POST['birthday'] ?: null;
    $contact_number = trim($_POST['contact_number']);
    $membership_availed = trim($_POST['membership_availed']);
    $membership_rate = floatval($_POST['membership_rate']);
    $payment_mode = trim($_POST['payment_mode']);
    $sales_person = trim($_POST['sales_person']);
    $activation_date = $_POST['activation_date'] ?: null;
    $expiry_date = $_POST['expiry_date'] ?: null;
    $membership_status = $_POST['membership_status'];

    // Validation
    if (empty($id_number) || empty($family_name) || empty($given_name) || empty($membership_availed) || $membership_rate <= 0 || empty($membership_status)) {
        $error = "Required fields cannot be empty or invalid.";
    } else {
        // Check id_number uniqueness
        $sql_check = "SELECT id FROM clients WHERE id_number = ? AND id != ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $id_number, $client_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = "ID Number must be unique.";
        } else {
            $sql_update = "UPDATE clients SET id_number = ?, family_name = ?, given_name = ?, birthday = ?, contact_number = ?, membership_availed = ?, membership_rate = ?, payment_mode = ?, sales_person = ?, activation_date = ?, expiry_date = ?, membership_status = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssssssdsssssi", $id_number, $family_name, $given_name, $birthday, $contact_number, $membership_availed, $membership_rate, $payment_mode, $sales_person, $activation_date, $expiry_date, $membership_status, $client_id);
            if ($stmt_update->execute()) {
                $success = "Client updated successfully.";
                header("Location: active_client.php?page=" . (isset($_GET['page']) ? $_GET['page'] : 1));
                exit();
            } else {
                $error = "Failed to update client: " . $conn->error;
            }
            $stmt_update->close();
        }
        $stmt_check->close();
    }
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch total active clients
$sql_total = "SELECT COUNT(*) as total FROM clients WHERE membership_status = 'Active'";
$total_result = $conn->query($sql_total);
$total_active_clients = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_active_clients / $records_per_page);

// Fetch active clients
$sql = "SELECT * FROM clients WHERE membership_status = 'Active' LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $records_per_page, $offset);
$stmt->execute();
$active_clients = $stmt->get_result();
$stmt->close();

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
    <title>Active Clients</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            box-sizing: border-box;
        }
        th.num, td.num { width: 60px; }
        th.id-number, td.id-number { width: 150px; }
        th.family-name, td.family-name { width: 150px; }
        th.given-name, td.given-name { width: 150px; }
        th.birthday, td.birthday { width: 150px; }
        th.contact, td.contact { width: 150px; }
        th.membership, td.membership { width: 150px; }
        th.rate, td.rate { width: 120px; }
        th.payment, td.payment { width: 120px; }
        th.sales-person, td.sales-person { width: 150px; }
        th.activation, td.activation { width: 150px; }
        th.expiry, td.expiry { width: 150px; }
        th.status, td.status { width: 120px; }
        th.action, td.action { width: 120px; }
    </style>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('hidden');
        }
        function openEditModal(id, id_number, family_name, given_name, birthday, contact_number, membership_availed, membership_rate, payment_mode, sales_person, activation_date, expiry_date, membership_status) {
            document.getElementById('edit_client_id').value = id;
            document.getElementById('edit_id_number').value = id_number;
            document.getElementById('edit_family_name').value = family_name;
            document.getElementById('edit_given_name').value = given_name;
            document.getElementById('edit_birthday').value = birthday || '';
            document.getElementById('edit_contact_number').value = contact_number;
            document.getElementById('edit_membership_availed').value = membership_availed;
            document.getElementById('edit_membership_rate').value = membership_availed === '1 month' ? '1200' : '6000';
            document.getElementById('edit_payment_mode').value = payment_mode;
            document.getElementById('edit_sales_person').value = sales_person;
            document.getElementById('edit_activation_date').value = activation_date || '';
            document.getElementById('edit_expiry_date').value = expiry_date || '';
            document.getElementById('edit_membership_status').value = membership_status;
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        function updateMembershipRate() {
            const membershipAvailed = document.getElementById('edit_membership_availed').value;
            const rateField = document.getElementById('edit_membership_rate');
            rateField.value = membershipAvailed === '1 month' ? '1200' : '6000';
        }
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.querySelector('p').textContent = message;
            toast.classList.remove('hidden', 'bg-green-600', 'bg-red-600');
            toast.classList.add(isError ? 'bg-red-600' : 'bg-green-600');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }
        <?php if (isset($success)) { ?>
            window.onload = function() { showToast('<?php echo htmlspecialchars($success); ?>'); };
        <?php } elseif (isset($error)) { ?>
            window.onload = function() { showToast('<?php echo htmlspecialchars($error); ?>', true); };
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
            <a href="./register_client.php" class="text-blue-500 hover:bg-blue-100 px-4 py-2 rounded-lg font-medium">Register Client</a>
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
        <div id="toast" class="hidden fixed top-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <p>Operation successful</p>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50" onclick="closeEditModal()">
            <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-4xl" onclick="event.stopPropagation()">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Edit Client</h2>
                <form method="POST" action="">
                    <input type="hidden" name="edit_client" value="1">
                    <input type="hidden" id="edit_client_id" name="client_id">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ID Number</label>
                            <input type="text" id="edit_id_number" name="id_number" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Family Name</label>
                            <input type="text" id="edit_family_name" name="family_name" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Given Name</label>
                            <input type="text" id="edit_given_name" name="given_name" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Birthday</label>
                            <input type="date" id="edit_birthday" name="birthday" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="text" id="edit_contact_number" name="contact_number" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Membership Availed</label>
                            <select id="edit_membership_availed" name="membership_availed" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required onchange="updateMembershipRate()">
                                <option value="1 month">1 month</option>
                                <option value="6 months">6 months</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Membership Rate</label>
                            <input type="number" id="edit_membership_rate" name="membership_rate" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Mode</label>
                            <select id="edit_payment_mode" name="payment_mode" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                <option value="GCash">GCash</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sales Person</label>
                            <input type="text" id="edit_sales_person" name="sales_person" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Activation Date</label>
                            <input type="date" id="edit_activation_date" name="activation_date" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                            <input type="date" id="edit_expiry_date" name="expiry_date" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Membership Status</label>
                            <select id="edit_membership_status" name="membership_status" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                <option value="Active">Active</option>
                                <option value="Expired">Expired</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-300">Save</button>
                        <button type="button" onclick="closeEditModal()" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition duration-300">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Active Clients Content -->
        <div class="container mx-auto max-w-7xl p-4 sm:p-8">
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">Active Clients</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-blue-50 sticky top-0">
                            <tr class="text-blue-800">
                                <th class="num border px-4 py-3 text-sm font-semibold text-center">#</th>
                                <th class="id-number border px-4 py-3 text-sm font-semibold text-center">ID Number</th>
                                <th class="family-name border px-4 py-3 text-sm font-semibold text-center">Family Name</th>
                                <th class="given-name border px-4 py-3 text-sm font-semibold text-center">Given Name</th>
                                <th class="birthday border px-4 py-3 text-sm font-semibold text-center">Birthday</th>
                                <th class="contact border px-4 py-3 text-sm font-semibold text-center">Contact</th>
                                <th class="membership border px-4 py-3 text-sm font-semibold text-center">Membership</th>
                                <th class="rate border px-4 py-3 text-sm font-semibold text-center">Rate</th>
                                <th class="payment border px-4 py-3 text-sm font-semibold text-center">Payment</th>
                                <th class="sales-person border px-4 py-3 text-sm font-semibold text-center">Sales Person</th>
                                <th class="activation border px-4 py-3 text-sm font-semibold text-center">Activation</th>
                                <th class="expiry border px-4 py-3 text-sm font-semibold text-center">Expiry</th>
                                <th class="status border px-4 py-3 text-sm font-semibold text-center">Status</th>
                                <th class="action border px-4 py-3 text-sm font-semibold text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($active_clients->num_rows == 0) {
                                echo "<tr><td colspan='14' class='border px-4 py-3 text-gray-600 text-sm text-center'>No active clients found.</td></tr>";
                            } else {
                                $row_number = $offset + 1;
                                while ($row = $active_clients->fetch_assoc()) {
                                    ?>
                                    <tr class="odd:bg-gray-50 hover:bg-gray-100">
                                        <td class="num border px-4 py-3 text-gray-600 text-sm text-center"><?php echo $row_number++; ?></td>
                                        <td class="id-number border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['id_number']); ?></td>
                                        <td class="family-name border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['family_name']); ?></td>
                                        <td class="given-name border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['given_name']); ?></td>
                                        <td class="birthday border px-4 py-3 text-gray-600 text-sm text-left"><?php echo formatDate($row['birthday']); ?></td>
                                        <td class="contact border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                        <td class="membership border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['membership_availed']); ?></td>
                                        <td class="rate border px-4 py-3 text-gray-600 text-sm text-left">₱<?php echo number_format($row['membership_rate'], 2); ?></td>
                                        <td class="payment border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['payment_mode']); ?></td>
                                        <td class="sales-person border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['sales_person']); ?></td>
                                        <td class="activation border px-4 py-3 text-gray-600 text-sm text-left"><?php echo formatDate($row['activation_date']); ?></td>
                                        <td class="expiry border px-4 py-3 text-gray-600 text-sm text-left"><?php echo formatDate($row['expiry_date']); ?></td>
                                        <td class="status border px-4 py-3 text-gray-600 text-sm text-left"><?php echo htmlspecialchars($row['membership_status']); ?></td>
                                        <td class="action border px-4 py-3 text-gray-600 text-sm text-center">
                                            <button onclick="openEditModal(
                                                '<?php echo $row['id']; ?>',
                                                '<?php echo htmlspecialchars($row['id_number'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['family_name'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['given_name'], ENT_QUOTES); ?>',
                                                '<?php echo $row['birthday'] ? htmlspecialchars($row['birthday']) : ''; ?>',
                                                '<?php echo htmlspecialchars($row['contact_number'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['membership_availed'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['membership_rate']); ?>',
                                                '<?php echo htmlspecialchars($row['payment_mode'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($row['sales_person'], ENT_QUOTES); ?>',
                                                '<?php echo $row['activation_date'] ? htmlspecialchars($row['activation_date']) : ''; ?>',
                                                '<?php echo $row['expiry_date'] ? htmlspecialchars($row['expiry_date']) : ''; ?>',
                                                '<?php echo htmlspecialchars($row['membership_status'], ENT_QUOTES); ?>'
                                            )" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Edit</button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
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