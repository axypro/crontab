### 0.1.1 (26.11.2015)

* #2: Do not exec the crontab, if the content has not changed
* #4: Crontab::save() returns TRUE if the crontab has been modified

### 0.1.0 (18.11.2015)

* Crontab: save crontab files by the config
* Job: a cron job - create from a string, make the string representation
* JobList: a list of jobs - load from a file, create the file content
* Setter: set/get crontab for a user