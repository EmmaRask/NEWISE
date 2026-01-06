<?php

declare(strict_types=1);

require_once __DIR__ . "/calendar.php";
    $roomBookings = [];
    $activityBookings = [];

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
    <nav>
        <picture class="nav-icon">
            <img src="nav/icons/pngwing.com (1).png" alt="nav-icon">
        </picture>
    </nav>
<main>

<section class="hero-fullwidth fullpage-section">
    <section class="hero split-section">

            <div class="hero-carousel">
                <img src="Images/heroCarousel/050BD195-2214-450D-98AF-24E36F076C5B_1_105_c.jpeg" alt="EverShift">
            </div>   

            <aside class="hero-info">
                <h1>EverShift</h1>
                <p class="tagline"><i>Wake up to a new horizon, every day!</i></p>
                <p>
                    Image you could travel not only between in style and comfort but also exoperince all 
                    four climates while doing so! Here at the Evershift we bring that very possibility to life.

                    Fall asleep to the fire crackling to kkep the chill away in a winter wonferland and wake up to cherry 
                    blossoms and spring just hours later without uncomfortable flights that take time from your vacation. 
                    The island of NEWISLE is a magical place andf home to the wandering hotel EverShift, a moving hotel 
                    that takes you on an unforgettable yourney across the realm of snow, ice, sun, rain and soaring blue skies. 
        
                    Our owner and powersource Calisefer can almost always be meet in the library. Their wishgranting time may be over,
                    But you never know what they may surprise you with!  Welcome aboard and enyoy the most spectacular place Yrgepelag can offer! 
                </p>
            </aside>
        </section>
    </section>
</section>
    
    <section class="offers fullpage-section split-section">
        <aside class="offer-info">
        <h2>Offers!</h2>
        <p>
            Traveling light? We offer rooms for budget traveling vagabonds, standard room if you want to up your 
            comfortability as well as a full luxuray stay for the one who know to enjoy their travels in style and comfort. 
            Right now you can stay at our bugdet friendly room and get an activity for free!
            Want to enjoy all NEWISE have to offer? 
            
            Then consider our lux package weekend stay, our luxury room and 
            one actitivity a day included in the price! Book now to not miss out this January!
        </p>
        </aside>
        <picture class="activityCarousel">
            <img src="Images/activities/88C57663-7FB2-4C43-B0BD-E055FFFE73A7_1_105_c.jpeg">
        </picture>
    </section>
    <section class="calendars fullpage-section">
        <div class="calendars-grid">

            <div class="calendar-wrapper">
                <div class="room-selector">
                    <button data-room="budget">Budget</button>
                    <button data-room="standard">Standard</button>
                    <button data-room="luxury">Luxury</button>
                </div>

                <?php renderCalendar('Room Booking', getBookings('room'), 'room'); ?>
            </div>

            <?php renderCalendar('Activities & Features', getBookings('activity'), 'activity'); ?>
            <?php renderCalendar('Special Offers', getBookings('offer'), 'offer'); ?>

        </div>
    </section>

</main> 

</body>
</html>