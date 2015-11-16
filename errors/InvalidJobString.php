<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab\errors;

use axy\errors\InvalidFormat;

/**
 * A job string has invalid format
 */
final class InvalidJobString extends InvalidFormat implements InvalidJob
{
    /**
     * The constructor
     *
     * @param string $job
     */
    public function __construct($job)
    {
        $this->job = $job;
        parent::__construct($job, 'Cron job');
    }

    /**
     * @return string
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @var string
     */
    private $job;
}
