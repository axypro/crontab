<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\tests;

use axy\crontab\Job;

/**
 * coversDefaultClass axy\crontab\Job
 */
class JobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * covers ::__toString
     */
    public function testToString()
    {
        $job = new Job();
        $job->command = '/bin/command > /dev/null';
        $this->assertSame('* * * * * /bin/command > /dev/null', (string)$job);
        $job->minute = '*/5';
        $job->hour = '0';
        $job->dayOfWeek = 'fri';
        $this->assertSame('*/5 0 * * fri /bin/command > /dev/null', (string)$job);
        $job->dayOfMonth = '7,8';
        $job->month = 9;
        $this->assertSame('*/5 0 7,8 9 fri /bin/command > /dev/null', (string)$job);
    }

    /**
     * covers ::createFromString
     */
    public function testCreateFromString()
    {
        $job = Job::createFromString("*/5 *\t*   2 *\t\t command > /dev/null");
        $this->assertInstanceOf('axy\crontab\Job', $job);
        $expected = [
            'command' => 'command > /dev/null',
            'minute' => '*/5',
            'hour' => null,
            'dayOfMonth' => null,
            'month' => '2',
            'dayOfWeek' => null,
        ];
        $this->assertEquals($expected, (array)$job);
        $this->setExpectedException('axy\crontab\errors\InvalidJobString');
        Job::createFromString('* * *');
    }

    /**
     * covers ::check
     * @dataProvider providerCheck
     * @param string $sJob
     * @param string $date
     * @param bool $expected [optional]
     */
    public function testCheck($sJob, $date, $expected = true)
    {
        $job = Job::createFromString($sJob);
        $this->assertSame($expected, $job->check(strtotime($date)));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function providerCheck()
    {
        return [
            'every' => [
                '* * * * * cmd',
                '2015-11-16 15:00:02',
            ],
            'single' => [
                '3 * * * * cmd',
                '2015-11-16 15:00:02',
                false,
            ],
            'single-ok' => [
                '3 * * * * cmd',
                '2015-11-16 15:03:02',
            ],
            'all' => [
                '3 15 16 11 * cmd',
                '2015-11-16 15:03:02',
            ],
            'all-m' => [
                '3 15 16 11 * cmd',
                '2015-11-16 15:02:02',
                false,
            ],
            'all-h' => [
                '3 15 16 11 * cmd',
                '2015-11-16 16:03:02',
                false,
            ],
            'all-dom' => [
                '3 15 16 11 * cmd',
                '2015-11-17 15:03:02',
                false,
            ],
            'all-mon' => [
                '3 15 16 11 * cmd',
                '2015-12-16 15:03:02',
                false,
            ],
            'days' => [
                '* * 16 * 1 cmd',
                '2015-11-16 15:03:02',
            ],
            'days-or' => [
                '* * 16 * 1 cmd',
                '2015-11-23 15:03:02',
            ],
            'days-or2' => [
                '* * 16 * 1 cmd',
                '2015-11-16 15:03:02',
            ],
            'days-or2-f' => [
                '* * 16 * 1 cmd',
                '2015-11-15 15:03:02',
                false,
            ],
            'days-or-and' => [
                '* * 16 2 1 cmd',
                '2015-11-16 15:03:02',
                false,
            ],
            'range' => [
                '2-5 2 * * * cmd',
                '2015-11-16 02:03:04',
            ],
            'range-2' => [
                '2-5 2 * * * cmd',
                '2015-11-16 02:04:04',
            ],
            'range-3' => [
                '2-5 2 * * * cmd',
                '2015-11-16 02:05:04',
            ],
            'range-fm' => [
                '2-5 2 * * * cmd',
                '2015-11-16 02:06:04',
                false,
            ],
            'range-fh' => [
                '2-5 2 * * * cmd',
                '2015-11-16 03:03:04',
                false,
            ],
            'div' => [
                '*/3 * * * * cmd',
                '2015-11-16 01:03:03',
            ],
            'div-2' => [
                '*/3 * * * * cmd',
                '2015-11-16 01:00:00',
            ],
            'div-3' => [
                '*/3 * * * * cmd',
                '2015-11-16 01:33:00',
            ],
            'div-f' => [
                '*/3 * * * * cmd',
                '2015-11-16 01:34:00',
                false,
            ],
            'div-delta' => [
                '1/3 * * * * cmd',
                '2015-11-16 01:34:02',
            ],
            'div-delta-f' => [
                '1/3 * * * * cmd',
                '2015-11-16 01:33:00',
                false,
            ],
            'list' => [
                '* 1,3,5 * * * cmd',
                '2015-11-16 01:02:33',
            ],
            'list-3' => [
                '* 1,3,5 * * * cmd',
                '2015-11-16 03:02:33',
            ],
            'list-5' => [
                '* 1,3,5 * * * cmd',
                '2015-11-16 05:02:33',
            ],
            'list-4' => [
                '* 1,3,5 * * * cmd',
                '2015-11-16 04:02:33',
                false,
            ],
            'range-delta-0' => [
                '0-8/2 * * * * cmd',
                '2015-11-16 04:00:00',
            ],
            'range-delta-1' => [
                '0-8/2 * * * * cmd',
                '2015-11-16 04:01:00',
                false,
            ],
            'range-delta-2' => [
                '0-8/2 * * * * cmd',
                '2015-11-16 04:02:00',
            ],
            'range-delta-7' => [
                '0-8/2 * * * * cmd',
                '2015-11-16 04:07:00',
                false,
            ],
            'range-delta-8' => [
                '0-8/2 * * * * cmd',
                '2015-11-16 04:08:00',
            ],
            'range-delta-10' => [
                '0-8/2 * * * * cmd',
                '2015-11-16 04:10:00',
                false,
            ],
            'range-delta-2-i' => [
                '1-8/2 * * * * cmd',
                '2015-11-16 04:02:00',
                false,
            ],
            'range-delta-7-i' => [
                '1-8/2 * * * * cmd',
                '2015-11-16 04:07:00',
            ],
            'sun-0' => [
                '* * * * 0 cmd',
                '2015-11-15 04:07:00',
            ],
            /*
            'sun-7' => [
                '* * * * 7 cmd',
                '2015-11-15 04:07:00',
            ],
            */
            'sun-3' => [
                '* * * * 3 cmd',
                '2015-11-15 04:07:00',
                false,
            ],
            'dow-sun' => [
                '* * * * sun,Mon cmd',
                '2015-11-15 04:07:00',
            ],
            'dow-mon' => [
                '* * * * sun,Mon cmd',
                '2015-11-16 04:07:00',
            ],
            'dow-thu' => [
                '* * * * sun,Mon cmd',
                '2015-11-17 04:07:00',
                false,
            ],
            'dom-jan' => [
                '* * * jan * cmd',
                '2015-01-16 04:07:00',
            ],
            'dom-feb' => [
                '* * * jan * cmd',
                '2015-02-16 04:07:00',
                false,
            ],
        ];
    }
}
