<?php

namespace rock\date;


use DateTimeZone;
use rock\base\ObjectInterface;
use rock\base\ObjectTrait;
use rock\date\locale\En;
use rock\date\locale\EnUK;
use rock\date\locale\Locale;
use rock\date\locale\Ru;
use rock\date\locale\Ua;
use rock\helpers\Inflector;
use rock\helpers\StringHelper;

/**
 * @method  date()
 * @method  time()
 * @method  datetime()
 */
class DateTime extends \DateTime implements DateTimeInterface, ObjectInterface
{
    use ObjectTrait {
        ObjectTrait::__construct as parentConstruct;
    }

    /**
     * Default format.
     * @var string
     */
    protected $defaultFormat = 'Y-m-d H:i:s';
    /**
     * List locales {@see \rock\date\locale\Locale}.
     * @var Locale[]
     */
    protected $locales = [];
    /**
     * List formats.
     * @var array
     */
    protected $formats = [
        self::USER_DATE_FORMAT => 'm/d/Y',
        self::USER_TIME_FORMAT => 'g:i A',
        self::USER_DATETIME_FORMAT => 'm/d/Y g:i A',
        self::ISO_DATE_FORMAT => 'Y-m-d',
        self::ISO_TIME_FORMAT => 'H:i:s',
        self::ISO_DATETIME_FORMAT => 'Y-m-d H:i:s',
        self::JS_FORMAT => self::RFC1123,
        self::W3C_FORMAT => self::W3C,
    ];
    /**
     * Current locale.
     * @var string
     */
    protected $locale = 'en';

    /**
     * @param string|int $time
     * @param string|\DateTimeZone $timezone
     * @param array $config
     */
    public function __construct($time = 'now', $timezone = null, array $config = [])
    {
        $this->parentConstruct($config);
        if (static::isTimestamp($time)) {
            $time = '@' . (string)$time;
        }
        parent::__construct($time, $this->calculateTimezone($timezone));

        $this->locales = array_merge($this->defaultLocales(), $this->locales);

        foreach ($this->defaultFormatOptions() as $name => $callback) {
            $this->setFormatOption($name, $callback);
        }
    }

    /**
     * Modify date.
     *
     * @param string|int $time time for modify
     * @param string|\DateTimeZone $timezone
     * @param array $config the configuration. It can be either a string representing the class name
     *                             or an array representing the object configuration.
     * @throws \rock\di\ContainerException
     * @return $this
     */
    public static function set($time = 'now', $timezone = null, array $config = [])
    {
        if (!isset($time)) {
            $time = 'now';
        }
        if (class_exists('\rock\di\Container')) {
            $config['class'] = static::className();
            return \rock\di\Container::load($config, [$time, $timezone]);
        }
        return new static($time, $timezone, $config);
    }

    /**
     * Sets a locale.
     * @param Locale|string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        if ($locale instanceof Locale) {
            $this->locale = $locale;
            return $this;
        }
        $this->locale = strtolower($locale);
        return $this;
    }

    /** @var Locale[] */
    protected static $localeInstances;

    /**
     * Returns a locale instance.
     * @return Locale
     */
    public function getLocale()
    {
        if ($this->locale instanceof Locale) {
            return $this->locale;
        }
        if (!isset($this->locales[$this->locale])) {
            $this->locale = 'en';
        }
        // lazy loading
        if (isset(static::$localeInstances[$this->locale])) {
            return static::$localeInstances[$this->locale];
        }
        return static::$localeInstances[$this->locale] = new $this->locales[$this->locale];
    }

    /**
     * Adds a list locales {@see \rock\date\locale\Locale}.
     * @param Locale[] $locales
     * @return $this
     */
    public function setLocales(array $locales)
    {
        $this->locales = array_merge($this->locales, $locales);
        return $this;
    }

    /**
     * Sets a default format.
     * @param string $format
     * @return $this
     * @see \rock\date\Datetime::$defaultFormat
     */
    public function setDefaultFormat($format)
    {
        $this->defaultFormat = $format;
        return $this;
    }

    /**
     * Returns a default format.
     * @return string
     */
    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    /**
     * Adds a list formats.
     * @param array $formats list formats.
     * @return $this
     */
    public function setFormats(array $formats)
    {
        $this->formats = array_merge($this->formats, $formats);
        return $this;
    }

    /**
     * Returns a format by name.
     * @param $name string
     * @return string|null
     */
    public function getFormat($name)
    {
        if (isset($this->formats[$name])) {
            return $this->formats[$name];
        }

        return null;
    }

