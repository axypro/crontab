<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\tests;

use axy\crontab\JobList;
use axy\crontab\Job;

/**
 * coversDefaultClass axy\crontab\JobList
 */
class JobListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * covers ::append
     * covers ::count
     * covers ::getIterator
     */
    public function testAppend()
    {
        $list = new JobList();
        $this->assertCount(0, $list);
        $job1 = Job::createFromString('* * * * * one');
        $list->append($job1);
        $list->append('1 2 3 4 5 two');
        $this->assertCount(2, $list);
        $array = iterator_to_array($list);
        $this->assertCount(2, $array);
        $this->assertSame($job1, $array[0]);
        /** @var \axy\crontab\Job $job2 */
        $job2 = $array[1];
        $this->assertInstanceOf('axy\crontab\Job', $job2);
        $this->assertSame('two', $job2->command);
    }

    /**
     * covers ::getContent
     */
    public function testGetContent()
    {
        $list = new JobList();
        $list->append(Job::createFromString('1 2 3 4 5 one'));
        $list->append(Job::createFromString('* 1/2 * 3 * sun two'));
        $expected = "1 2 3 4 5 one\n* 1/2 * 3 * sun two\n";
        $this->assertSame($expected, $list->getContent());
    }

    public function testConstructor()
    {
        $one = '1 2 3 4 5 one';
        $two = Job::createFromString('2 3 4 5 6 two');
        $list = new JobList([$one, $two]);
        $list2 = new JobList($list);
        $list2->append('7 8 9 0 1 three');
        $expected = "1 2 3 4 5 one\n2 3 4 5 6 two\n7 8 9 0 1 three\n";
        $this->assertSame($expected, $list2->getContent());
    }

    /**
     * covers ::createFromContent
     * covers ::createFromFile
     */
    public function testLoad()
    {
        $list = JobList::createFromFile(__DIR__.'/data/crontab.txt');
        $expected = "* * * * * every > /dev/null\n* 2 * 3 * command\n";
        $this->assertSame($expected, $list->getContent());
    }

    /**
     * covers ::check
     */
    public function testCheck()
    {
        $job1 = Job::createFromString('* 2 * 3 * one');
        $job2 = Job::createFromString('3-5 * * * * two');
        $list = new JobList([$job1, $job2]);
        $this->assertEmpty($list->check(strtotime('2015-11-17 00:00:00')));
        $this->assertEquals([$job2], $list->check(strtotime('2015-03-17 00:04:00')));
        $this->assertEquals([$job1, $job2], $list->check(strtotime('2015-03-17 02:04:00')));
        $this->assertEquals([$job1], $list->check(strtotime('2015-03-17 02:06:00')));
    }

    /**
     * @expectedException \axy\crontab\errors\InvalidJob
     */
    public function testError()
    {
        JobList::createFromFile(__DIR__.'/data/invalid-crontab.txt');
    }
}
