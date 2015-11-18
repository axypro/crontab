# `JobList`: A List of Jobs

### Append to the list

The constructor:

```php
JobList::__construct([mixed $jobs]);
```

`$jobs` can be another `JobList` instance or an array of job for `append()` (see below).


Append a job:

```php
JobList::append($job);
```

`$job` may be a [Job](Job.md) instance or a string.

```php
$list->append('* * * * * rm -rf /');
```

### Check

```php
$list->check();
```

Returns an array of Job instances which correspond to the current time.

### Get Content for Crontab

```php
echo $list->getConent();
```

Returns a string of jobs list for the crontab file.

### Create from file

Static methods:

```php
JobList::createFromFile(string $filename): JobList
JobList::createFromContent(string $content): JobList
```

Example file:

```php
# This is top comment
#
# m h dom m dow

* * * * * reboot
0 0 * * * shutdown
```

Load:

```php
$list = JobList::createFromFile('crontab.txt');
echo count($list); // 2
foreach ($list as $job) {
    echo $job->command.PHP_EOL;
}
```

Output:

```php
reboot
shutdown
```

### Interfaces

The class implements `Countable` (count of jobs) and `IteratorAggregate` (iteration of job instances).
