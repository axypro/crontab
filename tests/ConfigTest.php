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
}
