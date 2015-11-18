<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab;

use axy\crontab\errors\InvalidJobString;
use axy\errors\InvalidConfig;

/**
 * The config of tasks of some system
 */
class Config
{
    /**
     * The default user name
     *
     * @var string
     */
    public $user;

    /**
     * The root directory of the system
     *
     * @var string
     */
    public $dir;

    /**
     * The CLI command
     *
     * @var string
     */
    public $cli;

    /**
     * The output redirect
     *
     * @var string
     */
    public $redirect = '/dev/null 2>&1';

    /**
     * The system name for crontab block
     *
     * @var string
     */
    public $name;

    /**
     * The list of jobs configs
     *
     * @var array
     */
    public $jobs = [];

    /**
     * The constructor
     *
     * @param array $config [optional]
     *        config parameters for merging
     * @throws \axy\errors\InvalidConfig
     */
    public function __construct(array $config = null)
    {
        if ($config !== null) {
            foreach (['user', 'dir', 'cli', 'redirect', 'name'] as $k) {
                if (isset($config[$k])) {
                    $value = $config[$k];
                    if (!is_scalar($value)) {
                        throw new InvalidConfig('Crontab', 'Field "'.$k.'" must be a scalar');
                    }
                    $this->$k = $value;
                }
            }
            if (isset($config['jobs'])) {
                $jobs = $config['jobs'];
                if (!is_array($jobs)) {
                    throw new InvalidConfig('Crontab', 'Field "jobs" must be an array', 0, null, $this);
                }
                $this->loadJobs($jobs);
            }
        }
    }

    /**
     * Returns job lists for each user
     *
     * @param string|bool $user [optional]
     *        user name or TRUE for default user
     * @return string
     */
    public function getCrontabForUser($user = true)
    {
        if ($user === true) {
            $user = $this->user;
        }
        $result = [];
        foreach ($this->jobs as $params) {
            if ($params['user'] === $user) {
                if ($params['full']) {
                    $command = $params['full'];
                } else {
                    $command = [];
                    if ($this->dir !== null) {
                        $command[] = 'cd '.$this->dir;
                    }
                    $command[] = $this->cli.' '.$params['command'];
                    $command = implode(' && ', $command);
                    if ($params['redirect'] !== null) {
                        $command .= ' > '.$params['redirect'];
                    }
                }
                if ($params['comment'] !== null) {
                    $result[] = '# '.$params['comment'];
                }
                $result[] = $params['time'].' '.$command;
            }
        }
        return implode("\n", $result);
    }

    /**
     * @param array $jobs
     * @throws \axy\errors\InvalidConfig
     */
    private function loadJobs(array $jobs)
    {
        $defaults = $this->defaults;
        $defaults['user'] = $this->user;
        $defaults['redirect'] = $this->redirect;
        foreach ($jobs as $k => $job) {
            $this->loadSingleJob($job, $k, $defaults);
        }
    }

    /**
     * @param mixed $job
     * @param string $k
     * @param array $defaults
     * @throws \axy\errors\InvalidConfig
     */
    private function loadSingleJob($job, $k, array $defaults)
    {
        if (!is_array($job)) {
            $job = ['time' => $job];
        }
        if (!isset($job['command'])) {
            $job['command'] = $k;
        }
        foreach ($defaults as $dk => $dv) {
            if (array_key_exists($dk, $job)) {
                if ((!is_scalar($job[$dk])) && ($job[$dk] !== null)) {
                    throw new InvalidConfig('Crontab', 'Job "'.$k.'" field "'.$dk.'" must be a scalar', 0, null, $this);
                }
            } else {
                $job[$dk] = $dv;
            }
        }
        if (!isset($job['time'])) {
            throw new InvalidConfig('Crontab', 'Job "'.$k.'" has no time', 0, null, $this);
        }
        try {
            $job['time'] = Exp::create($job['time']);
        } catch (InvalidJobString $e) {
            throw new InvalidConfig('Crontab', 'Job "'.$k.'" has invalid time', 0, $e, $this);
        }
        $job = array_replace($defaults, $job);
        $this->jobs[$k] = $job;
    }

    /**
     * The default config of a job
     *
     * @var array
     */
    private $defaults = [
        'full' => null,
        'user' => null,
        'command' => null,
        'comment' => null,
        'time' => null,
        'redirect' => null,
    ];
}
