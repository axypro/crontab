#!/usr/bin/env php
<?php
/**
 * Crontab emulator
 */

$format = 'Format: crontab.php [-u{user}] [-l]'.PHP_EOL;

$args = $_SERVER['argv'];
$count = count($args);

$user = 'default';
array_shift($args); // crontab.php

if ((!empty($args)) && (substr($args[0], 0, 2) === '-u')) {
    $user = substr(array_shift($args), 2);
    if (!preg_match('~^[a-z]{1,20}$~i', $user)) {
        fwrite(STDERR, $format);
        exit();
    }
}

if (empty($args)) {
    $action = 'edit';
} else {
    $opt = array_shift($args);
    if ($opt === '-') {
        $action = 'edit';
    } elseif ($opt === '-l') {
        $action = 'list';
    } else {
        fwrite(STDERR, $format);
        exit();
    }
    if (!empty($args)) {
        fwrite(STDERR, $format);
        exit();
    }
}

$fn = __DIR__.'/tmp/'.$user.'.txt';

switch ($action) {
    case 'edit':
        $content = stream_get_contents(STDIN);
        file_put_contents($fn, $content);
        break;
    case 'list':
        if (is_file($fn)) {
            fwrite(STDOUT, file_get_contents($fn));
            exit();
        } else {
            fwrite(STDERR, 'no crontab for '.$user.PHP_EOL);
            exit();
        }
        break;
}
