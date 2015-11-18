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
     * @param string $err [optional]
     */
    public function __construct($cmd = null, $err = null)
    {
        if ($cmd === null) {
            $cmd = 'crontab';
        }
        $this->cmd = $cmd;
        if ($err) {
            $this->descriptors[2] = ['file', $err, 'w'];
        }
    }

    /**
     * @param string $user [optional]
     * @return string
     */
    public function get($user = null)
    {
        $cUser = $user ? ' -u'.$user : '';
        $cmd = $this->cmd.$cUser.' -l';
        return $this->loadFromProcess($cmd);
    }

    /**
     * @param string $content
     * @param string $user [optional]
     * @return bool
     */
    public function set($content, $user = null)
    {
        $cUser = $user ? ' -u'.$user.' -' : '';
        $cmd = $this->cmd.$cUser;
        return $this->saveToProcess($cmd, $content);
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
     * @param string $cmd
     * @return string
     */
    private function loadFromProcess($cmd)
    {
        $fp = proc_open($cmd, $this->descriptors, $pipes);
        if (!$fp) {
            return '';
        }
        $result = stream_get_contents($pipes[1]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        proc_close($fp);
        return $result;
    }

    /**
     * @param string $cmd
     * @param string $content
     * @return string
     */
    private function saveToProcess($cmd, $content)
    {
        $fp = proc_open($cmd, $this->descriptors, $pipes);
        if (!$fp) {
            return false;
        }
        fwrite($pipes[0], $content);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        proc_close($fp);
        return true;
    }

    /**
     * @var string
     */
    private $cmd;

    /**
     * @var \axy\crontab\Setter
     */
    private static $system;

    /**
     * @var array
     */
    private $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['file', '/dev/null', 'w'],
    ];
}
