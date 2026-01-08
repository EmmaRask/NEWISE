<?php

declare(strict_types=1);





/**
* @param string $title
* @param array<string, array<int>> $roomBookings
*/   


function renderCalendar(string $title, array $bookings, string $type): void
{
?>
<div class="calendar" data-type="<?= htmlspecialchars($type) ?>">
    <h2><?= htmlspecialchars($title) ?></h2>
    <p class="month-label">January 2026</p>

    <div class="calendar-grid">
        <?php for ($day = 1; $day <= 31; $day++): ?>
            <div class="day"
                <?php foreach ($bookings as $key => $days): ?>
                    data-<?= htmlspecialchars($key) ?>="<?= in_array($day, $days, true) ? 'booked' : 'free' ?>"
                <?php endforeach; ?>
            >
                <?= $day ?>
            </div>
        <?php endfor; ?>
    </div>
</div>
<?php
}

function getActivityBookings(array $featureGrid): array {
    $bookings = [];

    foreach ($featureGrid as $category => $tiers) {
        foreach ($tiers as $tier => $name) {
            // Dummy-data för bokade dagar, kan bytas mot DB senare
            $bookings[$category . ':' . $tier] = [1, 5, 10]; 
        }
    }

    return $bookings;
}

require_once __DIR__ . '/db/bookings.php';

function getBookings(string $type, array $featureGrid = []): array
{
    global $pdo;

    switch ($type) {
        case 'room':
            return [
                'budget'   => getBookedDaysForRoom($pdo, 1),
                'standard' => getBookedDaysForRoom($pdo, 2),
                'luxury'   => getBookedDaysForRoom($pdo, 3),
            ];
        case 'activity':
            // Använd $featureGrid för att generera bokningar
            return getActivityBookings($featureGrid);
        default:
            return [];
    }
}
?>

