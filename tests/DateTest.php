<?php

namespace rockunit;


use rock\date\DateTime;
use rock\date\DateException;
use rock\date\locale\Ru;
use rock\helpers\Inflector;

/**
 * @group base
 */
class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerData
     */
    public function testGetTimestamp($time)
    {
        $this->assertSame((new DateTime($time))->getTimestamp(), 595296000);
        $this->assertSame(DateTime::set($time)->getTimestamp(), 595296000);
    }

    public function providerData()
    {
        return [
            ['1988-11-12'],
            [595296000],
            ['595296000']
        ];
    }

    public function testFormat()
    {
        $this->assertSame(date('j  n  Y'), (new DateTime)->format('j  n  Y'));
        $this->assertSame(date('j  n  Y'), DateTime::set(null)->format('j  n  Y'));
        $this->assertSame(date('Y-m-d H:i:s'), (new DateTime)->format());
        $this->assertSame(date('Y-m-d H:i:s'), DateTime::set()->format());
    }

    public function testDefaultFormat()
    {
        $this->assertSame(date('Y-m-d'), (new DateTime)->isoDate());
        $this->assertSame(date('H:i:s'), (new DateTime)->isoTime());
        $this->assertSame(date('Y-m-d H:i:s'), (new DateTime)->isoDatetime());

        // set default format
        $dateTime = new DateTime;
        $dateTime->defaultFormat('j  n  Y');
        $this->assertSame(date('j  n  Y'), $dateTime->format());

        // unknown format
        $this->setExpectedException(DateException::className());
        (new DateTime)->unknown();
    }

    public function testLocal()
    {
        $dateTime = new DateTime('1988-11-12');
        $dateTime->locale('ru');
        $this->assertSame($dateTime->format('j  F  Y'), '12  ноября  1988');
        $this->assertSame($dateTime->format('j  M  Y'), '12  ноя  1988');
        $this->assertSame($dateTime->format('j  l  Y'), '12  суббота  1988');
        $this->assertSame($dateTime->format('j  D  Y'), '12  Сб  1988');
        $this->assertTrue($dateTime->getLocale() instanceof Ru);

        $this->assertNotEmpty($dateTime->getLocale()->getFormats());
        $this->assertNotEmpty($dateTime->getLocale()->getMonths());
        $this->assertNotEmpty($dateTime->getLocale()->getWeekDays());
        $this->assertNotEmpty($dateTime->getLocale()->getShortWeekDays());

        // unknown
        $dateTime->locale('unknown');
        $this->assertSame($dateTime->format('j  F  Y'), '12  November  1988');
    }

    public function testAgo()
    {
        $y = DateTime::set('2012-01-24')->diff(DateTime::set())->y;
        $name = Inflector::plural($y, (new Ru())->getYearNames());
        $this->assertSame("{$y} {$name} назад", DateTime::set('2012-01-24')->locale('ru')->format('ago'));
    }

    public function testAddCustomFormat()
    {
        $datetime = DateTime::set('1988-11-12');
        $datetime->addCustomFormat('shortDate', 'j / F / Y');
        $this->assertSame('12 / November / 1988', $datetime->shortDate());
        $this->assertArrayHasKey('shortDate', $datetime->getCustomFormats());
    }

    public function testAddFormatOption()
    {
        $datetime = new DateTime('1988-11-12');
        $datetime->addFormatOption('go', function (DateTime $datetime) {
            return floor((time() - $datetime->getTimestamp()) / 86400) . ' days ago';
        });
        $ago = floor((time() - $datetime->getTimestamp()) / 86400);
        $this->assertSame("12 November 1988, {$ago} days ago", $datetime->format('d F Y, go'));

        // duplicate
        $datetime->addFormatOption('go', function (DateTime $datetime) {
            return floor((time() - $datetime->getTimestamp()) / 86400) . ' days ago';
        });
    }

    public function testDiff()
    {
        $dateTime = new DateTime('1988-11-12');
        $this->assertSame($dateTime->diff(time())->w, (int)floor($dateTime->diff(time())->days / 7));

        $dateInterval = $dateTime->diff('1988-11-12');
        $this->assertSame($dateInterval->w, (int)floor($dateInterval->days / 7) * -1);

        $dateInterval = $dateTime->diff('1988-11-12', true);
        $this->assertSame($dateInterval->w, (int)floor($dateInterval->days / 7));

        $diff = (new DateTime('2015-02-01 00:00:00'))->diff(new DateTime('2015-03-01 00:00:00'));
        $this->assertSame($diff->m, 1);
        $this->assertSame($diff->d, 28);

        $diff = (new DateTime('2015-03-01 00:00:00'))->diff(new DateTime('2015-02-01 00:00:00'));
        $this->assertSame($diff->m, -1);
        $this->assertSame($diff->d, -28);

        $diff = (new DateTime('2015-03-01 00:00:00'))->diff(new DateTime('2015-02-01 00:00:00'), true);
        $this->assertSame($diff->m, 1);
        $this->assertSame($diff->d, 28);

        $diff = (new DateTime('2015-01-01 00:00:00'))->diff(new DateTime('2015-02-01 00:00:00'));
        $this->assertSame($diff->m, 1);
        $this->assertSame($diff->d, 31);

        $diff = (new DateTime('2015-02-01 00:00:00'))->diff(new DateTime('2015-01-01 00:00:00'));
        $this->assertSame($diff->m, -1);
        $this->assertSame($diff->d, -31);

        $diff = (new DateTime('2015-02-01 00:00:00'))->diff(new DateTime('2015-01-01 00:00:00'), true);
        $this->assertSame($diff->m, 1);
        $this->assertSame($diff->d, 31);
    }

    /**
     * @dataProvider providerIsTrue
     */
    public function testIsDateTrue($value)
    {
        $this->assertTrue(DateTime::is($value));
    }

    public function providerIsTrue()
    {
        return [
            ['1988-11-12'],
            ['595296000'],
            ['-595296000'],
            [595296000],
            [-595296000],
            [3.14],
            ['3.14']
        ];
    }

    /**
     * @dataProvider providerIsFalse
     */
    public function testIsDateFalse($value)
    {
        $this->assertFalse(DateTime::is($value));
    }

    public function providerIsFalse()
    {
        return [
            ['foo'],
            [''],
            [null],
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider providerIsTimestampTrue
     */
    public function testIsTimestampTrue($value)
    {
        $this->assertTrue(DateTime::isTimestamp($value));
    }

    public function providerIsTimestampTrue()
    {
        return [
            ['595296000'],
            ['-595296000'],
            [595296000],
            [-595296000],
        ];
    }

    /**
     * @dataProvider providerIsTimestampFalse
     */
    public function testIsTimestampFalse($value)
    {
        $this->assertFalse(DateTime::isTimestamp($value));
    }

    public function providerIsTimestampFalse()
    {
        return [
            ['foo'],
            [''],
            [null],
            [true],
            [false],
            ['1988-11-12'],
            ['3.14'],
            [3.14],
        ];
    }

    public function testTimezone()
    {
        $this->assertNotEquals(
            (new DateTime('now', 'America/Chicago'))->isoDatetime(),
            (new DateTime('now', new \DateTimeZone('Europe/Volgograd')))->isoDatetime()
        );

        $this->assertNotEquals(
            (new DateTime('2008-12-02 10:21:00'))->convertTimezone('America/Chicago')->isoDatetime(),
            (new DateTime('2008-12-02 10:21:00'))->convertTimezone(new \DateTimeZone('Europe/Volgograd'))->isoDatetime()
        );
    }

    public function testMicrotime()
    {
        $this->assertInternalType('float', DateTime::microtime());
        $this->assertSame(1422608086.944284, DateTime::microtime('0.94428400 1422608086'));
    }

    public function testMillitime()
    {
        $this->assertInternalType('float', DateTime::millitime());
    }
}