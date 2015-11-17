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
     * @param int $time
     * @param bool $expected [optional]
     */
    public function testCheck($sJob, $time, $expected = true)
    {
        $job = Job::createFromString($sJob);
        $this->assertSame($expected, $job->check($time));
    }

    /**
     * @return array
     */
    public function providerCheck()
    {
        $provider = [];
        foreach (file(__DIR__.'/data/check.txt') as $num => $line) {
            $line = trim($line);
            $first = substr($line, 0, 1);
            if ($first === '+') {
                $expected = true;
            } elseif ($first === '-') {
                $expected = false;
            } else {
                continue;
            }
            $line = explode(';', substr($line, 1), 3);
            if (count($line) !== 3) {
                continue;
            }
            $job = trim($line[0]).' command';
            $date = strtotime(trim($line[1]));
            $key = trim($line[2]);
            if (isset($provider[$key])) {
                $key .= '#'.$num;
            }
            $provider[$key] = [$job, $date, $expected];
        }
        return $provider;
    }
}
