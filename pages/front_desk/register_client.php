<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'front_desk') {
    header("Location: ../../index.php");
    exit();
}

// Handle single client registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_client'])) {
    $id_number = trim($_POST['id_number']);
    $family_name = trim($_POST['family_name']);
    $given_name = trim($_POST['given_name']);
    $birthday = $_POST['birthday'] ?: null;
    $contact_number = trim($_POST['contact_number']);
    $membership_availed = trim($_POST['membership_availed']);
    $membership_rate = floatval($_POST['membership_rate']);
    $payment_mode = trim($_POST['payment_mode']);
    $password = $_POST['password'];
    $sales_person = trim($_POST['sales_person']);
    $activation_date = $_POST['activation_date'] ?: null;
    $expiry_date = $_POST['expiry_date'] ?: null;
    $membership_status = $_POST['membership_status'];
    $entry_date = date('Y-m-d');

    // Validation
    if (empty($id_number) || empty($family_name) || empty($given_name) || empty($membership_availed) || $membership_rate <= 0 || empty($password) || empty($membership_status)) {
        $error = "Required fields cannot be empty or invalid.";
    } else {
        // Check id_number uniqueness
        $sql_check = "SELECT id FROM clients WHERE id_number = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $id_number);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = "ID Number already exists.";
        } else {
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $sql_insert = "INSERT INTO clients (id_number, family_name, given_name, birthday, contact_number, membership_availed, membership_rate, payment_mode, password, sales_person, activation_date, expiry_date, membership_status, entry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssssssdsssssss", $id_number, $family_name, $given_name, $birthday, $contact_number, $membership_availed, $membership_rate, $payment_mode, $password_hashed, $sales_person, $activation_date, $expiry_date, $membership_status, $entry_date);
            if ($stmt_insert->execute()) {
                $success = "Client registered successfully.";
            } else {
                $error = "Failed to register client: " . $conn->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

// Handle CSV upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $csv_file = $_FILES['csv_file']['tmp_name'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        $allowed_types = ['text/csv', 'application/csv', 'text/plain'];

        // Validate file
        if ($_FILES['csv_file']['size'] > $max_file_size) {
            $error = "File size exceeds 5MB limit.";
        } elseif (!in_array($_FILES['csv_file']['type'], $allowed_types)) {
            $error = "Invalid file type. Please upload a CSV file.";
        } else {
            $file = fopen($csv_file, 'r');
            $headers = fgetcsv($file); // Read headers
            $expected_headers = ['id_number', 'family_name', 'given_name', 'birthday', 'contact_number', 'membership_availed', 'membership_rate', 'payment_mode', 'password', 'sales_person', 'activation_date', 'expiry_date', 'membership_status'];
            if ($headers !== $expected_headers) {
                $error = "Invalid CSV format. Expected headers: " . implode(', ', $expected_headers);
            } else {
                $success_count = 0;
                $error_messages = [];
                $row_number = 1;

                $sql_check = "SELECT id_number FROM clients";
                $result = $conn->query($sql_check);
                $existing_ids = [];
                while ($row = $result->fetch_assoc()) {
                    $existing_ids[] = $row['id_number'];
                }

                $sql_insert = "INSERT INTO clients (id_number, family_name, given_name, birthday, contact_number, membership_availed, membership_rate, payment_mode, password, sales_person, activation_date, expiry_date, membership_status, entry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);

                while (($row = fgetcsv($file)) !== false) {
                    $row_number++;
                    if (count($row) != count($expected_headers)) {
                        $error_messages[] = "Row $row_number: Invalid number of columns.";
                        continue;
                    }

                    $data = array_combine($expected_headers, $row);
                    $id_number = trim($data['id_number']);
                    $family_name = trim($data['family_name']);
                    $given_name = trim($data['given_name']);
                    $birthday = !empty($data['birthday']) ? $data['birthday'] : null;
                    $contact_number = trim($data['contact_number']);
                    $membership_availed = trim($data['membership_availed']);
                    $membership_rate = floatval($data['membership_rate']);
                    $payment_mode = trim($data['payment_mode']);
                    $password = $data['password'];
                    $sales_person = trim($data['sales_person']);
                    $activation_date = !empty($data['activation_date']) ? $data['activation_date'] : null;
                    $expiry_date = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
                    $membership_status = trim($data['membership_status']);
                    $entry_date = date('Y-m-d');

                    // Validation
                    if (empty($id_number) || empty($family_name) || empty($given_name) || empty($membership_availed) || $membership_rate <= 0 || empty($password) || empty($membership_status)) {
                        $error_messages[] = "Row $row_number: Required fields cannot be empty or invalid.";
                        continue;
                    }

                    if (in_array($id_number, $existing_ids)) {
                        $error_messages[] = "Row $row_number: ID Number '$id_number' already exists.";
                        continue;
                    }

                    if (!in_array($membership_availed, ['1 month', '6 months'])) {
                        $error_messages[] = "Row $row_number: Invalid membership_availed. Must be '1 month' or '6 months'.";
                        continue;
                    }

                    if (($membership_availed == '1 month' && $membership_rate != 1200) || ($membership_availed == '6 months' && $membership_rate != 6000)) {
                        $error_messages[] = "Row $row_number: Invalid membership_rate for '$membership_availed'. Must be 1200 for '1 month' or 6000 for '6 months'.";
                        continue;
                    }

                    if (!empty($payment_mode) && !in_array($payment_mode, ['GCash', 'Cash'])) {
                        $error_messages[] = "Row $row_number: Invalid payment_mode. Must be 'GCash', 'Cash', or empty.";
                        continue;
                    }

                    if (!in_array($membership_status, ['Active', 'Expired'])) {
                        $error_messages[] = "Row $row_number: Invalid membership_status. Must be 'Active' or 'Expired'.";
                        continue;
                    }

                    if (!empty($birthday) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
                        $error_messages[] = "Row $row_number: Invalid birthday format. Must be YYYY-MM-DD or empty.";
                        continue;
                    }

                    if (!empty($activation_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $activation_date)) {
                        $error_messages[] = "Row $row_number: Invalid activation_date format. Must be YYYY-MM-DD or empty.";
                        continue;
                    }

                    if (!empty($expiry_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry_date)) {
                        $error_messages[] = "Row $row_number: Invalid expiry_date format. Must be YYYY-MM-DD or empty.";
                        continue;
                    }

                    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt_insert->bind_param("ssssssdsssssss", $id_number, $family_name, $given_name, $birthday, $contact_number, $membership_availed, $membership_rate, $payment_mode, $password_hashed, $sales_person, $activation_date, $expiry_date, $membership_status, $entry_date);
                    if ($stmt_insert->execute()) {
                        $success_count++;
                        $existing_ids[] = $id_number; // Update existing IDs to prevent duplicates in the same upload
                    } else {
                        $error_messages[] = "Row $row_number: Failed to insert client: " . $conn->error;
                    }
                }

                $stmt_insert->close();
                fclose($file);

                if ($success_count > 0) {
                    $success = "$success_count client(s) registered successfully.";
                    if (!empty($error_messages)) {
                        $error = implode("<br>", $error_messages);
                    }
                } else {
                    $error = empty($error_messages) ? "No valid clients found in CSV." : implode("<br>", $error_messages);
                }
            }
        }
    } else {
        $error = "No file uploaded or upload error occurred.";
    }
}

