<?php

namespace rock\date\locale;

use rock\base\ClassName;

/**
 * Specify translations & formats for different locales
 */
abstract class Locale
{
    use ClassName;

    /**
     * Plural names of year.
     * @var array
     */
    protected $year = [];
    /**
     * Plural names of month.
     * @var array
     */
    protected $month = [];
    /**
     * Plural names of week.
     * @var array
     */
    protected $week = [];
    /**
     * Plural names of day.
     * @var array
     */
    protected $day = [];
    /**
     * Plural names of hour.
     * @var array
     */
    protected $hour = [];
    /**
     * Plural names of minute.
     * @var array
     */
    protected $minute = [];
    /**
     * Plural names of second.
     * @var array
     */
    protected $second = [];
    /**
     * i18n names of month.
     * @var array
     */
    protected $months = [];
    /**
     * i18n short-names of month.
     * @var array
     */
    protected $shortMonths = [];
    /**
     * i18n names of week days.
     * @var array
     */
    protected $weekDays = [];
    /**
     * i18n short-names of week days.
     * @var array
     */
    protected $shortWeekDays = [];
    /**
     * List formats.
     * @var array
     */
    protected $formats = [];
    /**
     * List options.
     * @var array
     */
    protected $options = [];

    /**
     * Returns list formats.
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * Returns list options.
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns i18n name of month by index.
     * @param int $index
     * @return string
     */
    public function getMonth($index)
    {
        return $this->months[$index];
    }

    /**
     * Returns i18n short-name of month by index.
     * @param int $index
     * @return string
     */
    public function getShortMonth($index)
    {
        return $this->shortMonths[$index];
    }

    /**
     * Returns i18n names of month.
     * @return array
     */
    public function getMonths()
    {
        return $this->months;
    }

    /**
     * Returns i18n name of week day by index.
     * @param int $index
     * @return string
     */
    public function getWeekDay($index)
    {
        return $this->weekDays[$index];
    }

    /**
     * Returns i18n names of week day.
     * @return array
     */
    public function getWeekDays()
    {
        return $this->weekDays;
    }

    /**
     * Returns i18n short-name of week day by index.
     * @param int $index
     * @return string
     */
    public function getShortWeekDay($index)
    {
        return $this->shortWeekDays[$index];
    }

    /**
     * Returns i18n short-names of week day.
     * @return array
     */
    public function getShortWeekDays()
    {
        return $this->shortWeekDays;
    }

    /**
     * Returns i18n names of year.
     * @return array
     */
    public function getYearNames()
    {
        return $this->year;
    }

    /**
     * Returns plural names of month.
     * @return array
     */
    public function getMonthNames()
    {
        return $this->month;
    }

    /**
     * Returns plural names of week.
     * @return array
     */
    public function getWeekNames()
    {
        return $this->week;
    }

    /**
     * Returns plural names of day.
     * @return array
     */
    public function getDayNames()
    {
        return $this->day;
    }

    /**
     * Returns plural names of hour.
     * @return array
     */
    public function getHourNames()
    {
        return $this->hour;
    }

    /**
     * Returns plural names of minute.
     * @return array
     */
    public function getMinuteNames()
    {
        return $this->minute;
    }

    /**
     * Returns plural names of second.
     * @return array
     */
    public function getSecondNames()
    {
        return $this->second;
    }
}