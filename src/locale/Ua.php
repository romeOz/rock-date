<?php

namespace rock\date\locale;

use rock\date\DateTime;

class Ua extends Locale
{
    protected $year = ['рік', 'року', 'років'];
    protected $month = ['місяць', 'місяці', 'місяців'];
    protected $week = ['тиждень', 'тижня', 'тижнів'];
    protected $day = ['день', 'дня', 'днів'];
    protected $hour = ['годину', 'години', 'годин'];
    protected $minute = ['хвилина', 'хвилини', 'хвилин'];
    protected $second = ['секунда', 'секунди', 'секунд'];
    protected $months = [
        'січня', 'лютого', 'березня', 'квітня', 'травня', 'червня', 'липня', 'серпня', 'вересня', 'жовтня', 'листопада',
        'грудня'
    ];
    protected $weekDays = ['понеділок', 'вівторок', 'середа', 'четвер', "п'ятниця", 'субота', 'неділя'];
    protected $shortWeekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];
    protected $formats = array(
        DateTime::USER_DATE_FORMAT => 'd.m.Y',
        DateTime::USER_TIME_FORMAT => 'G:i',
        DateTime::USER_DATETIME_FORMAT => 'd.m.Y G:i',
    );
    protected $options = [
        'ago' => '{{number}} {{name}} тому'
    ];
}
