<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\tests;

use axy\crontab\helpers\Insert;

/**
 * coversDefaultClass axy\crontab\helpers\Insert
 */
class InsertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * covers ::insertContent
     */
    public function testInsertContentOk()
    {
        $original = [
            '# comment',
            '* * * * * one',
            '* * * * * two',
            '',
            '# begin site',
            '* * * * * old',
            '* * * * * old2',
            '# end site',
            '',
            '* * * * end',
        ];
        $content = [
            '* * * * * new',
            '* * * * * new2',
        ];
        $expected = [
            '# comment',
            '* * * * * one',
            '* * * * * two',
            '',
            '# begin site',
            '* * * * * new',
            '* * * * * new2',
            '# end site',
            '',
            '* * * * end',
        ];
        $original = implode("\n", $original)."\n";
        $content = implode("\n", $content);
        $expected = implode("\n", $expected)."\n";
        $this->assertSame($expected, Insert::insertContent($original, $content, 'site'));
    }

    /**
     * covers ::insertContent
     */
    public function testInsertContentCut()
    {
        $original = [
            '# comment',
            '* * * * * one',
            '* * * * * two',
            '',
            '# begin site',
            '* * * * * old',
            '* * * * * old2',
        ];
        $content = [
            '* * * * * new',
            '* * * * * new2',
        ];
        $expected = [
            '# comment',
            '* * * * * one',
            '* * * * * two',
            '',
            '# begin site',
            '* * * * * new',
            '* * * * * new2',
            '# end site',
        ];
        $original = implode("\n", $original)."\n";
        $content = implode("\n", $content);
        $expected = implode("\n", $expected)."\n";
        $this->assertSame($expected, Insert::insertContent($original, $content, 'site'));
    }

    /**
     * covers ::insertContent
     */
    public function testInsertContentMany()
    {
        $original = [
            '# comment',
            '* * * * * one',
            '* * * * * two',
            '',
            '# begin site',
            '* * * * * old',
            '* * * * * old2',
            '# end site',
            '',
            '# begin site2',
            '* * * * * old3',
            '* * * * * old4',
            '# end site2',
            '* * * * end',
        ];
        $content = [
            '* * * * * new',
            '* * * * * new2',
        ];
        $expected = [
            '# comment',
            '* * * * * one',
            '* * * * * two',
            '',
            '# begin site',
            '* * * * * old',
            '* * * * * old2',
            '# end site',
            '',
            '# begin site2',
            '* * * * * new',
            '* * * * * new2',
            '# end site2',
            '* * * * end',
        ];
        $original = implode("\n", $original)."\n";
        $content = implode("\n", $content);
        $expected = implode("\n", $expected)."\n";
        $this->assertSame($expected, Insert::insertContent($original, $content, 'site2'));
    }

    /**
     * covers ::insertContent
     */
    public function testInsertContentEnd()
    {
        $original = [
            '# comment',
            '* * * * * one',
            '* * * * * two',
        ];
        $content = [
            '* * * * * new',
            '* * * * * new2',
        ];
        $expected = [
            '# comment',
            '* * * * * one',
            '* * * * * two',
            '',
            '# begin site',
            '* * * * * new',
            '* * * * * new2',
            '# end site',
        ];
        $original = implode("\n", $original)."\n";
        $content = implode("\n", $content);
        $expected = implode("\n", $expected)."\n";
        $this->assertSame($expected, Insert::insertContent($original, $content, 'site'));
    }
}
