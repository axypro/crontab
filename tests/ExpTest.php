<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\tests;

use axy\crontab\Exp;

/**
 * coversDefaultClass axy\crontab\Exp
 */
class ExpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * covers ::create
     * @dataProvider providerCreate
     * @param string $exp
     * @param string $expected
     */
    public function testCreate($exp, $expected)
    {
        if ($expected === null) {
            $this->setExpectedException('axy\crontab\errors\InvalidJobString');
        }
        $actual = Exp::create($exp);
        if ($expected !== null) {
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * @return array
     */
    public function providerCreate()
    {
        $expressions = [
            '1 2 3 4/5 6' => '1 2 3 4/5 6',
            '* *   * 3 *' => '* * * 3 *',
            "\t1 \t 2 3\t" => '1 2 3 * *',
            '1 2 3   4 5 6' => null,
            '' => '* * * * *',
            'every' => '* * * * *',
            'every 1m' => '* * * * *',
            'every 5m' => '*/5 * * * *',
            'every 5   minutes + 2' => '2/5 * * * *',
            'every 3h' => '0 */3 * * *',
            'every 3 hour offset 2' => '0 2/3 * * *',
            'every 3 hour + 2 in 10m' => '10 2/3 * * *',
            'every 4 hours in 15 minutes' => '15 */4 * * *',
            'in 9:00' => '0 9 * * *',
            'in 23:11' => '11 23 * * *',
            'in 33:11' => null,
            'in' => null,
            'invalid' => null,
            'every 1 day' => null,
            'every 1m +' => null,
            'every 1m in 2h' => null,
            'every 2h not in' => null,
            'every 2h in' => null,
            'every 2h in 2h h' => null,
            'every 25h' => null,
            'every 63m' => null,
            'every 1' => null,
            'every x' => null,
            'in 25:00' => null,
            'in 09:63' => null,
        ];
        $provider = [];
        foreach ($expressions as $exp => $expected) {
            $provider[] = [$exp, $expected];
        }
        return $provider;
    }
}
