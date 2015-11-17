<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab;

/**
 * A list of jobs
 */
class JobList implements \Countable, \IteratorAggregate
{
    /**
     * The constructor
     *
     * @param \axy\crontab\JobList|array $jobs [optional]
     *        another job list, array of job or array of string
     * @throws \axy\crontab\errors\InvalidJobString
     */
    public function __construct($jobs = null)
    {
        if ($jobs !== null) {
            if ($jobs instanceof self) {
                $jobs = $jobs->jobs;
            }
            if (!is_array($jobs)) {
                throw new \InvalidArgumentException('jobs must be a JobList or an array');
            }
            foreach ($jobs as $job) {
                $this->append($job);
            }
        }
    }

    /**
     * @param \axy\crontab\Job|string $job
     * @throws \axy\crontab\errors\InvalidJobString
     */
    public function append($job)
    {
        if (!($job instanceof Job)) {
            $job = Job::createFromString($job);
        }
        $this->jobs[] = $job;
    }

    /**
     * Returns a list of jobs that must be executed in this time
     *
     * @param int $time [optional]
     * @return \axy\crontab\Job[]
     */
    public function check($time = null)
    {
        if ($time === null) {
            $time = time();
        }
        $result = [];
        foreach ($this->jobs as $job) {
            if ($job->check($time)) {
                $result[] = $job;
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->jobs);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->jobs);
    }

    /**
     * Returns the content for a crontab file
     *
     * @return string
     */
    public function getContent()
    {
        $lines = [];
        foreach ($this->jobs as $job) {
            $lines[] = (string)$job."\n";
        }
        return implode('', $lines);
    }

    /**
     * Creates a job list by the content of a crontab file
     *
     * @param string $content
     * @return \axy\crontab\JobList
     * @throws \axy\crontab\errors\InvalidJobString
     */
    public static function createFromContent($content)
    {
        $list = new self();
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (substr($line, 0, 1) === '#') {
                continue;
            }
            $list->append($line);
        }
        return $list;
    }

    /**
     * Loads a job list from the file
     *
     * @param string $filename
     * @return \axy\crontab\JobList
     * @throws \axy\crontab\errors\InvalidJobString
     */
    public static function createFromFile($filename)
    {
        return self::createFromContent(file_get_contents($filename));
    }

    /**
     * @var \axy\crontab\Job[]
     */
    private $jobs = [];
}
