<?php
declare(strict_types=1);

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/bookings.php';

// ---------------------------
// Ladda .env (API-nyckel & användarnamn)
// ---------------------------
require_once __DIR__ . '/../vendor/autoload.php'; // Om du använder composer + vlucas/phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Hämta hotel owner info från .env
$hotelOwner = $_ENV['CENTRALBANK_USER'] ?? 'defaultUser';
$hotelApiKey = $_ENV['CENTRALBANK_API_KEY'] ?? 'defaultApiKey';

// ---------------------------
// Ta emot POST-data från formuläret 
// ---------------------------
$room = $_POST['room'] ?? '';
$selectedDaysCsv = $_POST['selected_days'] ?? '';
$activities = $_POST['activities'] ?? [];
$guestName = $_POST['guest_name'] ?? '';
$transferCode = $_POST['transfer_code'] ?? '';

// Konvertera CSV -> array med datum
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
// Beräkna total kostnad 
// ---------------------------
$roomPrices = [
    'budget' => 3,
    'standard' => 6,
    'superior' => 9,
];
$activityPrices = [
    'water:economy' => 2,
    'wheels:basic' => 5, 
    'portal-travel:economy' => 2,
    'portal-travel:basic' => 5,
    'portal-travel:premium' => 10,
    'portal-travel:superior' => 17,
    
];

$totalCost = ($roomPrices[$room] ?? 0) * count($selectedDays);

foreach ($activities as $act) {
    $totalCost += ($activityPrices[$act] ?? 0) * count($selectedDays);
}

echo "Total cost: $totalCost\n";

// ---------------------------
// Validera transferCode mot centralbanken
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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "\nHTTP code: $httpCode\n";


$result = json_decode($response, true);

echo "\n--- TransferCode response ---\n";
var_dump($response);
var_dump($result);

if (isset($result['status']) && $result['status'] === 'success') {
    echo "Transfercode validated successfully!\n";

    // ---------------------------
    //  Skapa receipt
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

// ---------------------------
// Deposit
// ---------------------------
$depositData = [
    'user' => $hotelOwner,
    'transferCode' => $transferCode,
];

$ch = curl_init('https://www.yrgopelag.se/centralbank/deposit');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($depositData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$depositResponse = curl_exec($ch);
curl_close($ch);

$depositResult = json_decode($depositResponse, true);

if (!isset($depositResult['status']) || $depositResult['status'] !== 'success') {
    die('Deposit failed');
}

// ---------------------------
//  Spara bokningen i DB
// ---------------------------

// Hämta eller skapa guest
$stmt = $pdo->prepare('SELECT id FROM guests WHERE name = :name');
$stmt->execute(['name' => $guestName]);
$guestId = $stmt->fetchColumn();

if (!$guestId) {
    $stmt = $pdo->prepare('INSERT INTO guests (name) VALUES (:name)');
    $stmt->execute(['name' => $guestName]);
    $guestId = (int)$pdo->lastInsertId();
}
$checkInDate = sprintf('2026-01-%02d 15:00:00', min($selectedDays));
$checkOutDate = sprintf(
    '2026-01-%02d 11:00:00',
    max($selectedDays) + 1
);

$stmt = $pdo->prepare('
    INSERT INTO bookings (guest_id, room_id, check_in, check_out)
    VALUES (:guest_id, :room_id, :check_in, :check_out)
');

$stmt->execute([
    'guest_id' => $guestId,
    'room_id' => $roomId,
    'check_in' => $checkInDate,
    'check_out' => $checkOutDate,
]);

$bookingId = (int)$pdo->lastInsertId();

echo "Booking saved with ID: $bookingId<br>";