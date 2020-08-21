<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateNotOverlap implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($start = 'start', $end = 'end')
    {
        //
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $intersect = true;

        // check if values is array
        // foreach ($value as &$v) {
        //     if (is_string($v)) {
        //         $v = (array) json_decode($v);
        //     } else {
        //         break;
        //     }
        // }

        for($i=0;$i<count($value); $i++){
            for($j=$i+1;$j<count($value); $j++){
                // Does not allow ranges that 'touch'
                // if($value[$i][$this->start]<=$value[$j][$this->end] && $value[$i][$this->end]>=$value[$j][$this->start])
                if($value[$i][$this->start]<$value[$j][$this->end] && $value[$i][$this->end]>$value[$j][$this->start])
                {
                    $intersect = false;
                }
            }
        }
        return $intersect;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The dates overlap each other.';
    }
}
