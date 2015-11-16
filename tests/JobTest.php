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
        $job = Job::createFromString("*/5 *\t*   2 *\t\tcommand > /dev/null");
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
}
