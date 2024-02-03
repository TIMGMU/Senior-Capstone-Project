<?php
function build_calendar($month, $year) {
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

    //Get current date
    $datetoday = date('Y-m-d');

    //Creating HTML Table
    $calendar = "<table class='table table-bordered'>";
    $calendar = $calendar . "<center><h2>$monthName $year</h2></center>";

    $calendar= $calendar . "<tr>";

    //Create Calendar Headers
    foreach($daysOfWeek as $day) {
        $calendar= $calendar . "<th class='header'>$day</th>";
    }

    $calendar = $calendar . "</tr><tr>";

    //variable $dayOfWeek will make sure that there must ONLY be 7 columns on our table
    if($daysOfWeek > 0){
        for($k = 0; $k < $dayOfWeek; $k++) {
            $calendar= $calendar . "<td></td>";
        }
    }

    //Initiating day counter
    $currentDay = 1;

    //Get Month Number

    $month = str_pad($month, 2,"0", STR_PAD_LEFT);

    while($currentDay <= $numDays) {

        //if seventh column (saturday reached, start a new row)
        if($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar= $calendar . "</tr><tr>";
        }

        $currentDayRel = str_pad($month, 2,"0", STR_PAD_LEFT);
        $date = "$year-$month-$currentDayRel";

        $calendar= $calendar . "<td><h4>$currentDay</h4>";

        $calendar = $calendar. "</td>";

        //incrementing counters
        $currentDay++;
        $dayOfWeek++;
    }

    //Completing row of the last week in month
    if($dayOfWeek != 7) {
        $remainingDays = 7 - $dayOfWeek;
        for($i = 0; $i < $remainingDays; $i++) {
            $calendar= $calendar . "<td></td>";

        }
    }

    $calendar= $calendar . "</tr>";
    $calendar= $calendar . "</table>";

    echo $calendar;
}
?>

<html>
<head>
    <meta name = "viewport" content = "width = device-width, initial-scale = 1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
</head>
<body>
    <div class = "container">
        <div class = "row">
            <div class = "col-md-12">
                <?php
                $dateComponents = getdate();
                $month = $dateComponents['mon'];
                $year = $dateComponents['year'];
                echo build_calendar($month, $year);
                ?>
            </div>
        </div>
    </div>
</body>
</html>