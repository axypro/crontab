<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\tests;

use axy\crontab\Config;

/**
 * coversDefaultClass axy\crontab\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testMerge()
    {
        $params = [
            'user' => 'git',
            'cli' => './cli',
            'jobs' => [
                'task' => 'in 10:00',
                'check' => [
                    'user' => 'root',
                    'command' => 'check one',
                    'redirect' => null,
                    'time' => '10',
                ],
            ],
        ];
        $expected = [
            'user' => 'git',
            'dir' => null,
            'cli' => './cli',
            'redirect' => '/dev/null 2>&1',
            'name' => null,
            'jobs' => [
                'task' => [
                    'full' => null,
                    'user' => 'git',
                    'command' => 'task',
                    'comment' => null,
                    'time' => '0 10 * * *',
                    'redirect' => '/dev/null 2>&1',
                ],
                'check' => [
                    'full' => null,
                    'user' => 'root',
                    'command' => 'check one',
                    'comment' => null,
                    'time' => '10 * * * *',
                    'redirect' => null,
                ],

            ],
        ];
        $config = new Config($params);
        $actual = [];
        foreach (array_keys($expected) as $key) {
            $actual[$key] = $config->$key;
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider providerInvalid
     * @param array $params
     * @expectedException \axy\errors\InvalidConfig
     * @return \axy\crontab\Config
     */
    public function testInvalid($params)
    {
        return new Config($params);
    }

    /**
     * @return array
     */
    public function providerInvalid()
    {
        return [
            [
                [
                    'dir' => [],
                ],
            ],
            [
                [
                    'jobs' => 3,
                ],
            ],
            [
                [
                    'jobs' => [
                        'x' => [
                            'full' => [],
                            'time' => 'every',
                        ],
                    ],
                ],
            ],
            'no-time' => [
                [
                    'jobs' => [
                        'x' => [
                            'full' => '',
                        ],
                    ],
                ],
            ],
            'invalid-job' => [
                [
                    'jobs' => [
                        'task' => 'in ?:?',
                    ],
                ],
            ],
        ];
    }

    /**
     * covers ::getCrontabForUser
     * covers ::getListUsers
     */
    public function testGetCrontabForUser()
    {
        $params = [
            'user' => 'git',
            'cli' => './cli',
            'dir' => '/var/www',
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
        $config = new Config($params);
        $this->assertEmpty($config->getCrontabForUser('none'));
        $this->assertEmpty($config->getCrontabForUser(null));
        $this->assertSame('0 * * * * indexer --all --rotate', $config->getCrontabForUser('root'));
        $expected = [
            '0 10 * * * cd /var/www && ./cli task > /dev/null 2>&1',
            '# Shutdown it!',
            '*/5 * * * * cd /var/www && ./cli shutdown -p now',
        ];
        $expected = implode("\n", $expected);
        $this->assertSame($expected, $config->getCrontabForUser('git'));
        $this->assertSame($expected, $config->getCrontabForUser());
        $this->assertSame(['git', 'root'], $config->getListOfUsers());
    }
}
