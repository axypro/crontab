<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab;

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
}
