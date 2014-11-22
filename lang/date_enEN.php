<?php if(!defined('IS_CMS')) die();
# ACHTUNG UTF-8 Codiert

$lang['timepicker'] = array(
        # datepicker
        "closeText" => "Done",
        "prevText" => "Prev",
        "nextText" => "Next",
        "weekHeader" => "&nbsp;&nbsp;&nbsp;",
        "firstDay" =>  "1",
        "isRTL" =>  "false",
        # timepicker
        "timeText" => "Time",
        "hourText" => "Hour",
        "minuteText" => "Minute",
        "secondText" => "Second"
);

$lang['holidays'] = array(
                    "New year's day",
                    "Labour day",
                    "German unification day",
                    "1. Xmas day",
                    "2. Xmas day",
                    "Good Friday",
                    "Easter Monday",
                    "Ascension day",
                    "Pentecost",
                    "Corpus Christi"
                    );

# mit welchen tag begint die woche
$lang['week_days'] = array(0,1,2,3,4,5,6);

$lang['week_nr_title'] = "kW";

$lang['month'] = array("", # Achtun Wichtig weil sonst der Januar mit 0 Anfängt
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December"
                    );
# denn Dummy eintrag raus jetzt Fängt der Januar mit 1 an
unset($lang['month'][0]);

$lang['month_small'] = array("", # Achtun Wichtig weil sonst der Januar mit 0 Anfängt
                    "Jan",
                    "Feb",
                    "Mar",
                    "Apr",
                    "May",
                    "Jun",
                    "Jul",
                    "Aug",
                    "Sep",
                    "Oct",
                    "Nov",
                    "Dec"
                    );
# denn Dummy eintrag raus jetzt Fängt der Januar mit 1 an
unset($lang['month_small'][0]);

$lang['day'] = array( # Achtung die Woche begind mit Sonntag
                    "Sunday",
                    "Monday",
                    "Tuesday",
                    "Wednesday",
                    "Thursday",
                    "Friday",
                    "Saturday"
                    );

$lang['day_small'] = array( # Achtung die Woche begind mit Sonntag
                    "Su",
                    "Mo",
                    "Tu",
                    "We",
                    "Th",
                    "Fr",
                    "Sa"
                    );


$lang["D"] = array_combine(array("Sun","Mon","Tue","Wed","Thu","Fri","Sat"),$lang['day_small']);

$lang["l"] = array_combine(array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"),$lang['day']);

$lang["M"] = array_combine(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"),$lang['month_small']);

$lang["F"] = array_combine(array("January","February","March","April","May","June","July","August","September","October","November","December"),$lang['month']);

return $lang

?>