$avatar_letter = strtoupper(substr($_SESSION['username'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .form-container {
            max-width: 800px;
            margin: auto;
        }
    </style>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('hidden');
        }
        function openCsvModal() {
            document.getElementById('csvModal').classList.remove('hidden');
        }
        function closeCsvModal() {
            document.getElementById('csvModal').classList.add('hidden');
        }
        function updateMembershipRate() {
            const membershipAvailed = document.getElementById('membership_availed').value;
            const rateField = document.getElementById('membership_rate');
            rateField.value = membershipAvailed === '1 month' ? '1200' : '6000';
        }
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.querySelector('p').textContent = message;
            toast.classList.remove('hidden', 'bg-green-600', 'bg-red-600');
            toast.classList.add(isError ? 'bg-red-600' : 'bg-green-600');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 5000);
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

        <!-- CSV Upload Modal -->
        <div id="csvModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50" onclick="closeCsvModal()">
            <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Upload CSV</h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="upload_csv" value="1">
                    <div class="mb-4">
                        <label for="csv_file" class="block text-sm font-medium text-gray-700">Select CSV File</label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        <p class="mt-2 text-sm text-gray-600">Expected headers: id_number, family_name, given_name, birthday, contact_number, membership_availed, membership_rate, payment_mode, password, sales_person, activation_date, expiry_date, membership_status</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-300">Upload</button>
                        <button type="button" onclick="closeCsvModal()" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition duration-300">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Register Client Content -->
        <div class="container mx-auto max-w-7xl p-4 sm:p-8">
            <div class="bg-white p-8 rounded-xl shadow-lg form-container">
                <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Register Client</h2>
                <div class="flex justify-end mb-4">
                    <button onclick="openCsvModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Register via CSV</button>
                </div>
                <?php if (!isset($success)) { ?>
                    <?php if (isset($error)) { ?>
                        <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
                    <?php } ?>
                    <form method="POST" action="" class="space-y-6">
                        <input type="hidden" name="register_client" value="1">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <label for="id_number" class="block text-sm font-medium text-gray-700">ID Number</label>
                                <input type="text" id="id_number" name="id_number" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label for="family_name" class="block text-sm font-medium text-gray-700">Family Name</label>
                                <input type="text" id="family_name" name="family_name" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label for="given_name" class="block text-sm font-medium text-gray-700">Given Name</label>
                                <input type="text" id="given_name" name="given_name" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label for="birthday" class="block text-sm font-medium text-gray-700">Birthday</label>
                                <input type="date" id="birthday" name="birthday" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                                <input type="text" id="contact_number" name="contact_number" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="membership_availed" class="block text-sm font-medium text-gray-700">Membership Availed</label>
                                <select id="membership_availed" name="membership_availed" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required onchange="updateMembershipRate()">
                                    <option value="1 month">1 month</option>
                                    <option value="6 months">6 months</option>
                                </select>
                            </div>
                            <div>
                                <label for="membership_rate" class="block text-sm font-medium text-gray-700">Membership Rate</label>
                                <input type="number" id="membership_rate" name="membership_rate" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg bg-gray-100" readonly required>
                            </div>
                            <div>
                                <label for="payment_mode" class="block text-sm font-medium text-gray-700">Payment Mode</label>
                                <select id="payment_mode" name="payment_mode" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Payment Mode</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Cash">Cash</option>
                                </select>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" id="password" name="password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label for="sales_person" class="block text-sm font-medium text-gray-700">Sales Person</label>
                                <input type="text" id="sales_person" name="sales_person" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="activation_date" class="block text-sm font-medium text-gray-700">Activation Date</label>
                                <input type="date" id="activation_date" name="activation_date" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                <input type="date" id="expiry_date" name="expiry_date" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="membership_status" class="block text-sm font-medium text-gray-700">Membership Status</label>
                                <select id="membership_status" name="membership_status" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    <option value="Active">Active</option>
                                    <option value="Expired">Expired</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">Register Client</button>
                    </form>
                <?php } else { ?>
                    <div class="text-center">
                        <p class="text-green-600 text-lg mb-4"><?php echo htmlspecialchars($success); ?></p>
                        <div class="flex justify-center space-x-4">
                            <a href="./register_client.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Register Another Client</a>
                            <a href="./dashboard.php" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition duration-300">Back to Dashboard</a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>