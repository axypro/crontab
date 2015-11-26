# Crontab of some system

Suppose there is a certain system (most likely it is a site).

The site engine provides CLI-interface:

```
$ ./cli.php install db
```

Some of the CLI-tasks must be run by the CRON: garbage collection, statistic counting, log parsing and etc.

Example of crontab file:

```
10 * * * * cd /path/to/site && ./cli.php garbage > /dev/null 2>&1
0 0 * * * cd /path/to/site && ./cli.php logs > /dev/null 2>&1
0 */2 * * * cd /path/to/site && ./cli.php statistic > /dev/null 2>&1
```

Most tasks run from one user (owner of the site directory) but may be necessary and root:

```
0 * * * * indexer --all --rotate
```

And these systems on a single server can be many (all of them will share the same crontab file).

Library allows to describe all this in the config and save at the right time (usually at deploy).

## Config

The config for the above example:

```php
[
    'user' => 'git',
    'dir' => '/path/to/site',
    'cli' => './cli.php',
    'name' => 'example.loc',
    'jobs' => [
        'garbage' => '10',
        'logs' => 'In 0:00',
        'statistics' => 'Every 2 hours',
        'indexer' => [
            'full' => 'indexer --all --rotate',
            'time' => 'every 1 hour',
            'user' => 'root',
        ],
    ],
];
```

## Save

```php
use axy\crontab\Crontab;

$crontab = new Crontab($config);

$crontab->save();
```

This script save crontab for `git` and `root` users from config.
Tasks are written in a specific block of a file.
And each time overwritten once they.
Without affecting tasks not related to the site.

To write crontab files of all users need to run the script as root.

Or `$crontab->save(false)` write to crontab only default user (`git` in the example).

The method returns TRUE if the crontab has been modified.

## List

Or, you can simply print all the tasks to the console.
And then enter them manually.

```php
$crontab->getAllUsers();
```

Returns the list: `user name => crontab content`.

## Format of Config

All properties is optional.

##### user

The default user.
Specified for `crontab` in the option `-u`.
Need for execution from the root.

##### dir

The root dir of the system.
If not specified a job do not contain `cd ... && `.

##### cli

The CLI-script (path relative to the `dir`).

##### name

The name of the system.
To identify blocks within a crontab file.

##### redirect

The output redirect, appended to the end.
`/dev/null 2>&1` by default.

##### jobs

The array of jobs.
Format description see below.

## Job Format

Short syntax: `$task => $time`.

```php
$jobs = [
    'logs' => 'In 0:00',
];
```

Similarly to

```php
$jobs = [
    'logs' => [
        'time' => 'In 0:00',
    ],
];
```

Converted into:

```
0 0 * * * cd {dir} && {cli} logs > {redirect}
```

##### time

The time for running.
Has the extended format (see below).

Required field.

##### user

The owner of crontab.
By default `$config['user']`.

##### full

The full command.
If this field is specified all other fields are ignored.

```php
$jobs = [
    'indexer' => [
        'full' => 'indexer --all --rotate',
        'time' => 'every 1 hour',
        'user' => 'root',
     ],
];
```

##### command

The CLI-command of the site.
By default used the key of the array element.

##### comment

Just a comment for job (added to crontab before the job).

##### redirect

Output redirect.
By default used `$config['redirect']`.

Can be disabled by `NULL`.

## Extended format of time

All expressions are case insensitive.

* `1 2 3 4 5` - standard cron expression.
* `1 2` - short expression, expands to `1 2 * * *`.
* `in 3:30` - the current time for every day `30 3 * * *`.
* `every` - every minute (`* * * * *`).
* `every 2 minutes` - (`*/2 * * * *`). Can be used `m`, `min`, `minute` and `minutes`
* `every 5m + 1` - every 5 minutes with offset 1 (`1/5 * * * *`)
* `every 5m offset 1` - can be used `+` or `offset`
* `every 2h` - (`0 */2 * * *`). Can be user `h`, `hour` or `hours`.
* `every 2 hours in 10 min` - (`10 */2 * * *`).
* `every 5h offset 2 in 10m` - (`10 2/5 * * *`).
* `every hour` - every hour (`0 * * * *`).
* `every hour in 10m` - every hour (`10 * * * *`).
* empty string is `every`