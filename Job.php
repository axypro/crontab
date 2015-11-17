<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab;

use axy\crontab\helpers\Checker;
use axy\crontab\errors\InvalidJobString;

/**
 * A cron job
 */
class Job
{
    /**
     * Command to execute
     *
     * @var string
     */
    public $command;

    /**
     * @var string
     */
    public $minute;

    /**
     * @var string
     */
    public $hour;

    /**
     * @var string
     */
    public $dayOfMonth;

    /**
     * @var string
     */
    public $month;

    /**
     * @var string
     */
    public $dayOfWeek;

    /**
     * @return string
     */
    public function __toString()
    {
        $values = [
            $this->minute,
            $this->hour,
            $this->dayOfMonth,
            $this->month,
            $this->dayOfWeek,
        ];
        foreach ($values as &$v) {
            if ($v === null) {
                $v = '*';
            }
        }
        unset($v);
        return implode(' ', $values).' '.$this->command;
    }

    /**
     * Checks a timestamp
     *
     * @param int $time [optional]
     *        the timestamp (the current time by default)
     * @return bool
     */
    public function check($time = null)
    {
        $this->normalize();
        $date = getdate($time ?: time());
        $comps = [
            'minute' => ['minutes', null],
            'hour' => ['hours', null],
            'month' => ['mon', 'm'],
        ];
        foreach ($comps as $k => $v) {
            if (!Checker::check($this->$k, $date[$v[0]], $v[1])) {
                return false;
            }
        }
        $wDay = 'w'.'day';
        $mDay = 'm'.'day';
        if ($this->dayOfWeek !== null) {
            if (Checker::check($this->dayOfWeek, $date[$wDay], 'w')) {
                return true;
            }
            if ((int)$date[$wDay] === 0) {
                if (Checker::check($this->dayOfWeek, 7, 'w')) { // Sunday is 0 and 7
                    return true;
                }
            }
            if ($this->dayOfMonth === null) {
                return false;
            }
        }
        return Checker::check($this->dayOfMonth, $date[$mDay]);
    }

    /**
     * Creates a job instance from a crontab string
     *
     * @param string $string
     * @return \axy\crontab\Job
     * @throws \axy\crontab\errors\InvalidJobString
     */
    public static function createFromString($string)
    {
        $components = preg_split('~\s+~', trim($string), 6);
        if (count($components) !== 6) {
            throw new InvalidJobString($string);
        }
        $job = new self();
        $job->command = ltrim($components[5]);
        foreach (self::$keys as $i => $k) {
            $v = $components[$i];
            $job->$k = $v;
        }
        $job->normalize();
        return $job;
    }

    private function normalize()
    {
        foreach (self::$keys as $k) {
            if ($this->$k === '*') {
                $this->$k = null;
            }
        }
    }

    /**
     * @var string[]
     */
    private static $keys = ['minute', 'hour', 'dayOfMonth', 'month', 'dayOfWeek'];
}
