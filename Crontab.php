<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab;

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
     * @var \axy\crontab\Config
     */
    private $config;
}
