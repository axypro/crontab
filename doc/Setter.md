# `Setter`

```php
use axy\crontab\Setter;

$setter = Setter::getSystemInstance();
echo $setter->get();
```

Returns the crontab for the current user.
Or of another user (need root permission):

```php
echo $setter->get('root');
```

And set:

```php
$setter->set('* * * * * command');
```

Replaces the entire crontab-file.

For another user:

```php
$setter->set('* * * * * command', 'git');
```
