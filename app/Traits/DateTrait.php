<?php

namespace App\Traits;

use DateTime;

trait TestTrait
{
    private function checkOverlappingDates()
    {
        $ranges = array(
            array('start' => new DateTime('2014-01-01'), 'end' => new DateTime('2014-01-05')),
            array('start' => new DateTime('2014-01-06'), 'end' => new DateTime('2014-01-06')),
            array('start' => new DateTime('2014-01-07'), 'end' => new DateTime('2014-01-07')),
        );

        function intersects($lhs, $rhs) {
            // Note that this function allows ranges that "touch",
            // eg. one pair starts at the exact same time that the other ends.
            // Adding less "or equal to" will allow same start date
            return !($lhs['start'] > $rhs['end'] || $lhs['end'] < $rhs['start']);
        }

        function checkDates($ranges) {
            // Comparison loop is of size n•log(n), not doing any redundant comparisons
            for($i = 0; $i < sizeof($ranges); $i++) {
                for($j = $i+1; $j < sizeof($ranges); $j++) {
                    if(intersects($ranges[$i], $ranges[$j])) {
                        echo "Date {$i} intersects with date {$j}\n";
                    }
                }
            }
        }

        checkDates($ranges);
    }

}
