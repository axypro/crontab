#!/usr/bin/env php
<?php
/**
 * Crontab emulator
 */

$format = 'Format: crontab.php [-u{user}] -(l|e) [filename]'.PHP_EOL;

$args = $_SERVER['argv'];
$count = count($args);

if (($count < 2) || ($count > 3)) {
    fwrite(STDERR, $format);
    exit();
}

if (substr($args[1], 0, 2) === '-u') {
    $user = substr($args[1], 2);
    if (!preg_match('~^[a-z]{1,20}$~i', $user)) {
        fwrite(STDERR, $format);
        exit();
    }
    $ind = 2;
} else {
    $user = 'default';
    $ind = 1;
}

if (!isset($args[$ind])) {
    fwrite(STDERR, $format);
    exit();
}
$opt = $args[$ind];

$fn = __DIR__.'/tmp/'.$user.'.txt';
if ($opt === '-l') {
    if (is_file($fn)) {
        fwrite(STDOUT, file_get_contents($fn));
        exit();
    } else {
        // fwrite(STDERR, 'no crontab for '.$user.PHP_EOL);
        exit();
    }
} elseif ($opt === '-e') {
    $content = fread(STDIN, 1024000);
    file_put_contents($fn, $content);
} else {
    fwrite(STDERR, $format);
    exit();
}
