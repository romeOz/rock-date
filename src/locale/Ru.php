<?php

namespace rock\date\locale;

use rock\date\DateTime;

class Ru extends Locale
{
    protected $year = ['год', 'года', 'лет'];
    protected $month = ['месяц', 'месяца', 'месяцев'];
    protected $week = ['неделя', 'недели', 'недель'];
    protected $day = ['день', 'дня', 'дней'];
    protected $hour = ['час', 'часа', 'часов'];
    protected $minute = ['минута', 'минуты', 'минут'];
    protected $second = ['секунда', 'секунды', 'секунд'];
    protected $months = [
        'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября',
        'декабря'
    ];
    protected $shortMonths = [
        'янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'
    ];
    protected $weekDays = [
        'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье'
    ];
    protected $shortWeekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
    protected $formats = [
        DateTime::USER_DATE_FORMAT => 'd.m.Y',
        DateTime::USER_TIME_FORMAT => 'G:i',
        DateTime::USER_DATETIME_FORMAT => 'd.m.Y G:i',
    ];
    protected $options = [
        'ago' => '{{number}} {{name}} назад'
    ];
}
