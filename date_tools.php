<?php

if (function_exists("date_default_timezone_set")) {
    date_default_timezone_set('Europe/Paris');
}

/*if (!$yy)
  $yy=date(Y);
if ($yy<1971 || $yy >2037) // Convert years beyond the UNIX-Timestamp
{*/

    function get_days_in_month($yyyy, $mm)
    {
        if ((int)$yyyy < 1800)
            return false;
        if ((int)$mm < 1 or (int)$mm > 12)
            return false;
        return (int)$mm == 2 ? ((int)$yyyy % 4 ? 28 : ((int)$yyyy % 100 ? 29 : ((int)$yyyy % 400 ? 28 : 29))) : (((int)$mm - 1) % 7 % 2 ? 30 : 31);
    }

    function get_days_in_year($yyyy)
    {
        if ((int)$yyyy < 1800)
            return false;
        return (337 + get_days_in_month($yyyy, 2));
    }

    function get_day_of_week($yyyy, $mm, $dd)
    {
        if ((int)$yyyy < 1800)
           return false;
        if ((int)$mm < 1 or (int)$mm > 12)
           return false;
        if ((int)$dd < 1 or (int)$dd > get_days_in_month($yyyy, $mm))
           return false;
        $t = array(0, 3, 2, 5, 0, 3, 5, 1, 4, 6, 2, 4);
        if ($mm < 3)
           $yyyy -= 1;
        return ((int)$yyyy + (int)($yyyy/4) - (int)($yyyy/100) + (int)($yyyy/400) + $t[(int)$mm-1] + (int)$dd) % 7;
    }

   /**
     * Returns a multidimentional array of worked days with values set to 1 on worked days. Other days 0.
     *
     * @param	startTimestamp		timestamp for start date
     * @param	duration			duration in worked days of the project
     * @param	workedDays			list of worked day numbers separated by commas, values range from 1 to 7
     * @param	exceptions			array of tx_paeproject_exception objects
     * @param	mode				mode=0 : estimation. mode=1 : real.
     * @return	multidimentional array ordered by [year][month][day] filled with 0 and 1
     */
   function mapWorkedDays($startTimestamp, $duration, $workedDays, $exceptions, $mode = 0)
   {
        //echo("mapWorkedDays(start=" . date('m.d.Y',$startTimestamp) . ", duration=" . $duration . ", workedDays=(" . $workedDays . "), exceptions=" . sizeof($exceptions) . ", mode=" . $mode . ") <br>");

        $oneDayTimestampValue = 86400;

        $currentTimestamp = $startTimestamp;

        $startYear	= date('Y', $startTimestamp);
        $startMonth	= date('n', $startTimestamp);
        $startDay	= date('j', $startTimestamp);

        $durationRemaining = $duration;

        $workedDaysMap = array();
        $workedDaysMap[$startYear] = array();
        $workedDaysMap[$startYear][$startMonth] = array();

        //if the list of worked days is empty
        if (trim($workedDays) == "") {
            //return void map
            return $workedDaysMap;
        }
        else $workedDaysList = explode(',', $workedDays);

        //echo "workedDaysList=" . $workedDays . "<br>";

        while ($durationRemaining > 0) {

            //echo "durationRemaining=" . $durationRemaining . "<br>";
            //echo "currentDate=" . date('d/m/Y', $currentTimestamp) . "<br>";

            $currentYear  = date('Y', $currentTimestamp);
            $currentMonth = date('n', $currentTimestamp);
            $currentDay   = date('j', $currentTimestamp);

            //make sure arrays are correctly initialized
            if (!isset($workedDaysMap[$currentYear]))                              $workedDaysMap[$currentYear] = array();
            if (!isset($workedDaysMap[$currentYear][$currentMonth]))               $workedDaysMap[$currentYear][$currentMonth] = array();
            if (!isset($workedDaysMap[$currentYear][$currentMonth][$currentDay]))  $workedDaysMap[$currentYear][$currentMonth][$currentDay] = array();

            $currentDayOfWeek = get_day_of_week($currentYear, $currentMonth, $currentDay);

            //day is not worked by default
            $dayValue=0;

            //echo "currentDayOfWeek=".$currentDayOfWeek." in_array()=".in_array ($currentDayOfWeek, $workedDaysList)."<br>";

            //day value is set as 1 (worked) if matching worked days list
            if (in_array ($currentDayOfWeek, $workedDaysList)) {
                $dayValue = 1;
            }

            //checking exceptions
            foreach ($exceptions as $currentException) {
            $currentException->toHTML();
                //exception must not be disabled
                if ($currentException->data['disable'] == 0) {
                    //if we are evaluating estimated data and exception affects estimated data
                    if ($mode === 0 && $currentException->data['affect_estimation'] == 1) {
                        if ($currentTimestamp >= $currentException->data['start_date']) {
                            if ($currentTimestamp <= $currentException->data['end_date']) {
                                //setting day status to 0 as days is within exception range
                                $dayValue = 0;
                                //echo "EXCEPTION=".date('d/m/Y',$currentTimestamp)."<br>";
                            }
                        }
                    }

                    //if we are evaluating real data and exception affects real data
                    elseif ($mode === 1 && $currentException->data['affect_real'] == 1) {
                        if ($currentTimestamp >= $currentException->data['start_date']) {
                            if ($currentTimestamp <= $currentException->data['end_date']) {
                                //setting day status to 0 as days is within exception range
                                $dayValue = 0;
                                //echo "EXCEPTION=".date('d/m/Y',$currentTimestamp)."<br>";
                            }
                        }
                    }
                }

            }

            //recording day state : 0=not worked, 1=worked
            $workedDaysMap[$currentYear][$currentMonth][$currentDay] = $dayValue;

            //Removing one day from remaining duration if day as been marked as worked
            if ($dayValue === 1) {
                $durationRemaining--;
            }

            //advancing one day
            $currentTimestamp += $oneDayTimestampValue;
        }
        return $workedDaysMap;
    }

    /**
     * Returns the last worked day of the supplied workedDayMap.
     *
     * @param	workedDayMap		the worked day map
     * @return	the unix timestamp of the very last day in map
     */
    function getEndDate($workedDayMap)
    {
        if (isset($workedDayMap) && sizeof($workedDayMap) > 0) {
            //find last year in map
            @end($workedDayMap);
            $lastYear = key($workedDayMap);

            //find last month in last year array
            @end($workedDayMap[$lastYear]);
            $lastMonth = key($workedDayMap[$lastYear]);

            //find last day in last month array
            @end($workedDayMap[$lastYear][$lastMonth]);
            $lastDay = key($workedDayMap[$lastYear][$lastMonth]);

            return strtotime($lastYear . "-" . $lastMonth . "-" . $lastDay);
        }
        else return 0;
    }

    /**
     * Returns the first worked day of the supplied workedDayMap.
     *
     * @param	workedDayMap		the worked day map
     * @return	the unix timestamp of the very last day in map
     */
	function getStartDate($workedDayMap)
    {
        if (isset($workedDayMap) && sizeof($workedDayMap) > 0) {
            //find last year in map
            @reset($workedDayMap);
            $firstYear = key($workedDayMap);

            //find last month in last year array
            @reset($workedDayMap[$lastYear]);
            $firstMonth = key($workedDayMap[$firstYear]);

            //find last day in last month array
            @reset($workedDayMap[$firstYear][$firstMonth]);
            $firstDay = key($workedDayMap[$firstYear][$firstMonth]);

            return strtotime($firstYear . "-" . $firstMonth . "-" . $firstDay);
        }
        else return 0;
	}

    /**
     * This function calculaties the days between two data (Ymd)
     * By example, if $datum1 = 20071215 and $datum2 = 20081215, the output will be 366 ;-)
     */
	function calculate_day_between($datum1,$datum2)
    {
        //echo "datum1=".$datum1." datum2=".$datum2." <br>";
        if (is_numeric($datum1) && is_numeric($datum2) && strlen($datum1) == 8 && strlen($datum2) == 8) {
            $dat  = ($datum1 < $datum2) ? $datum1 : $datum2;
            $datv = ($datum1 < $datum2) ? $datum2 : $datum1;
            $i    = 0;
            while ( $dat < $datv) {
                $i++;
                switch (substr($dat,6,2)) {
                    case '28': $dat += (substr($dat,4,2) == 02 && substr($dat,0,4)%4 > 0 )? 73 : 1;
                        break;
                    case '29': $dat += (substr($dat,4,2) == 02 && substr($dat,0,4)%4 == 0 )? 72 : 1;
                        break;
                    case '30': $dat += (in_array( substr($dat,4,2), array(04,06,09,11)))? 71 : 1;
                        break;
                    case '31': $dat += (substr($dat,4,2) == 12 )? 8870 : 70;
                        break;
                    default:   $dat++;
                        break;
                }
            }
            return $i-1;
        }
        else {
            return false;
        }
    }

    /*
    $daysInYear = get_days_in_year($yy);
    $startDay = get_day_of_week($yy,1,1);
    if ($daysInYear == 365) {
        if ($startDay === 0)
            $y = 1978;
        elseif ($startDay === 1)
            $y = 1973;
        elseif ($startDay === 2)
            $y = 1974;
        elseif ($startDay === 3)
            $y = 1975;
        elseif ($startDay === 4)
            $y = 1981;
        elseif ($startDay === 5)
            $y = 1971;
        elseif ($startDay === 6)
            $y = 1977;
    } elseif ($daysInYear === 366) {
        if ($startDay === 0)
            $y = 1984;
        elseif ($startDay === 1)
            $y = 1996;
        elseif ($startDay === 2)
            $y = 1980;
        elseif ($startDay === 3)
            $y = 1992;
        elseif ($startDay === 4)
            $y = 1976;
        elseif ($startDay === 5)
            $y = 1988;
        elseif ($startDay === 6)
            $y = 1972;
    }
} else {
   $y = $yy;
}*/
