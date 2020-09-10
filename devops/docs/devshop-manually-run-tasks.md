## To manually run tasks
1. In browser, go to http://devshop.cms.va.gov/hosting/tasks, sort by "created" twice to list most recently created tasks at top
1. In browser, go to http://devshop.cms.va.gov/projects to list environments
1. Click into environment that you want to run manually, to see the tasks that are queued
    1. The order they need to run manually in is "verify platform", "install", "deploy", "run tests"
1. Hover over the task that needs to be run first to get the task/node id
1. ssh into devshop, then `sudo su - aegir`
1. Run `drush @hostmaster hosting-task <task ID from above>`. If you refresh the browser page now you will see the task running now
1. Do this for the remaining tasks

## To delete existing tasks
1. Go to the UI of the task and hit "cancel", this is especially useful if more commits are pushed and old tasks are queued that you don't want anymore

### Notes
1. The "Run tests" task is created at the end of the "Deploy" task.
1. "verify site" is not needed and is annoying and runs periodically, you can ignore this task. It just updates site alias files.
1. "verify platform" runs a composer install and runs more than it should.
