# `Job`: A Single Job

Properties:

* `minute`
* `hour`
* `dayOfMonth`
* `month`
* `dayOfWeek`
* `command`

`NULL` is `*`.

```php
use axy\crontab\Job;

$job = new Job();

$job->minute = 3;
$job->hour = '2/4';
$job->command = 'script.php > /dev/null';

echo $job;
```

Output:

```
3 2/4 * * * script.php > /dev/null
```

### Create from String

```php
$job = Job::createFromString('0 0 10 * * script.php');

echo $job->dayOfMonth; // 10
echo $job->command; // script.php
```

### Check

```php
Job::check([int $timestamp]): bool
```

Returns TRUE is a job is specified time (the current time by default) corresponds to the job.

```php
$job = Job::createFromString('0 5 * * * script.php');

$job->check(strtotime('2015-11-18 05:00:00')); // TRUE
$job->check(strtotime('2015-11-18 05:05:00')); // FALSE
```

NOTE: there may be variants of crontab expression that do not work.
