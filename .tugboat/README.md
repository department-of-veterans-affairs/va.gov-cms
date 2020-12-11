## Tugboat 101

### Summary of Tugboat
Tugboat is a pull request environment builder. It creates "Previews" which are instances you can test changes on. It is very fast. It uses a concept of a "Base Preview" which is a container with a production database snapshot baked into it and ready to put your new code onto.

**Getting started**
1. Log in at (internal) https://tugboat.vfs.va.gov. When you first log in with GitHub, you need to wait up to 2 minutes for your user account to be granted access to project(s) by a cron script that runs every minute (we are working on making this instant eventually).
1. Make a pull request
1. Refresh, Rebuild and Reset are confusing, it will take time, be okay with that. Learn more at https://docs.tugboat.qa/building-a-preview/preview-deep-dive/how-previews-work. Below is a quick summary that might be less confusing:
    1. Refresh:
    1. Rebuild:
    1. Reset
1. Clone doesn't clone the current state of the DB, it clones the state of the preview it started from when it was made.
1. Environments are deleted on a PR merge/close by default, you can "Lock" the environment to prevent deletion
1. There should only be one "Base Preview" built on master
    1. The base preview rebuilds daily at 4pm ET, just after our prod.cms.va.gov daily deployment
1. You can change the prefix on any environment, all that matters is the token in the URL, e.g. https://pr165-z82nl225gxrzbpcmfxt34th673gtwpmu.tugboat.vfs.va.gov/ will go to the same place as https://rainboxes-z82nl225gxrzbpcmfxt34th673gtwpmu.tugboat.vfs.va.gov/, they are the same.

## Common Tugboat operations

| I want to... | Then you should... |
| :--- | :--- |
| Re-run tests on my pull request | Run the "Rebuild" action, this will run the "BUILD" stage and then run the "ONLINE" stage, which will run the tests.
| Update the base preview image with latest Production DB snapshot | Go to dashboard and "rebuild" the base preview (master). Normally this happens every day automatically and you shouldn't need to do this, but if you do, that is how.
| Get the latest database on my pull request environment | Run the "Refresh" command to run the "Update" stage of the "mysql" service which pulls in the latest database snapshot (within 15 minutes of freshness) |
| See why my deploy failed | Go to the dashboard, find your PR and click the 'php' service title, then click "build logs"
| Lock my environment from getting deleted, like after my PR is closed. | Use the "LOCK" action
| See what went wrong | Use the "Terminal" on the service
| Manually create an environment from a branch, tag, or pull request | First push to upstream, then go to branches and click "Build Preview"



## Less common Tugboat operations

| I want to... | Then you should... |
| :--- | :--- |
| Know why the logs are showing a previous build | Change from "current" to the latest timestamp
| Run more advanced commands with the `tugboat` tool on the proxy| We are working on figuring this out |
| Want to get the latest .env file | Run a "Refresh" to run the "Build" stage which re-generates the .env file

## Tugboat setup operations
| I want to... | Then you should... |
| :--- | :--- |
| Test out an 'init' stage change | |
| Test out an 'update' stage change | |


## Known issues
1. The generated URLs do change and are not constant/static, e.g. https://pr165-z82nl225gxrzbpcmfxt34th673gtwpmu.tugboat.vfs.va.gov/ will change on a new commit to e.g. https://pr165-a18cl225gxrzbpcmfxt34th673gtwpmu.tugboat.vfs.va.gov/. If this is not good please let us know, and we can come up with a solution
1. You cannot search logs with a browser right now, it is a known issue. The alternative is to use the `tugboat` CLI tool to view logs which we are working on figuring out how to use with the proxy.


