<?php
function build_calendar($month, $year, $technician) {
    
    $mysqli = new mysqli('localhost', 'root', 'AlexAMC-518984', 'bookingcalendar');
    //Gets all nail technicians
    $stmt = $mysqli->prepare('select * from technicians');
    $technicians = "";
    $first_technician = 0;
    $i = 0;
    if($stmt->execute()) {
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                if( $i == 0) {
                    $first_room = $row['id'];
                }
                $technicians = $technicians . "<option value=" . $row['id'] . ">" . $row['name'] . "</options>";
                $i++;
            }
            $stmt->close();
        }
    }

    if($technician != 0) {
        $first_technician = $technician;
    }

    $stmt = $mysqli->prepare('select * from bookings where MONTH(date) = ? AND YEAR(date) = ? AND technician = ?');
    $stmt->bind_param('ssi', $month, $year, $first_technician);
    $bookings = array();
    if($stmt->execute()) {
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $bookings[] = $row['date'];
            }

            $stmt->close();
        }
    }
    
    //all days in week
    $daysOfWeek = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

    //Get First day of month
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);

    //Get number of days in month
    $numDays = date('t', $firstDayOfMonth);

    //Get info on first day of month
    $dateComponents = getdate($firstDayOfMonth);

    //Get name of this month
    $monthName = $dateComponents['month'];

    //Getting index value 0-6 of the first day of current month
    $dayOfWeek = $dateComponents['wday'];
    //Structure of calendar
    /*if($dayOfWeek == 0) {
        $dayOfWeek = 3;
    }
    else {
        $dayOfWeek = $dayOfWeek - 1;
    }*/

    //Get current date
    $datetoday = date('Y-m-d');

    

    //Creating HTML Table
    $prev_month = date('m', mktime(0, 0, 0, $month - 1, 1, $year));
    $prev_year = date('Y', mktime(0, 0, 0, $month-1, 1, $year));
    $next_month = date('m', mktime(0, 0, 0, $month + 1, 1, $year));
    $next_year = date('Y', mktime(0, 0, 0, $month + 1, 1, $year));

    $calendar = "<center><h2>$monthName $year</h2>";
    $calendar = $calendar . "<a class='btn btn-primary btn-xs' href='?month=" . $prev_month . 
        "&year=" . $prev_year . "'>Prev Month</a>";
    $calendar = $calendar . "<a class='btn btn-primary btn-xs' href='?month=" . date('m') . 
        "&year=" . date('Y') . "'>Current Month</a>";
    $calendar = $calendar . "<a class='btn btn-primary btn-xs' href='?month=". $next_month . 
        "&year=" . $next_year . "'>Next Month</a></center>";

    $calendar = $calendar . "
        <form id='technician_select_form'>
            <div class='row'>
                <label>Select Nail Technician</label>
                <select class='form-control' id='technician_select' name='technician'>
                    " . $technicians . "
                </select>
                <input type='hidden' name='month' value='". $month . "'>
                <input type='hidden' name='year' value='". $year . "'>
            </div>
        </form>
        <table class='table table-bordered'>";
    $calendar= $calendar . "<tr>";

    //Create Calendar Headers
    foreach($daysOfWeek as $day) {
        $calendar= $calendar . "<th class='header'>$day</th>";
    }

    $calendar = $calendar . "</tr><tr>";
    //increments day
    $currentDay = 1;

    //variable $dayOfWeek will make sure that there must ONLY be 7 columns on our table
    if($daysOfWeek > 0){
        for($k = 0; $k < $dayOfWeek; $k++) {
            $calendar= $calendar . "<td class='empty'></td>";
        }
    }
    

    //Get Month Number

    $month = str_pad($month, 2,"0", STR_PAD_LEFT);

    while($currentDay <= $numDays) {

        //if seventh column (saturday reached, start a new row)
        if($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar= $calendar . "</tr><tr>";
        }

        $currentDayRel = str_pad($currentDay, 2,"0", STR_PAD_LEFT);
        $date = "$year-$month-$currentDayRel";

        $dayName = strtolower(date('I', strtotime($date)));
        $eventNum = 0;
        $today = $date==date('Y-m-d') ? 'today' : "";

        //if statement for if holidays are needed to take off some days, make second if statement else
        /*if($dayName == '' || $dayName == '') {
            $calendar = $calendar . "<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>Holiday</button>";
        }*/
        if($date < date('Y-m-d')) {
            $calendar = $calendar . "<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>N/A</button>";
        }
        else {

            //Checks to see if all timeslots are booked and makes the Booked button red to 'All booked'
            $totalbookings = checkSlots($mysqli, $date);

            //if totalbookings equals maxed timeslots, then it will change the button
            if($totalbookings == 36) {
                $calendar = $calendar . "<td class='$today'><h4>$currentDay</h4> 
                <a href='#' class='btn btn-danger btn-xs'>All Booked</a>";
            }
            else {
                $availableSlots = 36 - $totalbookings;
                $calendar = $calendar . "<td class='$today'><h4>$currentDay</h4> 
                <a href='book.php?date=" . $date . "'class='btn btn-success btn-xs'>Book</a> <small><i>$availableSlots slots available</i></small>";
            }
            
        }
        

        //incrementing counters
        $currentDay++;
        $dayOfWeek++;
    }

    //Completing row of the last week in month
    if($dayOfWeek < 7) {
        $remainingDays = 7 - $dayOfWeek;
        for($i = 0; $i < $remainingDays; $i++) {
            $calendar= $calendar . "<td class='empty'></td>";

        }
    }

    $calendar= $calendar . "</tr></table>";

    return $calendar;
    echo $calendar;
}

