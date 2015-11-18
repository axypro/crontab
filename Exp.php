<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab;

use axy\crontab\errors\InvalidJobString;

/**
 * Extended format of cron expression
 */
class Exp
{
    /**
     * @param string $expression
     * @return string
     * @throws \axy\crontab\errors\InvalidJob
     */
    public static function create($expression)
    {
        $exp = preg_replace('/\s+/', ' ', strtolower(trim($expression)));
        if ($exp === '') {
            return '* * * * *';
        }
        $exp = explode(' ', $exp);
        if (preg_match('~^[0-9\/\*]~', $exp[0])) {
            $count = count($exp);
            if ($count < 5) {
                $exp = array_merge($exp, array_fill(0, 5 - $count, '*'));
            } elseif ($count > 5) {
                throw new InvalidJobString($expression);
            }
            return implode(' ', $exp);
        }
        $word = array_shift($exp);
        switch ($word) {
            case 'every':
                $result = self::createEvery($exp);
                break;
            case 'in':
                $result = self::createIn($exp);
                break;
            default:
                $result = null;
        }
        if ($result === null) {
            throw new InvalidJobString($expression);
        }
        return $result;
    }

    /**
     * @param array $exp
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private static function createEvery(array $exp)
    {
        if (empty($exp)) {
            return '* * * * *';
        }
        $time = self::loadTime($exp);
        if ($time === null) {
            return null;
        }
        $period = $time[0];
        $time = $time[1];
        $offset = self::loadOffset($exp);
        if ($offset === null) {
            return null;
        } elseif ($offset === 0) {
            $offset = '*';
        }
        if ($time !== 1) {
            $time = $offset.'/'.$time;
        } else {
            $time = '*';
        }
        if ($period === 'm') {
            if (!empty($exp)) {
                return null;
            }
            return $time.' * * * *';
        } else {
            if (!empty($exp)) {
                if ($exp[0] !== 'in') {
                    return null;
                }
                array_shift($exp);
                if (empty($exp)) {
                    return null;
                }
                $tm = self::loadTime($exp);
                if (($tm === null) || ($tm[0] !== 'm') || (!empty($exp))) {
                    return null;
                }
                $min = $tm[1];
            } else {
                $min = 0;
            }
            return $min.' '.$time.' * * *';
        }
    }

    /**
     * @param array $exp
     * @return string
     */
    private static function createIn(array $exp)
    {
        if (count($exp) !== 1) {
            return null;
        }
        if (!preg_match('~^(?<h>[0-2]?[0-9]):(?<m>[0-5][0-9])$~s', $exp[0], $matches)) {
            return null;
        }
        $h = (int)$matches['h'];
        $m = (int)$matches['m'];
        if (($h > 23) || ($m > 59)) {
            return null;
        }
        return $m.' '.$h.' * * *';
    }

    /**
     * @param array $exp
     * @return array
     */
    private static function loadTime(array &$exp)
    {
        if (!preg_match('~^([0-9]+)([^0-9].*?)?$~s', $exp[0], $matches)) {
            return null;
        }
        array_shift($exp);
        $value = (int)$matches[1];
        if (empty($matches[2])) {
            if (empty($exp)) {
                return null;
            }
            $period = array_shift($exp);
        } else {
            $period = $matches[2];
        }
        if (in_array($period, ['m', 'min', 'minute', 'minutes'])) {
            $period = 'm';
            if ($value > 59) {
                return null;
            }
        } elseif (in_array($period, ['h', 'hour', 'hours'])) {
            if ($value > 23) {
                return null;
            }
            $period = 'h';
        } else {
            return null;
        }
        return [$period, $value];
    }

    /**
     * @param array $exp
     * @return int
     */
    private static function loadOffset(array &$exp)
    {
        if (empty($exp)) {
            return 0;
        }
        if (!in_array($exp[0], ['+', 'offset'])) {
            return 0;
        }
        array_shift($exp);
        if (empty($exp)) {
            return null;
        }
        return (int)array_shift($exp);
    }
}
