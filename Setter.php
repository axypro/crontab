<?php
/**
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crontab;

/**
 * Get/set crontab to the system
 */
class Setter
{
    /**
     * @param string $cmd [optional]
     */
    public function __construct($cmd = null)
    {
        if ($cmd === null) {
            $cmd = 'crontab';
        }
        $this->cmd = $cmd;
    }

    /**
     * @param string $user [optional]
     * @return string
     */
    public function get($user = null)
    {
        $result = [];
        $cUser = $user ? ' -u'.$user : '';
        $cmd = $this->cmd.$cUser.' -l';
        $fp = popen($cmd, 'r');
        while (!feof($fp)) {
            $result[] = fread($fp, 512);
        }
        fclose($fp);
        return implode('', $result);
    }

    /**
     * @param string $content
     * @param string $user [optional]
     */
    public function set($content, $user = null)
    {
        $cUser = $user ? ' -u'.$user : '';
        $cmd = $this->cmd.$cUser.' -e';
        $fp = popen($cmd, 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

    /**
     * @return \axy\crontab\Setter
     */
    public static function getSystemInstance()
    {
        if (!self::$system) {
            self::$system = new self();
        }
        return self::$system;
    }

    /**
     * @var string
     */
    private $cmd;

    /**
     * @var \axy\crontab\Setter
     */
    private static $system;
}