    /**
     * Returns a list formats.
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * Adds a format options.
     * @param array $options
     * @return $this
     */
    public function setFormatOptions(array $options)
    {
        foreach ($options as $name => $handler) {
            $this->setFormatOption($name, $handler);
        }
        return $this;
    }

    protected static $formatOptionsNames = [];
    protected static $formatOptionsPlaceholders = [];
    protected static $formatOptionsHandlers = [];

    /**
     * Adds a format option.
     * @param string $name name of option.
     * @param callable $handler
     * @return $this
     * @throws DateException
     */
    public function setFormatOption($name, callable $handler)
    {
        if (in_array($name, static::$formatOptionsNames)) {
            return $this;
        }
        static::$formatOptionsNames[] = $name;
        static::$formatOptionsPlaceholders[] = '~' . count(static::$formatOptionsPlaceholders) . '~';
        static::$formatOptionsHandlers[] = $handler;
        return $this;
    }

    /**
     * Returns formatting date.
     *
     * @param string|null $format http://php.net/date format or format name. Default value is current
     * @return string
     */
    public function format($format = null)
    {
        if (empty($format)) {
            $format = $this->defaultFormat;
        }
        return $this->formatDatetimeObject($format);
    }

    /**
     * Returns date in `YYYY-MM-DD` format, in server timezone.
     *
     * @return string
     */
    public function isoDate()
    {
        return $this->format(self::ISO_DATE_FORMAT);
    }

    /**
     * Returns date in `HH-II-SS` format, in server timezone.
     *
     * @return string
     */
    public function isoTime()
    {
        return $this->format(self::ISO_TIME_FORMAT);
    }

    /**
     * Returns datetime in `YYYY-MM-DD HH:II:SS` format, in server timezone.
     *
     * @return string
     */
    public function isoDatetime()
    {
        return $this->format(self::ISO_DATETIME_FORMAT);
    }

    /**
     * Returns the difference between two DateTime objects.
     * @param string|int|\DateTime $datetime2
     * @param bool $absolute
     * @return bool|DateInterval
     */
    public function diff($datetime2, $absolute = false)
    {
        if (is_scalar($datetime2)) {
            if (static::isTimestamp($datetime2)) {
                $datetime2 = '@' . (string)$datetime2;
            }
            $datetime2 = new \DateTime($datetime2);
        }

        if (($interval = parent::diff($datetime2, $absolute)) === false) {
            return false;
        }

        $sign = $absolute ? parent::diff($datetime2)->invert : $interval->invert;
        $selfinterval = new DateInterval("P{$interval->y}Y{$interval->m}M{$interval->d}DT{$interval->h}H{$interval->i}M{$interval->s}S");
        $days = $selfinterval->total_days = $interval->days;
        $selfinterval->invert = $interval->invert;

        $selfinterval->total_seconds = $datetime2->getTimestamp() - $this->getTimestamp();
        $selfinterval->total_minutes = (int)floor($selfinterval->total_seconds / 60);
        $selfinterval->total_hours = (int)floor($selfinterval->total_seconds / (60 * 60));
        $selfinterval->total_weeks = (int)floor($days / 7);
        $daysMonth = $sign ? $datetime2->format('t') : $this->format('t');
        $selfinterval->total_months = (int)floor($days / $daysMonth);

        return $selfinterval;
    }

    /**
     * Returns formatting date.
     *
     * ```php
     * $datetime = new DateTime();
     * $datetime->addFormat('shortDate', 'd/m');
     * $datetime->shortDate();
     * ```
     * @param string $name
     * @param $params
     * @throws DateException
     * @return string
     */
    public function __call($name, $params)
    {
        $name = $this->getFormat($name);
        if (!$name) {
            throw new DateException("There is no method or format with name: {$name}");
        }
        return $this->format($name);
    }

    /**
     * Conversion in accordance with the client.
     *
     * @param string|DateTimeZone $timezone
     * @return $this|\DateTime
     */
    public function convertTimezone($timezone)
    {
        return parent::setTimezone($this->calculateTimezone($timezone));
    }

    /**
     * Validate is date.
     *
     * @param string|int $date
     * @return bool
     */
    public static function is($date)
    {
        if (is_bool($date) || empty($date) xor ($date === 0 || $date === '0')) {
            return false;
        }
        $date = static::isTimestamp($date) ? '@' . (string)$date : $date;
        return (bool)date_create($date);
    }

