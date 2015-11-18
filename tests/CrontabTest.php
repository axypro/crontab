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
}
