<?php
/**
 * Parsing and changing of crontab files
 *
 * @package axy\crontab
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @license https://raw.github.com/axypro/crontab/master/LICENSE MIT
 * @link https://github.com/axypro/crontab repository
 * @link https://packagist.org/packages/axy/crontab composer package
 * @uses PHP5.4+
 */

namespace axy\crontab;

if (!is_file(__DIR__.'/vendor/autoload.php')) {
    throw new \LogicException('Please: composer install');
}

require_once(__DIR__.'/vendor/autoload.php');
