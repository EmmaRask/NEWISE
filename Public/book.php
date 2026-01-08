<?php
declare(strict_types=1);

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/bookings.php';

// ---------------------------
// 1️⃣ Ladda .env (API-nyckel & användarnamn)
// ---------------------------
require_once __DIR__ . '/../vendor/autoload.php'; // Om du använder composer + vlucas/phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Hämta hotel owner info från .env
$hotelOwner = $_ENV['CENTRALBANK_USER'] ?? 'defaultUser';
$hotelApiKey = $_ENV['CENTRALBANK_API_KEY'] ?? 'defaultApiKey';

// ---------------------------
// 2️⃣ Ta emot POST-data från formuläret
// ---------------------------
$room = $_POST['room'] ?? '';
$selectedDaysCsv = $_POST['selected_days'] ?? '';
$activities = $_POST['activities'] ?? [];
$guestName = $_POST['guest_name'] ?? '';
$transferCode = $_POST['transfer_code'] ?? '';

// Konvertera CSV till array med datum
$selectedDays = array_map('intval', explode(',', $selectedDaysCsv));

$roomMap = [
    'budget' => 1,
    'standard' => 2,
    'luxury' => 3,
];

$roomId = $roomMap[$room] ?? null;

if (!$roomId) {
    die('Invalid room selected');
}

$alreadyBookedDays = getBookedDaysForRoom($pdo, $roomId);

foreach ($selectedDays as $day) {
    if (in_array($day, $alreadyBookedDays, true)) {
        die('Room is not available for one or more selected days');
    }
}


// ---------------------------
// 3️⃣ Beräkna total kostnad (exempel)
// ---------------------------
$roomPrices = [
    'standard' => 7,
    'superior' => 10,
];
$activityPrices = [
    'portal-travel:premium' => 10,
    'portal-travel:basic' => 5,
    // lägg till fler aktiviteter här
];

$totalCost = ($roomPrices[$room] ?? 0) * count($selectedDays);

foreach ($activities as $act) {
    $totalCost += ($activityPrices[$act] ?? 0) * count($selectedDays);
}

echo "Total cost: $totalCost\n";

// ---------------------------
// 4️⃣ Validera transferCode mot centralbanken
// ---------------------------
$transferData = [
    "transferCode" => $transferCode,
    "totalCost" => $totalCost
];

$ch = curl_init('https://www.yrgopelag.se/centralbank/transferCode');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($transferData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);


$response = curl_exec($ch);
// curl_close($ch); // ❌ PHP 8.5+, inte nödvändigt
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "\nHTTP code: $httpCode\n";


$result = json_decode($response, true);

echo "\n--- TransferCode response ---\n";
var_dump($response);
var_dump($result);

if (isset($result['status']) && $result['status'] === 'success') {
    echo "Transfercode validated successfully!\n";

    // ---------------------------
    // 5️⃣ Skapa receipt
    // ---------------------------
    $arrivalDate = date('Y-m-d', strtotime($selectedDays[0] . ' January 2026'));
    $departureDate = date('Y-m-d', strtotime(end($selectedDays) . ' January 2026 +1 day'));

    $featuresUsed = [];
    foreach ($activities as $act) {
        [$activity, $tier] = explode(':', $act);
        $featuresUsed[] = [
            "activity" => $activity,
            "tier" => $tier
        ];
    }

    $receiptData = [
        "user" => $hotelOwner,
        "api_key" => $hotelApiKey,
        "guest_name" => $guestName,
        "arrival_date" => $arrivalDate,
        "departure_date" => $departureDate,
        "features_used" => $featuresUsed,
        "star_rating" => 5
    ];

    $ch = curl_init('https://www.yrgopelag.se/centralbank/receipt');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($receiptData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $receiptResponse = curl_exec($ch);
    // curl_close($ch);

    if ($receiptResponse === false || $receiptResponse === '') {
    echo "Receipt sent successfully (no response body).\n";
} else {
    $receiptResult = json_decode($receiptResponse, true);
    var_dump($receiptResult);
}


    echo "\n--- Receipt raw response ---\n";
    var_dump($receiptResponse);

    $receiptResult = json_decode($receiptResponse, true);
    echo "\n--- Receipt decoded response ---\n";
    var_dump($receiptResult);

} else {
    echo "Error validating transfer code:\n";
    var_dump($result);
}
