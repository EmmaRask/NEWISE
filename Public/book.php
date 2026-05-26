<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/bookings.php';


// ---------------------------
// Ladda .env (Centralbank credentials)
// ---------------------------
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$hotelOwner = $_ENV['CENTRALBANK_USER'] ?? '';
$hotelApiKey = $_ENV['CENTRALBANK_API_KEY'] ?? '';

// ---------------------------
// Ta emot POST-data
// ---------------------------
$room = $_POST['room'] ?? '';
$selectedDaysCsv = $_POST['selected_days'] ?? '';
$activities = array_filter($_POST['activities'] ?? [], fn($act) => $act !== '');
$guestName = $_POST['guest_name'] ?? '';
$transferCode = $_POST['transfer_code'] ?? '';

$selectedDays = array_map('intval', explode(',', $selectedDaysCsv));

// ---------------------------
// Room mapping + availability check
// ---------------------------
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
// Prisberäkning
// ---------------------------
$roomPrices = [
    'budget' => 3,
    'standard' => 6,
    'luxury' => 9,
];

$activityPrices = [
    'water:economy' => 2,
    'wheels:basic' => 5,
    'hotel-specific:economy' => 2,
    'hotel-specific:basic' => 5,
    'hotel-specific:premium' => 10,
    'hotel-specific:superior' => 17
    ];

$totalCost = ($roomPrices[$room] ?? 0) * count($selectedDays);

foreach ($activities as $act) {
    $totalCost += ($activityPrices[$act] ?? 0) * count($selectedDays);
}

// ---------------------------
// Validera transferCode
// ---------------------------
$transferData = [
    'transferCode' => $transferCode,
    'totalCost' => $totalCost,
];

$ch = curl_init('https://yrgopelag.se/centralbank/transferCode');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode($transferData),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

if (!isset($result['status']) || $result['status'] !== 'success') {
    die('Invalid transfer code');
}

// ---------------------------
// Skicka receipt
// ---------------------------
$arrivalDate = date('Y-m-d', strtotime(min($selectedDays) . ' January 2026'));
$departureDate = date('Y-m-d', strtotime(max($selectedDays) . ' January 2026 +1 day'));

$featuresUsed = [];
foreach ($activities as $act) {
    [$activity, $tier] = explode(':', $act);
    $featuresUsed[] = [
        'activity' => $activity,
        'tier' => $tier,
    ];
}

$receiptData = [
    'user' => $hotelOwner,
    'api_key' => $hotelApiKey,
    'guest_name' => $guestName,
    'arrival_date' => $arrivalDate,
    'departure_date' => $departureDate,
    'features_used' => $featuresUsed,
    'star_rating' => 5,
];

$ch = curl_init('https://www.yrgopelag.se/centralbank/receipt');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode($receiptData),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);

curl_exec($ch);
// Centralbanken kan returnera tom body vid success



// ---------------------------
// Deposit
// ---------------------------
// 

$depositData = [
    'user' => $hotelOwner,
    'api_key' => $hotelApiKey,
    'transferCode' => $transferCode,
];

$ch = curl_init('https://yrgopelag.se/centralbank/deposit');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($depositData),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
]);

$depositResponse = curl_exec($ch);
$depositHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    die('Deposit curl error: ' . curl_error($ch));
}

$depositResult = json_decode($depositResponse, true);

if (!isset($depositResult['status']) || $depositResult['status'] !== 'success') {
    echo '<pre>';
    echo 'DEPOSIT FAILED' . PHP_EOL;
    echo 'HTTP code: ';
    var_dump($depositHttpCode);
    echo 'Hotel owner from .env: ';
    var_dump($hotelOwner);
    echo 'Transfer code: ';
    var_dump($transferCode);
    echo 'Raw response:' . PHP_EOL;
    var_dump($depositResponse);
    echo 'Decoded response:' . PHP_EOL;
    var_dump($depositResult);
    echo '</pre>';
    exit;
}


// ---------------------------
// Spara bokningen i DB
// ---------------------------
$stmt = $pdo->prepare('SELECT id FROM guests WHERE name = :name');
$stmt->execute(['name' => $guestName]);
$guestId = $stmt->fetchColumn();

if (!$guestId) {
    $stmt = $pdo->prepare('INSERT INTO guests (name) VALUES (:name)');
    $stmt->execute(['name' => $guestName]);
    $guestId = (int) $pdo->lastInsertId();
}

$checkIn = sprintf('2026-01-%02d 15:00:00', min($selectedDays));
$checkOutDate = new DateTime(sprintf('2026-01-%02d 11:00:00', max($selectedDays)));
$checkOutDate->modify('+1 day');
$checkOut = $checkOutDate->format('Y-m-d H:i:s');

$stmt = $pdo->prepare(
    'INSERT INTO bookings (guest_id, room_id, check_in, check_out)
     VALUES (:guest_id, :room_id, :check_in, :check_out)'
);

$stmt->execute([
    'guest_id' => $guestId,
    'room_id' => $roomId,
    'check_in' => $checkIn,
    'check_out' => $checkOut,
]);

$bookingId = (int) $pdo->lastInsertId();

foreach ($activities as $act) {
    if ($act === '' || !str_contains($act, ':')) {
        continue;
    }

    [$activity, $tier] = explode(':', $act);

    $stmt = $pdo->prepare(
        'SELECT id FROM features WHERE category = :category AND tier = :tier'
    );
    $stmt->execute([
        'category' => $activity,
        'tier' => $tier,
    ]);

    $featureId = $stmt->fetchColumn();

    if ($featureId) {
        $stmt = $pdo->prepare(
            'INSERT INTO booking_features (booking_id, feature_id)
             VALUES (:booking_id, :feature_id)'
        );
        $stmt->execute([
            'booking_id' => $bookingId,
            'feature_id' => $featureId,
        ]);
    }
}

echo 'Booking completed successfully';
