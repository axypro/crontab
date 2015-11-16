<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\helpers;

/**
 * Helper for job check
 */
class Checker
{
    /**
     * @param string $exp
     * @param int $value
     * @param string $type [optional]
     * @return bool
     */
    public static function check($exp, $value, $type = null)
    {
        if ($exp === null) {
            return true;
        }
        $value = (int)$value;
        if ((string)$value === $exp) {
            return true;
        }
        $sep = explode('/', $exp, 2);
        $exp = $sep[0];
        $divider = (isset($sep[1])) ? (int)$sep[1] : null;
        $type = isset(self::$types[$type]) ? self::$types[$type] : [];
        $exp = strtolower($exp);
        return self::checkFirst($value, self::getFirst($exp, $value, $type, $divider), $divider);
    }

    /**
     * @param string $exp
     * @param string $value
     * @param array $type
     * @param int $divider
     * @return int
     */
    private static function getFirst($exp, $value, $type, $divider)
    {
        if (strpos($exp, ',') !== false) {
            return self::checkList($exp, $value, $type);
        }
        if (strpos($exp, '-') !== false) {
            return self::checkRange($exp, $value, $type);
        }
        if ($exp === '*') {
            return 0;
        }
        if ($divider) {
            return isset($type[$exp]) ? $type[$exp] : (int)$exp;
        }
        return self::checkSingle($exp, $value, $type);
    }

    /**
     * @param int $value
     * @param int $first
     * @param int $divider
     * @return bool
     */
    private static function checkFirst($value, $first, $divider)
    {
        if ($first === null) {
            return false;
        }
        if (!$divider) {
            return true;
        }
        return (($value % $divider) === $first);
    }

    /**
     * @param string $exp
     * @param int $value
     * @param array $type
     * @return int
     */
    private static function checkSingle($exp, $value, $type)
    {
        $exp = isset($type[$exp]) ? $type[$exp] : (int)$exp;
        if ($exp === $value) {
            return $exp;
        }
        return null;
    }

    /**
     * @param string $exp
     * @param int $value
     * @param array $type
     * @return int
     */
    private static function checkRange($exp, $value, $type)
    {
        $exp = explode('-', $exp, 2);
        $begin = $exp[0];
        $end = $exp[1];
        $begin = isset($type[$begin]) ? $type[$begin] : (int)$begin;
        $end = isset($type[$end]) ? $type[$end] : (int)$end;
        return (($value >= $begin) && ($value <= $end)) ? $begin : null;
    }

    /**
     * @param string $exp
     * @param int $value
     * @param array $type
     * @return int
     */
    private static function checkList($exp, $value, $type)
    {
        $first = null;
        foreach (explode(',', $exp) as $c) {
            $c = isset($type[$c]) ? $type[$c] : (int)$c;
            if ($first === null) {
                $first = $c;
            }
            if ($value === $c) {
                return $first;
            }
        }
        return null;
    }

    /**
     * @var array
     */
    private static $types = [
        'w' => [
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'web' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        ],
        'm' => [
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
        ],
    ];
}
