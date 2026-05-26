<?php

ini_set('display_errors', '1');

require_once __DIR__ . "/calendar.php";
    $roomBookings = [];
    $activityBookings = [];
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../db/bookings.php';



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yrgopelag</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="calendar.css">

</head>
<body>
    
<main>

    <section class="legend">
        <h1>EverShift
            <img class="inline-icon" src="nav/icons/pngwing.com (1).png" alt="nav-icon">
        </h1>
    </section>



<section class="hero-fullwidth fullpage-section">
    <section class="hero split-section">

            <div class="hero-carousel">
                <img src="Images/heroCarousel/050BD195-2214-450D-98AF-24E36F076C5B_1_105_c.jpeg" alt="EverShift">
            </div>   

            <aside class="hero-info">
                <h1>Wake up to a new horizon, every day</h1>
                <p>
                    Image you could travel not only between in style and comfort but also exoperince all 
                    four climates while doing so! Here at the Evershift we bring that very possibility to life.

                    Fall asleep to the fire crackling to kkep the chill away in a winter wonferland and wake up to cherry 
                    blossoms and spring just hours later without uncomfortable flights that take time from your vacation. 
                    The island of NEWISLE is a magical place andf home to the wandering hotel EverShift, a moving hotel 
                    that takes you on an unforgettable yourney across the realm of snow, ice, sun, rain and soaring blue skies. 
        
                    Our owner and powersource Reficlac can almost always be meet in the library. Their wishgranting time may be over,
                    But you never know what they may surprise you with!  Welcome aboard and enyoy the most spectacular place Yrgepelag can offer! 
                </p>
            </aside>
        </section>
    </section>
</section>
    
    <section class="offers fullpage-section split-section">
        <aside class="offer-info">
        <h2>Rooms and Activities</h2>
        <p>
            Traveling light? We offer rooms for budget traveling vagabonds, standard room if you want to up your 
            comfortability as well as a full luxuray stay for the one who know to enjoy their travels in style and comfort. 
            
            To make your stay at the EverShift more eventfull we of course offer activites. We are proud to boast out travel-portal 
            where we have selected some popular portalworlds to explore. Maybe with a bicykle and a picnick in the Digiworld? 
            Or se if you are lucky to meet Aslan in Narnia or just a friendly faun? We have a large library to explore if you prefere to stay 
            at the hotel and just study up on the worlds beyond by candle light. Then maybe a plunge in our pool would suit you better? 

            Explore our selection and welcome to your most magical stay yet! 
        </p>
        </aside>
        <picture class="activityCarousel">
            <img src="Images/activities/88C57663-7FB2-4C43-B0BD-E055FFFE73A7_1_105_c.jpeg">
        </picture>
    </section>
    
<?php
require_once __DIR__ . '/features.php';
?>


 <section class="calendars fullpage-section">
        <div class="calendars-grid">

            <div class="calendar-wrapper">
                <form class="booking-form" method="post" action="book.php">
                    <div class="room-selector">
                        <select name="room" id="room-select">
                            <option value="">Chose Your Room</option>
                            <option value="budget">Budget - 3 credits/night</option>
                            <option value="standard">Standard - 6 credits/night </option>
                            <option value="luxury">Luxury - 9 credits/night</option>
                        </select>
                    </div>    
                
                    <div class="activity-selector">
                       <select name="activities[]" id="feature-select" multiple>
                            <option value="">Chose Your Activity</option>
                            <?php foreach ($featureGrid as $category => $tiers): ?>
                                <?php foreach ($tiers as $tier => $name): ?>
                                    <?php $key = $category . ':' . $tier; ?>
                                        <?php if (in_array($key, $offeredActivities, true)) : ?>
                                    <option value="<?= htmlspecialchars($key) ?>">
                                        <?= ucfirst($name) ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>

                    </div>

                    <div class="form-row">
                        <label for="fullname">Name</label>
                        <input type="text" name="guest_name" id="fullname" required>
                    </div>

                    <div class="form-row">
                        <label for="transfer-code">Transfer-Code</label>
                        <input type="text" name="transfer_code" id="transfer-code" required>
                    </div>
                    <input type="hidden" name="selected_days" id="selected-days">                        
                    <button type="submit">Book Now</button>
                </form>
        
        
                    <input type="hidden" name="selected_days_room" id="selected-days-room">
                    <?php renderCalendar('Room Booking', getBookings('room'), 'room'); ?>
                    <input type="hidden" name="selected_days_activity" id="selected-days-activity">
                    <?php renderCalendar('Activities & Features', getBookings('activity', $featureGrid), 'activity'); ?>
            
            </div>
        </div>    
</section>

<section class="room-gallery">
    <img src="Images/hotelrooms/budget-bunks.jpg" alt="Budget room">
    <img src="Images/hotelrooms/standard.jpg" alt="Standard room">
    <img src="Images/hotelrooms/luxury.jpg" alt="Luxury room">
</section>

    <?php
    $heroImages = glob(__DIR__ . '/Images/heroCarousel/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    $activityImages = glob(__DIR__ . '/Images/activities/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

    $heroImages = array_map(fn($img) => 'Images/heroCarousel/' . basename($img), $heroImages);
    $activityImages = array_map(fn($img) => 'Images/activities/' . basename($img), $activityImages);
    ?>

    <script>    
        const heroImages = <?= json_encode($heroImages, JSON_UNESCAPED_SLASHES) ?>;
        const activityImages = <?= json_encode($activityImages, JSON_UNESCAPED_SLASHES) ?>;
    </script>

    <script src="script.js"></script>
</main> 

</body>
</html>