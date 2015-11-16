<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab;

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
        foreach (['minute', 'hour', 'dayOfMonth', 'month', 'dayOfWeek'] as $i => $k) {
            $v = $components[$i];
            if ($v === '*') {
                $v = null;
            }
            $job->$k = $v;
        }
        return $job;
    }
}
