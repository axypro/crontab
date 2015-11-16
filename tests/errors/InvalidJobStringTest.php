<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\tests;

use axy\crontab\errors\InvalidJobString;

/**
 * coversDefaultClass axy\crontab\errors\InvalidJobString
 */
class InvalidJobStringTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $e = new InvalidJobString('* * cmd');
        $this->assertInstanceOf('axy\crontab\errors\InvalidJob', $e);
        $this->assertSame('* * cmd', $e->getJob());
        $this->assertSame('Cron job "* * cmd" has invalid format', $e->getMessage());
    }
}
