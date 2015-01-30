<?php

namespace rock\date\locale;


use rock\date\DateTime;

class EnUK extends Locale
{
    protected $year = ['year', 'years', 'years'];
    protected $month = ['month', 'months', 'months'];
    protected $week = ['week', 'weeks', 'weeks'];
    protected $day = ['day', 'days', 'days'];
    protected $hour = ['hour', 'hours', 'hours'];
    protected $minute = ['minute', 'minutes', 'minutes'];
    protected $second = ['second', 'seconds', 'seconds'];
        protected $months = [
        'January', 'February', 'March', 'April', 'May', 'June', 'Jule', 'August', 'September', 'October', 'November',
        'December'
    ];
    protected $shortMonths = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    protected $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    protected $shortWeekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    protected $formats = [
        DateTime::USER_DATE_FORMAT => 'm/d/Y',
        DateTime::USER_TIME_FORMAT => 'G:i',
        DateTime::USER_DATETIME_FORMAT => 'm/d/Y G:i',
    ];
    protected $options = [
        'ago' => '{{number}} {{name}} ago'
    ];
}
