<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidTimeRange implements Rule
{
    protected $startTime;
    protected $endTime;

    public function __construct($startTime, $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function passes($attribute, $value)
    {
        return $this->startTime < $this->endTime;
    }

    public function message()
    {
        return 'The start time must be before the end time.';
    }
}
