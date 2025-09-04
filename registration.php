<?php
require_once 'config.php';

function generateIDNumber($conn) {
    $year = date('y');
    $sql = "SELECT COUNT(*) as count FROM clients WHERE id_number LIKE '%-$year'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1;
    return sprintf("%06d-%s", $count, $year);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $family_name = $_POST['family_name'];
    $given_name = $_POST['given_name'];
    $birthday = $_POST['birthday']; // Already in Y-m-d format from date input
    $contact_number = $_POST['contact_number'];
    $membership_availed = $_POST['membership_availed'];
    $membership_rate = $membership_availed === '1 month' ? 1200.00 : 6000.00;
    $payment_mode = $_POST['payment_mode'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $register_error = "Passwords do not match. Please try again.";
    } else {
        $id_number = generateIDNumber($conn);
        $password_hashed = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO pending_clients (id_number, family_name, given_name, birthday, contact_number, membership_availed, membership_rate, payment_mode, password, membership_status, entry_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssdss", $id_number, $family_name, $given_name, $birthday, $contact_number, $membership_availed, $membership_rate, $payment_mode, $password_hashed);
        if ($stmt->execute()) {
            $register_success = true;
            $generated_id = $id_number;
            $raw_password = $password;
        } else {
            $register_error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-green-100 min-h-screen flex items-center justify-center p-4 sm:p-8">
    <div class="container mx-auto max-w-3xl">
        <?php if (isset($register_success) && $register_success) { ?>
            <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-4 text-green-600">Registration Successful!</h2>
                <p class="text-lg mb-2">Your ID Number for login is: <strong class="text-blue-600"><?php echo htmlspecialchars($generated_id); ?></strong></p>
                <p class="text-lg mb-4">Your Password is: <strong class="text-blue-600"><?php echo htmlspecialchars($raw_password); ?></strong></p>
                <p class="text-gray-600">Awaiting front desk approval.</p>
                <div class="mt-6 flex justify-center gap-4">
                    <a href="index.php" class="bg-blue-500 text-white px-6 py-2 rounded-full hover:bg-blue-600 transition duration-300">Go to Login</a>
                    <a href="registration.php" class="bg-gray-500 text-white px-6 py-2 rounded-full hover:bg-gray-600 transition duration-300">Register Another Client</a>
                </div>
            </div>
        <?php } else { ?>
            <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
                <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Client Registration</h2>
                <?php if (isset($register_error)) { ?>
                    <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($register_error); ?></p>
                <?php } ?>
                <form method="POST" action="" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="family_name" class="block text-sm font-medium text-gray-700">Family Name</label>
                            <input type="text" name="family_name" id="family_name" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Enter family name" required>
                        </div>
                        <div>
                            <label for="given_name" class="block text-sm font-medium text-gray-700">Given Name</label>
                            <input type="text" name="given_name" id="given_name" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Enter given name" required>
                        </div>
                        <div>
                            <label for="birthday" class="block text-sm font-medium text-gray-700">Birthday</label>
                            <input type="date" name="birthday" id="birthday" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" required>
                        </div>
                        <div>
                            <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="text" name="contact_number" id="contact_number" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Enter contact number">
                        </div>
                        <div>
                            <label for="membership_availed" class="block text-sm font-medium text-gray-700">Membership Availed</label>
                            <select name="membership_availed" id="membership_availed" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" required>
                                <option value="" disabled selected>Select membership</option>
                                <option value="1 month">1 Month (₱1,200)</option>
                                <option value="6 months">6 Months (₱6,000)</option>
                            </select>
                        </div>
                        <div>
                            <label for="payment_mode" class="block text-sm font-medium text-gray-700">Mode of Payment</label>
                            <select name="payment_mode" id="payment_mode" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                <option value="" disabled selected>Select payment mode</option>
                                <option value="gcash">G-Cash</option>
                                <option value="cash">Cash</option>
                            </select>
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
                    <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">Submit Reservation</button>
                </form>
                <p class="mt-4 text-center text-gray-600">Already registered? <a href="index.php" class="text-blue-500 hover:underline">Login here</a></p>
            </div>
        <?php } ?>
    </div>
</body>
</html>