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

function getBookings(string $type, array $featureGrid = []): array {
    switch($type) {
        case 'room':
            return [
                'budget' => [2,5,12],
                'standard' => [8,9,18],
                'luxury' => [15,16,17]
            ];
        case 'activity':
            // Använd $featureGrid för att generera bokningar
            return getActivityBookings($featureGrid);
        case 'offer':
            return [
                'offer' => [6,13,20]
            ];
        default:
            return [];
    }
}
?>