function checkSlots($mysqli, $date) {
    $stmt = $mysqli->prepare("select * from bookings where date = ?");
    $stmt->bind_param('s', $date);
    $totalbookings = 0;
    if($stmt->execute()) {
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $totalbookings++;
            }

            $stmt->close();
        }
    }

    return $totalbookings;
}
?>



<html>
<head>
    <meta name = "viewport" content = "width = device-width, initial-scale = 1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <style>
        @media only screen and (max-widith: 760px), 
        (min-device-width: 802px) and (max-device-width: 12020px) {
            
            // Force table to not be like tables 
            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }
            /*
            .empty {
                display: none;
            }*/
            
            // Hide table headers (but not display: none;, for accessibility) 
            th {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                border: 1px solid #ccc;
            }

            td {
                // Behave like a "row" 
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
            }

            // Label the data 
            /*td:nth-of-type(1):before {
                content: "Sunday";
            }
            td:nth-of-type(2):before {
                content: "Monday";
            }
            td:nth-of-type(3):before {
                content: "Tuesday";
            }
            td:nth-of-type(4):before {
                content: "Wednesday";
            }
            td:nth-of-type(5):before {
                content: "Thursday";
            }
            td:nth-of-type(6):before {
                content: "Friday";
            }
            td:nth-of-type(7):before {
                content: "Saturday";
            }*/
        }
        
        // Smartphones (portrait and landscape) 
        @media only screen and (min-device-width: 320px) and (max-device-width: 480px) {
            body {
                padding: 0;
                margin: 0;
            }
        }
        
        // iPads (portrait and landscape) 
        @media only screen and (min-device-width: 802px) and (max-device-width: 1020px) {
            body {
                width: 495px;
            }
        }
        
        @media (min-width: 641px) {
            table {
                table-layout: fixed;
            }

            td {
                width: 33%;
            }
        }
        
        .row {
            margin-top: 20px;
        }
        table {
            table-layout:fixed;
        }

        td {
            width:33%;
        }

        .today {
            background: yellow;
        }

    </style>
</head>
<body>
    <div class = "container">
        <div class = "row">
            <div class = "col-md-12">
                <?php
                $dateComponents = getdate();
                if(isset($_GET['month']) && isset($_GET['year'])) {
                    $month = $_GET['month'];
                    $year = $_GET['year'];
                }
                else {
                    $month = $dateComponents['mon'];
                    $year = $dateComponents['year'];
                }

                if(isset($_GET['technician'])) {
                    $technician = $_GET['technician'];
                }
                else {
                    $technician = 0;
                }

                echo build_calendar($month, $year, $technician);
                ?>
            </div>
        </div>
    </div>
    <script 
        src="https://code.jquery.com/jquery-3.4.1.min.js" 
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFIBw8HfCJo="
        crossorigin="anonymous">
    </script>
    <script>
        $("#technician_select").change(function() {
            $("#technician_select_form").submit();
        });

        $("#technician_select option[value='<?php echo $technician; ?>']").attr('selected', 'selected');
    </script>
    <!--<script>
        $.ajax({
            url: "calendar.php",
            type:"POST",
            data: {'month': '<?php echo date('m'); ?>', 'year':'<?php echo date('Y'); ?>'},
            success: function(data) {
                $("#calendar").html(data);
            }
        }) ;

        $(document).on('click', 'changemonth', function() {
            $.ajax({
            url: "calendar.php",
            type:"POST",
            data: {'month': $(this).data('month'),'year':$(this).data('year')},
            success: function(data) {
                $("#calendar").html(data);
            }
            });
        });
    </script>-->
</body>
</html>