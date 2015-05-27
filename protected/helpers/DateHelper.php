<?php
/**
 * Copyright 2015 Sky Wickenden
 *
 * This file is part of StreamBed.
 * An implementation of the Babbling Brook Protocol.
 *
 * StreamBed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * at your option any later version.
 *
 * StreamBed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with StreamBed.  If not, see <http://www.gnu.org/licenses/>
 */

/**
 *  Helper class for working with dates.
 *
 * @package PHP_Helper
 */
class DateHelper
{

    /**
     * Based on function origionaly created by Dunstan Orchard - http://1976design.com
     * and adapted by BuddyPress - http://buddypress.trac.wordpress.org/browser/tags/1.2.7/bp-core.php#L1396
     *
     * This function will return an English representation of the time elapsed
     * since a given date.
     * eg: 2 hours and 50 minutes
     * eg: 4 days
     * eg: 4 weeks and 6 days
     *
     * @param $older_date int Unix timestamp of date you want to calculate the time since for.
     * @param $newer_date int Unix timestamp of date to compare older date to. Default false (current time).
     *
     * @return str The time since.
     */
    public static function timeSince($older_date, $newer_date=false) {

        // array of time period chunks
        $chunks = array(
            array(60 * 60 * 24 * 365, 'year', 'years'),
            array(60 * 60 * 24 * 7, 'week', 'weeks'),
            array(60 * 60 * 24, 'day', 'days'),
            array(60 * 60, 'hour', 'hours'),
            array(60, 'minute', 'minutes'),
            array(1, 'second', 'seconds'),
        );

        if (is_numeric($older_date) === false) {
            $time_chunks = explode(':', str_replace(' ', ':', $older_date));
            $date_chunks = explode('-', str_replace(' ', '-', $older_date));

            $older_date = gmmktime(
                (int)$time_chunks[1],
                (int)$time_chunks[2],
                (int)$time_chunks[3],
                (int)$date_chunks[1],
                (int)$date_chunks[2],
                (int)$date_chunks[0]
            );
        }

        // $newer_date will equal false if we want to know the time elapsed between a date and the current time.
        // $newer_date will have a value if we want to work out time elapsed between two known dates.
        $newer_date = ($newer_date === false) ? time() : $newer_date;

        /* Difference in seconds */
        $since = $newer_date - $older_date;

        /* Something went wrong with date calculation and we ended up with a negative date. */
        if (0 > $since) {
            throw new Exception("Negative time found. " . $since);
        }

        /**
        * We only want to output two chunks of time here, eg:
        * x years, xx months
        * x days, xx hours
        * so there's only two bits of calculation below:
        */

        // Step one: the first chunk
        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];

            // Finding the biggest chunk (if the chunk fits, break).
            $count = floor($since / $seconds);
            if ((int)$count !== 0) {
                break;
            }
        }

        // Set output var to singular or plural.
        $output = (1 === $count) ? '1 ' . $chunks[$i][1] : $count . ' ' . $chunks[$i][2];

        // Step two: the second chunk
//        if ($i + 2 < $j) {
//            $seconds2 = $chunks[$i + 1][0];
//
//            $count2 = floor(($since - ($seconds * $count)) / $seconds2);
//            if ((int)$count2 !== 0) {
//                $output .=  ' and ';
//                $output .=  (1 === $count2) ? '1 ' . $chunks[$i + 1][1] : $count2 . ' ' . $chunks[$i + 1][2];
//            }
//        }

        if ((int)trim($output) === false) {
            $output = '0 seconds';
        }

        return $output;
    }

}

?>