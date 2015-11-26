<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab;

use axy\crontab\helpers\Insert;
use axy\errors\InvalidConfig;

/**
 * Crontab of some system
 */
class Crontab
{
    /**
     * The constructor
     *
     * @param array $config
     * @throws \axy\errors\InvalidConfig
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
        $cmd = isset($config['_cmd_crontab']) ? $config['_cmd_crontab'] : null;
        $err = isset($config['_cmd_error']) ? $config['_cmd_error'] : null;
        $this->setter = new Setter($cmd, $err);
    }

    /**
     * Returns crontab lists for users
     *
     * @param string|bool $user [optional]
     *        user or TRUE for default user
     * @param bool $brackets [optional]
     *        use comment bracket
     * @return string
     */
    public function getUser($user = true, $brackets = true)
    {
        $result = $this->config->getCrontabForUser($user);
        if ($brackets) {
            $name = $this->config->name;
            $result = '# begin '.$name."\n".$result."\n# end ".$name;
        }
        return $result;
    }

    /**
     * Returns crontab for all users
     *
     * @param bool $brackets [optional]
     * @return string[] (user => crontab)
     */
    public function getAllUsers($brackets = true)
    {
        $result = [];
        foreach ($this->config->getListOfUsers() as $user) {
            $result[$user] = $this->getUser($user, $brackets);
        }
        return $result;
    }

    /**
     * Saves crontab
     *
     * @param bool $asRoot [optional]
     *        the process is run as root
     * @throws \axy\errors\InvalidConfig
     */
    public function save($asRoot = true)
    {
        if ($asRoot) {
            $users = $this->getAllUsers(false);
            if (isset($users[null])) {
                throw new InvalidConfig('Crontab', 'required default "user" for run as root');
            }
        } else {
            $user = $this->config->user;
            $users = [$user => $this->getUser($user, false)];
        }
        foreach ($users as $user => $crontab) {
            $origin = $this->setter->get($user);
            $content = Insert::insertContent($origin, $crontab, $this->config->name);
            if ($origin !== $content) {
                $this->setter->set($content, $user);
            }
        }
    }

    /**
     * @var \axy\crontab\Config
     */
    private $config;

    /**
     * @var \axy\crontab\Setter
     */
    private $setter;
}
