<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client') {
    header("Location: ../../index.php");
    exit();
}

$sql = "SELECT * FROM clients WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
$membership_status = (strtotime($client['expiry_date']) >= strtotime(date('Y-m-d'))) ? 'Active' : 'Expired';

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - FitZone Gym</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom animations and styles */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .hover-scale {
            transition: transform 0.3s ease;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        /* Status badge */
        .status-active {
            background-color: #10b981;
            color: white;
        }

        .status-expired {
            background-color: #ef4444;
            color: white;
        }

        /* Aligned data display */
        .data-row {
            display: flex;
            align-items: center;
        }

        .data-label {
            display: inline-block;
            width: 180px;
            font-weight: 600;
        }

        .data-value {
            display: inline-block;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 text-gray-800 transition-colors duration-300">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg sticky top-0 z-10">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold tracking-tight">FitZone Gym</h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm">Welcome, <?php echo htmlspecialchars($client['given_name']); ?>!</span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition-colors duration-200">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold mb-6 fade-in">Membership Details</h2>
        <div class="bg-white p-6 rounded-lg shadow-lg hover-scale fade-in">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="data-row mb-4">
                        <span class="data-label">ID Number:</span>
                        <span class="data-value"><?php echo htmlspecialchars($client['id_number']); ?></span>
                    </div>
                    <div class="data-row mb-4">
                        <span class="data-label">Name:</span>
                        <span class="data-value"><?php echo htmlspecialchars($client['family_name'] . ' ' . $client['given_name']); ?></span>
                    </div>
                    <div class="data-row mb-4">
                        <span class="data-label">Birthday:</span>
                        <span class="data-value"><?php echo formatDate($client['birthday']); ?></span>
                    </div>
                    <div class="data-row mb-4">
                        <span class="data-label">Contact Number:</span>
                        <span class="data-value"><?php echo htmlspecialchars($client['contact_number']); ?></span>
                    </div>
                    <div class="data-row mb-4">
                        <span class="data-label">Membership Availed:</span>
                        <span class="data-value"><?php echo htmlspecialchars($client['membership_availed']); ?></span>
                    </div>
                </div>
                <div>
                    <div class="data-row mb-4">
                        <span class="data-label">Membership Rate:</span>
                        <span class="data-value"><?php echo htmlspecialchars($client['membership_rate']); ?></span>
                    </div>
                    <div class="data-row mb-4">
                        <span class="data-label">Mode of Payment:</span>
                        <span class="data-value"><?php echo htmlspecialchars($client['payment_mode']); ?></span>
                    </div>
                    <div class="data-row mb-4">
                        <span class="data-label">Sales Person:</span>
                        <span class="data-value"><?php echo htmlspecialchars($client['sales_person']); ?></span>
                    </div>
                    <div class="data-row mb-4">
                        <span class="data-label">Activation Date:</span>
                        <span class="data-value"><?php echo formatDate($client['activation_date']); ?></span>
                    </div>
                    <div class="data-row mb-4">
                        <span class="data-label">Expiry Date:</span>
                        <span class="data-value"><?php echo formatDate($client['expiry_date']); ?></span>
                    </div>
                    <div class="data-row mb-4">
                        <span class="data-label">Membership Status:</span>
                        <span class="data-value inline-block px-3 py-1 rounded-full text-sm <?php echo $membership_status === 'Active' ? 'status-active' : 'status-expired'; ?>">
                            <?php echo htmlspecialchars($membership_status); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>