    /**
     * Validate is timestamp.
     *
     * @param string|int $timestamp
     * @return bool
     */
    public static function isTimestamp($timestamp)
    {
        if (is_bool($timestamp) || !is_scalar($timestamp)) {
            return false;
        }
        return ((string)(int)$timestamp === (string)$timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
    }

    /**
     * Returns microtime.
     * @param int|null $microtime
     * @return float
     */
    public static function microtime($microtime = null)
    {
        list($usec, $sec) = explode(" ", $microtime ?: microtime());
        return (float)$usec + (float)$sec;
    }

    /**
     * Returns  millisecond.
     * @return float
     */
    public static function millitime()
    {
        return round(static::microtime() * 1000);
    }

    protected function addSign($sign, $value)
    {
        return !$sign ? (int)$value : (int)$value * -1;
    }

    /** @var \DateTimezone[] */
    protected static $timezonesInstances = [];

    /**
     * Returns {@see \DateTimezone} by timezone.
     *
     * @param string|\DateTimezone $timezone
     * @return \DateTimezone|null
     */
    protected function calculateTimezone($timezone)
    {
        if (!isset($timezone)) {
            return null;
        }
        $key = $timezone instanceof \DateTimeZone ? $timezone->getName() : $timezone;
        // lazy loading
        if (!isset(static::$timezonesInstances[$key])) {
            static::$timezonesInstances[$key] = is_string($timezone) ? new \DateTimezone($timezone) : $timezone;
        }
        return static::$timezonesInstances[$key];
    }

    protected function formatDatetimeObject($format)
    {
        if ($format instanceof \Closure) {
            return call_user_func($format, $this);
        }
        $format = $this->getFormat($format) ?: $format;

        if ($format instanceof \Closure) {
            return call_user_func($format, $this);
        }
        $isStashed = $this->stashFormatOptions($format);
        $result = parent::format($format);
        if ($isStashed) {
            $this->calculateFormatOptions($result);
        }
        return $result;
    }

    protected function stashFormatOptions(&$format)
    {
        $format = str_replace(static::$formatOptionsNames, static::$formatOptionsPlaceholders, $format, $count);
        return (bool)$count;
    }

    protected function calculateFormatOptions(&$format)
    {
        $formatOptionsCallbacks = static::$formatOptionsHandlers;
        $format = preg_replace_callback('/~(\d+)~/', function ($matches) use ($formatOptionsCallbacks) {
            return call_user_func($formatOptionsCallbacks[$matches[1]], $this);
        }, $format);
    }

    protected function defaultFormatOptions()
    {
        return [
            'F' => function (DateTime $datetime) {
                return $datetime->getLocale()->getMonth($datetime->format('n') - 1);
            },
            'M' => function (DateTime $datetime) {
                return $datetime->getLocale()->getShortMonth($datetime->format('n') - 1);
            },
            'l' => function (DateTime $datetime) {
                return $datetime->getLocale()->getWeekDay($datetime->format('N') - 1);
            },
            'D' => function (DateTime $datetime) {
                return $datetime->getLocale()->getShortWeekDay($datetime->format('N') - 1);
            },
            'ago' => function (DateTime $datetime) {
                $diff = $datetime->diff(new DateTime());
                $locale = $datetime->getLocale();
                if ($diff->y >= 1) {
                    $number = $diff->y;
                    $names = $locale->getYearNames();
                } elseif ($diff->total_months >= 1) {
                    $number = $diff->total_months;
                    $names = $locale->getMonthNames();
                } elseif ($diff->total_days >= 7) {
                    $number = $diff->total_weeks;
                    $names = $locale->getWeekNames();
                } elseif ($diff->total_days >= 1) {
                    $number = $diff->total_days;
                    $names = $locale->getDayNames();
                } elseif ($diff->total_hours >= 1) {
                    $number = $diff->total_hours;
                    $names = $locale->getHourNames();
                } elseif ($diff->total_minutes >= 1) {
                    $number = $diff->total_minutes;
                    $names = $locale->getMinuteNames();
                } else {
                    $number = $diff->total_seconds;
                    $names = $locale->getSecondNames();
                }
                $options = $locale->getOptions();
                if (isset($options['ago'])) {
                    return StringHelper::replace(
                        $options['ago'],
                        ['number' => $number, 'name' => Inflector::plural($number, $names)]
                    );
                }
                return $number . ' ' . Inflector::plural($number, $names) . ' ago';
            }
        ];
    }

    protected function defaultLocales()
    {
        return [
            'en' => En::className(),
            'en-us' => En::className(),
            'en-uk' => EnUK::className(),
            'ru' => Ru::className(),
            'ru-ru' => Ru::className(),
            'ua' => Ua::className(),
        ];
    }

    public function __toString()
    {
        return $this->format();
    }
}