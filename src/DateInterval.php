<?php

namespace rock\date;


class DateInterval extends \DateInterval
{
    public $total_months;
    public $total_weeks;
    public $total_days;
    public $total_hours;
    public $total_minutes;
    public $total_seconds;

    public function format($format)
    {
        $callback = function($value){
            if ($value[0] === '%tm') {
                return $this->total_months;
            }
            if ($value[0] === '%tw') {
                return $this->total_weeks;
            }

            if ($value[0] === '%a' || $value[0] === '%td') {
                return $this->total_days;
            }

            if ($value[0] === '%th') {
                return $this->total_hours;
            }
            if ($value[0] === '%ti') {
                return $this->total_minutes;
            }

            if ($value[0] === '%ts') {
                return $this->total_seconds;
            }

            return parent::format($value[0]);

        };
        return preg_replace_callback('/%(?:[a-z]{1,2})+/i', $callback, $format);

    }
}