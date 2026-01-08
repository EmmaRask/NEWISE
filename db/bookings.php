<?php
declare(strict_types=1);

// Inkludera PDO-anslutningen
require_once __DIR__ . '/connection.php';

/**
 * Hämta bokade dagar för ett rum
 */
function getBookedDaysForRoom(PDO $pdo, int $roomId): array
{
    $stmt = $pdo->prepare(
        'SELECT check_in, check_out FROM bookings WHERE room_id = :room_id'
    );
    $stmt->execute(['room_id' => $roomId]);

    $days = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $booking) {
        $start = new DateTime($booking['check_in']);
        $end   = new DateTime($booking['check_out']);

        while ($start < $end) {
            $days[] = (int)$start->format('j');
            $start->modify('+1 day');
        }
    }

    return array_values(array_unique($days));
}
