<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\tests;

use axy\crontab\Crontab;

/**
 * coversDefaultClass axy\crontab\Crontab
 */
class CrontabTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (strtolower(substr(PHP_OS) === 'win')) {
            $this->markTestSkipped('Windows');
        }
    }

    /**
     * covers ::getAllUsers
     * covers ::getUser
     */
    public function testGetAllUsers()
    {
        $config = [
            'user' => 'git',
            'cli' => './cli',
            'dir' => '/var/www',
            'name' => 'example.loc',
            'jobs' => [
                'task' => 'in 10:00',
                'check' => [
                    'user' => 'root',
                    'full' => 'indexer --all --rotate',
                    'time' => 'every 1h'
                ],
                'shutdown' => [
                    'time' => 'every 5 min',
                    'redirect' => null,
                    'command' => 'shutdown -p now',
                    'comment' => 'Shutdown it!',
                ],
            ],
        ];
        $crontab = new Crontab($config);
        $actual = $crontab->getAllUsers(true);
        $this->assertInternalType('array', $actual);
        $expected = [
            'git' => [
                '# begin example.loc',
                '0 10 * * * cd /var/www && ./cli task > /dev/null 2>&1',
                '# Shutdown it!',
                '*/5 * * * * cd /var/www && ./cli shutdown -p now',
                '# end example.loc',
            ],
            'root' => [
                '# begin example.loc',
                '0 * * * * indexer --all --rotate',
                '# end example.loc',
            ],
        ];
        foreach ($expected as &$e) {
            $e = implode("\n", $e);
        }
        unset($e);
        $this->assertEquals($expected, $actual);
        $this->assertSame('0 * * * * indexer --all --rotate', $crontab->getUser('root', false));
    }

    /**
     * covers ::save
     */
    public function testSave()
    {
        $fnGit = __DIR__.'/emu/tmp/git.txt';
        $fnRoot = __DIR__.'/emu/tmp/root.txt';
        copy(__DIR__.'/data/old-git.txt', $fnGit);
        if (is_file($fnRoot)) {
            unlink($fnRoot);
        }
        $config = [
            'user' => 'git',
            'cli' => './cli',
            'dir' => '/var/www',
            'name' => 'example.loc',
            'jobs' => [
                'task' => 'in 10:00',
                'check' => [
                    'user' => 'root',
                    'full' => 'indexer --all --rotate',
                    'time' => 'every 1h'
                ],
                'shutdown' => [
                    'time' => 'every 5 min',
                    'redirect' => null,
                    'command' => 'shutdown -p now',
                    'comment' => 'Shutdown it!',
                ],
            ],
            '_cmd_crontab' => __DIR__.'/emu/crontab.php',
        ];
        $crontab = new Crontab($config);
        $crontab->save();
        $this->assertFileExists($fnGit);
        $this->assertFileExists($fnRoot);
        $this->assertFileEquals(__DIR__.'/data/new-git.txt', $fnGit);
        $this->assertFileEquals(__DIR__.'/data/new-root.txt', $fnRoot);
    }

    public function testSaveNoRoot()
    {
        $fnGit = __DIR__.'/emu/tmp/default.txt';
        $fnRoot = __DIR__.'/emu/tmp/root.txt';
        copy(__DIR__.'/data/old-git.txt', $fnGit);
        if (is_file($fnRoot)) {
            unlink($fnRoot);
        }
        $config = [
            'cli' => './cli',
            'dir' => '/var/www',
            'name' => 'example.loc',
            'jobs' => [
                'task' => 'in 10:00',
                'check' => [
                    'user' => 'root',
                    'full' => 'indexer --all --rotate',
                    'time' => 'every 1h'
                ],
                'shutdown' => [
                    'time' => 'every 5 min',
                    'redirect' => null,
                    'command' => 'shutdown -p now',
                    'comment' => 'Shutdown it!',
                ],
            ],
            '_cmd_crontab' => __DIR__.'/emu/crontab.php',
        ];
        $crontab = new Crontab($config);
        $crontab->save(false);
        $this->assertFileExists($fnGit);
        $this->assertFileNotExists($fnRoot);
        $this->assertFileEquals(__DIR__.'/data/new-git.txt', $fnGit);
    }

    /**
     * covers ::save
     */
    public function testSaveRootNoDefault()
    {
        $config = [
            'cli' => './cli',
            'dir' => '/var/www',
            'name' => 'example.loc',
            'jobs' => [
                'task' => 'in 10:00',
                'check' => [
                    'user' => 'root',
                    'full' => 'indexer --all --rotate',
                    'time' => 'every 1h'
                ],
                'shutdown' => [
                    'time' => 'every 5 min',
                    'redirect' => null,
                    'command' => 'shutdown -p now',
                    'comment' => 'Shutdown it!',
                ],
            ],
            '_cmd_crontab' => __DIR__.'/emu/crontab.php',
        ];
        $crontab = new Crontab($config);
        $this->setExpectedException('axy\errors\InvalidConfig');
        $crontab->save(true);
    }

    /**
     * covers ::save
     */
    public function testSaveNotModified()
    {
        $fnGit = __DIR__.'/emu/tmp/git.txt';
        if (is_file($fnGit)) {
            unlink($fnGit);
        }
        $fnChange = __DIR__.'/emu/tmp/change';
        $config = [
            'user' => 'git',
            'cli' => './cli',
            'dir' => '/var/www',
            'name' => 'example.loc',
            'jobs' => [
                'task' => 'in 10:00',
                'shutdown' => [
                    'time' => 'every 5 min',
                    'redirect' => null,
                    'command' => 'shutdown -p now',
                    'comment' => 'Shutdown it!',
                ],
            ],
            '_cmd_crontab' => __DIR__.'/emu/crontab.php',
        ];
        $crontab = new Crontab($config);
        if (is_file($fnChange)) {
            unlink($fnChange);
        }
        $this->assertTrue($crontab->save());
        $this->assertFileExists($fnChange);
        $crontab2 = new Crontab($config);
        unlink($fnChange);
        $this->assertFalse($crontab2->save());
        $this->assertFileNotExists($fnChange);
    }
}
