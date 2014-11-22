<?php if(!defined('IS_CMS')) die();
# ACHTUNG UTF-8 Codiert
$lang['timepicker'] = array(
        # datepicker
        "closeText" => "schließen",
        "prevText" => "zurück",
        "nextText" => "Vor",
        "weekHeader" => "&nbsp;&nbsp;&nbsp;",
        "firstDay" =>  "1",
        "isRTL" =>  "false",
        # timepicker
        "timeText" => "Zeit",
        "hourText" => "Stunde",
        "minuteText" => "Minute",
        "secondText" => "Sekunde"
);

$lang['holidays'] = array(
                    "Neujahrstag",
                    "Tag der Arbeit",
                    "Tag der Deutschen Einheit",
                    "1. Weihnachtstag",
                    "2. Weihnachtstag",
                    "Karfreitag",
                    "Ostermontag",
                    "Christi Himmelfahrt",
                    "Pfingstmontag",
                    "Fronleichnam"
                    );

# mit welchen tag begint die woche
$lang['week_days'] = array(1,2,3,4,5,6,0);

$lang['week_nr_title'] = "kW";

$lang['month'] = array("", # Achtun Wichtig weil sonst der Januar mit 0 Anfängt
                    "Januar",
                    "Februar",
                    "März",
                    "April",
                    "Mai",
                    "Juni",
                    "Juli",
                    "August",
                    "September",
                    "Oktober",
                    "November",
                    "Dezember"
                    );
# denn Dummy eintrag raus jetzt Fängt der Januar mit 1 an
unset($lang['month'][0]);

$lang['month_small'] = array("", # Achtun Wichtig weil sonst der Januar mit 0 Anfängt
                    "Jan",
                    "Feb",
                    "Mär",
                    "Apr",
                    "Mai",
                    "Jun",
                    "Jul",
                    "Aug",
                    "Sep",
                    "Okt",
                    "Nov",
                    "Dez"
                    );
# denn Dummy eintrag raus jetzt Fängt der Januar mit 1 an
unset($lang['month_small'][0]);

$lang['day'] = array( # Achtung die Woche begind mit Sonntag
                    "Sonntag",
                    "Montag",
                    "Dienstag",
                    "Mittwoch",
                    "Donnerstag",
                    "Freitag",
                    "Samstag"
                    );

$lang['day_small'] = array( # Achtung die Woche begind mit Sonntag
                    "So",
                    "Mo",
                    "Di",
                    "Mi",
                    "Do",
                    "Fr",
                    "Sa"
                    );

$lang["D"] = array_combine(array("Sun","Mon","Tue","Wed","Thu","Fri","Sat"),$lang['day_small']);

$lang["l"] = array_combine(array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"),$lang['day']);

$lang["M"] = array_combine(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"),$lang['month_small']);

$lang["F"] = array_combine(array("January","February","March","April","May","June","July","August","September","October","November","December"),$lang['month']);

return $lang;
?>