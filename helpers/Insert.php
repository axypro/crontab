<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\helpers;

/**
 * Insert content in file
 */
class Insert
{
    /**
     * @param string $original
     * @param string $content
     * @param string $separator
     * @return string
     */
    public static function insertContent($original, $content, $separator)
    {
        $stage = 'before';
        $result = [];
        $begin = '# begin '.$separator;
        $end = '# end '.$separator;
        foreach (explode("\n", $original) as $line) {
            switch ($stage) {
                case 'before':
                    $result[] = $line;
                    if (trim($line) === $begin) {
                        $stage = 'in';
                        $result[] = $content;
                    }
                    break;
                case 'in':
                    if (trim($line) === $end) {
                        $result[] = $line;
                        $stage = 'after';
                    }
                    break;
                case 'after':
                    $result[] = $line;
                    break;
            }
        }
        if ($stage === 'in') {
            $result[] = $end;
        } elseif ($stage === 'before') {
            $result[] = $begin;
            $result[] = $content;
            $result[] = $end;
        }
        return rtrim(implode("\n", $result))."\n";
    }
}
