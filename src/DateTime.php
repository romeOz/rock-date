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
use rock\di\Container;
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

    const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    /**
     * Default format: {@see \rock\date\DateTime::DEFAULT_FORMAT}.
     * @var string
     */
    public $format = self::DEFAULT_FORMAT;
    /**
     * Current locale.
     * @var string
     */
    public $locale = 'en';
    /**
     * List locales.
     * @var array
     */
    public $locales = [];
    public $formats = [];
    protected $defaultFormats = [
        self::USER_DATE_FORMAT => 'm/d/Y',
        self::USER_TIME_FORMAT => 'g:i A',
        self::USER_DATETIME_FORMAT => 'm/d/Y g:i A',
        self::ISO_DATE_FORMAT => 'Y-m-d',
        self::ISO_TIME_FORMAT => 'H:i:s',
        self::ISO_DATETIME_FORMAT => 'Y-m-d H:i:s',
        self::JS_FORMAT => self::RFC1123,
        self::W3C_FORMAT=> self::W3C,

    ];
    /** @var Locale[] */
    protected static $localeInstances;
    /** @var \DateTimezone[] */
    protected static $timezonesInstances = [];
    protected static $formatOptionsNames = [];
    protected static $formatOptionsPlaceholders = [];
    protected static $formatOptionsCallbacks = [];

    /**
     * @param string|int          $time
     * @param string|\DateTimeZone $timezone
     * @param array               $config
     */
    public function __construct($time = 'now', $timezone = null, array $config = [])
    {
        if (static::isTimestamp($time)) {
            $time = '@' . (string)$time;
        }
        $this->parentConstruct($config);

        parent::__construct($time, $this->calculateTimezone($timezone));

        $this->locale = strtolower($this->locale);
        $this->formats = array_merge($this->defaultFormats, $this->formats);
        $this->locales = array_merge($this->defaultLocales(), $this->locales);

        foreach ($this->defaultOption() as $alias => $callback) {
            $this->addFormatOption($alias, $callback);
        }
    }

    /**
     * Modify date.
     *
     * @param string|int $time    time for modify
     * @param string|\DateTimeZone        $timezone
     * @param array       $config  the configuration. It can be either a string representing the class name
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
            $config['class'] = self::className();
            return Container::load($time, $timezone, $config);
        }
        return new static($time, $timezone, $config);
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
            $format = $this->format;
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
        $name = $this->getCustomFormat($name);
        if(!$name) {
            throw new DateException("There is no method or format with name: {$name}");
        }
        return $this->format($name);
    }

    /**
     * Set default format. Default {@see \rock\date\DateTime::DEFAULT_FORMAT}.
     * @param $format
     * @return $this
     * @see \rock\date\DateTime::$format
     */
    public function defaultFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Adds custom format.
     * @param string $name
     * @param string $format
     */
    public function addCustomFormat($name, $format)
    {
        $this->formats[$name] = $format;
    }

    /**
     * Return custom format by name.
     * @param $name string
     * @return string|null
     */
    public function getCustomFormat($name)
    {
        if (isset($this->formats[$name])) {
            return $this->formats[$name];
        }

        return null;
    }

    /**
     * Return list custom formats.
     * @return array
     */
    public function getCustomFormats()
    {
        return $this->formats;
    }

    /**
     * Adds option.
     * @param string $name
     * @param callable $callback
     * ```function (DataTime $dataTime) {}```
     * @throws DateException
     */
    public function addFormatOption($name, callable $callback)
    {
        if (in_array($name, static::$formatOptionsNames)) {
            return;
        }
        static::$formatOptionsNames[] = $name;
        static::$formatOptionsPlaceholders[] = '~' . count(static::$formatOptionsPlaceholders) . '~';
        static::$formatOptionsCallbacks[] = $callback;
    }

    /**
     * Returns instance i18n locale.
     * @return Locale
     */
    public function getLocale()
    {
        // lazy loading
        if (empty(static::$localeInstances[$this->locale])) {
            if (!isset($this->locales[$this->locale])) {
                $this->locale = 'en';
            }
            static::$localeInstances[$this->locale] = new $this->locales[$this->locale];
        }

        return static::$localeInstances[$this->locale];
    }

    /**
     * Set locale.
     * @param string $locale
     * @return $this
     * @see \rock\date\DateTime::$locale
     */
    public function locale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Returns the difference between two DateTime objects.
     * @param string|int|\DateTime $datetime2
     * @param bool      $absolute
     * @return bool|\DateInterval
     */
    public function diff($datetime2, $absolute = false)
    {
        if (is_scalar($datetime2)) {
            if (static::isTimestamp($datetime2)) {
                $datetime2 = '@' . (string)$datetime2;
            }
            $datetime2 = new \DateTime($datetime2);
        }

        if (($interval = parent::diff($datetime2, $absolute)) === false){
            return false;
        }

        $sign = $absolute ? parent::diff($datetime2)->invert : $interval->invert;
        $days = $interval->days;
        $interval->s = $datetime2->getTimestamp() - $this->getTimestamp();
        $interval->i = $this->addSign($interval->invert, floor($interval->s / 60));
        $interval->h = $this->addSign($interval->invert, floor($interval->s / (60 * 60)));
        $interval->d = $this->addSign($interval->invert, $days);
        $interval->w = $this->addSign($interval->invert, floor($days / 7));
        $daysMonth = $sign ? $datetime2->format('t') : $this->format('t');
        $interval->m = $this->addSign($interval->invert, floor($days / $daysMonth));
        $interval->y = $this->addSign($interval->invert, $interval->y);
        $interval->s = $this->addSign($interval->invert, $interval->s);

        return $interval;
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
        list($usec, $sec) = explode(" ", $microtime ? : microtime());
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
        if(!isset(static::$timezonesInstances[$key])) {
            static::$timezonesInstances[$key] = is_string($timezone) ? new \DateTimezone($timezone) : $timezone;
        }
        return static::$timezonesInstances[$key];
    }

    protected function formatDatetimeObject($format)
    {
        if ($format instanceof \Closure) {
            return call_user_func($format, $this);
        }
        $format = $this->getCustomFormat($format) ? : $format;

        if ($format instanceof \Closure) {
            return call_user_func($format, $this);
        }
        $isStashed = $this->stashCustomFormatOptions($format);
        $result = parent::format($format);
        if($isStashed) {
            $this->applyCustomFormatOptions($result);
        }
        return $result;
    }

    protected function stashCustomFormatOptions(&$format)
    {
        $format = str_replace(static::$formatOptionsNames, static::$formatOptionsPlaceholders, $format, $count);
        return (bool)$count;
    }

    protected function applyCustomFormatOptions(&$format)
    {
        $formatOptionsCallbacks = static::$formatOptionsCallbacks;
        $format = preg_replace_callback('/~(\d+)~/', function ($matches) use ($formatOptionsCallbacks) {
            return call_user_func($formatOptionsCallbacks[$matches[1]], $this);
        }, $format);
    }

    protected function defaultOption()
    {
        return [
            'F' => function(DateTime $datetime){
                return $datetime->getLocale()->getMonth($datetime->format('n') - 1);
            },
            'M' => function(DateTime $datetime){
                return $datetime->getLocale()->getShortMonth($datetime->format('n') - 1);
            },
            'l' => function(DateTime $datetime){
                return $datetime->getLocale()->getWeekDay($datetime->format('N') - 1);
            },
            'D' => function(DateTime $datetime){
                return $datetime->getLocale()->getShortWeekDay($datetime->format('N') - 1);
            },
            'ago' => function(DateTime $datetime){
                $diff = $datetime->diff(new DateTime());
                $locale = $datetime->getLocale();
                if ($diff->y >= 1) {
                    $number = $diff->y;
                    $names = $locale->getYearNames();
                } elseif ($diff->m >= 1) {
                    $number = $diff->m;
                    $names = $locale->getMonthNames();
                } elseif ($diff->d >= 7) {
                    $number = $diff->w;
                    $names = $locale->getWeekNames();
                } elseif ($diff->d >= 1) {
                    $number = $diff->d;
                    $names = $locale->getDayNames();
                } elseif ($diff->h >= 1) {
                    $number = $diff->h;
                    $names = $locale->getHourNames();
                } elseif ($diff->i >= 1) {
                    $number = $diff->i;
                    $names = $locale->getMinuteNames();
                } else {
                    $number = $diff->s;
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