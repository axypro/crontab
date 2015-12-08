<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\tests;

use axy\crontab\Setter;

/**
 * coversDefaultClass axy\crontab\Setter
 */
class SetterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (strtolower(substr(PHP_OS, 0, 3) === 'win')) {
            $this->markTestSkipped('Windows');
        }
    }

    /**
     * covers ::get
     */
    public function testGet()
    {
        $setter = new Setter(__DIR__.'/emu/crontab.php');
        $fn = __DIR__.'/emu/tmp/user.txt';
        file_put_contents($fn, "1\n2\n");
        $this->assertSame("1\n2\n", $setter->get('user'));
        unlink($fn);
        $this->assertSame('', $setter->get('user'));
    }

    /**
     * covers :;set
     */
    public function testSet()
    {
        $setter = new Setter(__DIR__.'/emu/crontab.php');
        $fn = __DIR__.'/emu/tmp/root.txt';
        if (is_file($fn)) {
            unlink($fn);
        }
        $setter->set("3\n4\n", 'root');
        $this->assertFileExists($fn);
        $this->assertSame(file_get_contents($fn), "3\n4\n");
        unlink($fn);
    }

    /**
     * covers ::getSystemInstance
     */
    public function testGetSystemInstance()
    {
        $instance = Setter::getSystemInstance();
        $this->assertInstanceOf('axy\crontab\Setter', $instance);
        $this->assertSame($instance, Setter::getSystemInstance());
    }

    /**
     * covers ::get
     * covers ::set
     */
    public function testDefaultUser()
    {
        $setter = new Setter(__DIR__.'/emu/crontab.php');
        $fn = __DIR__.'/emu/tmp/default.txt';
        if (is_file($fn)) {
            unlink($fn);
        }
        $setter->set('Crontab');
        $this->assertFileExists($fn);
        $this->assertSame('Crontab', $setter->get());
        unlink($fn);
    }
}
