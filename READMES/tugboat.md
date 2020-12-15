## Tugboat 101

### Summary of Tugboat
Tugboat is fast, modern Preview environment creation tool based on containers (Docker Swarm). Tugboat creates "Previews" which are instances you can test changes on, login with a web based CLI tool, and view logs in the UI. It uses a concept of a "Base Preview" which is a container with a production database snapshot baked into it and ready to put your new code onto and run post-deploy operations (updatedb, config:import). This base preview image is built every day just after the PROD CMS deploy and uses a database snapshot from that time. So when you launch your PR, it launches from that state and doesn't have to sync files or the database and therefore can just run your code updates and configuration import and then it posts a comment to your pull request with links to your Preview environment.

**Getting started for Pull Requests (Demo coming soon)**
1. Log in to the Tugboat dashboard (internal) https://tugboat.vfs.va.gov. When you first log in with GitHub, you need to wait up to 2 minutes for your user account to be granted access to project(s) by a cron script that runs every minute (we are working on making this instant eventually). After you have waited:
1. Click the "CMS" project then click "CMS Pull Request Environments"
1. Make a pull request
1. A "Deployment in Progress" message will appear on your Pull Request, and you will see a new environment appear in the Tugboat Dashboard. Here you can view the logs or launch a "terminal" to modify code or run drush commands etc.
1. Within 3 minutes a comment should be posted with links to your environment for testing, this includes a WEB (web-*) URL that builds the static site for testing. The WEB environment will take a while to build and only be stable after all tests pass.
1. After the comment is posted with your environment links tests will start running and switch from "Expected" to "Pending", this will take closer to 30+ minutes.

## Tips
1. Refresh, Rebuild and Reset operations. Learn more at https://docs.tugboat.qa/building-a-preview/preview-deep-dive/how-previews-work. Below is a quick summary that might help clarify
    1. Refresh: Starts at the "Update" stage, see .tugboat/config.yml. This syncs your DB and files from recent PROD backups.
    1. Rebuild: Rebuilds your Pull Request Preview environment from the "Build" stage, see .tugboat/config.yml.
    1. Reset: Resets your database and code to the state it was when the Preview environment was created.
1. Clone doesn't clone the current state of the DB, it clones the state of the Preview from when it was created.
1. Environments are deleted on a PR merge/close by default, you can "Lock" the environment to prevent deletion.
1. There should only be one "Base Preview" built on master
    1. The base preview rebuilds daily at 4pm ET, just after our prod.cms.va.gov daily deployment
1. You can change the prefix on any environment, all that matters is the token in the URL, e.g. https://pr165-z82nl225gxrzbpcmfxt34th673gtwpmu.tugboat.vfs.va.gov/ will go to the same place as https://rainboxes-z82nl225gxrzbpcmfxt34th673gtwpmu.tugboat.vfs.va.gov/, they are the same. The exception is that if any URL starts with `web-*` then it will be routed to the /docroot/static folder to serve out the static website (vets-website).

## Common Tugboat operations

| I want to... | Then you should... |
| :--- | :--- |
| Re-run tests on my pull request | Run the "Rebuild" action, this will run the "BUILD" stage and then run the "ONLINE" stage, which will run the tests.
| Update the base preview image with latest Production DB snapshot | Go to dashboard and "rebuild" the base preview (master). Normally this happens every day automatically and you shouldn't need to do this, but if you do, that is how.
| Get the latest database on my pull request environment | Run the "Refresh" command to run the "Update" stage of the "mysql" service which pulls in the latest databaseand files snapshots (within 15 minutes of freshness) |
| See why my deploy failed | Go to the dashboard, find your PR and click the 'php' service title, then click "build logs"
| Lock my environment from getting deleted, like after my PR is closed. | Use the "LOCK" action
| Manually create an environment from a branch, tag, or pull request | First push to upstream, then go to branches and click "Build Preview"

## Less common Tugboat operations

| I want to... | Then you should... |
| :--- | :--- |
| Know why the logs are showing a previous build | Change from "current" to the latest timestamp, this is a known issue and we are working on a resolution. You can also use the Tugboat CLI tool with `tugboat log <service id>` to get the log and grep it etc.
| Search the logs, ctrl + F in my browser is not working | This is not possible in the browser UI, you must use the Tugboat CLI tool with `tugboat log <service id>` and grep the logs that way.
| Scroll the logs. | This is not possible in the Tugboat UI, use `tugboat log <service id>` to grep or scroll.
| Run more advanced commands with the `tugboat` tool on the proxy | We are working on figuring this out |
| Want to get the latest .env file | Run a "Refresh" to run the "Build" stage which re-generates the .env file with latest ENV variables.

## Tugboat config testing operations
| I want to... | Then you should... |
| :--- | :--- |
| Test out an 'init' stage change | Push your branch to upstream (can't be a fork) and go to https://tugboat.vfs.va.gov/ and click your project. Then scroll down to the "Available to Build" section, then click the "Branches" tab and then click the dropdown and click "Build with no base preview" |
| Test out an 'update' stage change | TODO |

## Tugboat's CLI tool for software engineers
1. Download at https://tugboat.vfs.va.gov/downloads and put in your $PATH
1. cp .tugboat/.tugboat.yml $HOME/.tugboat.yml
1. Generate API key at https://tugboat.vfs.va.gov/access-tokens
1. Add token to $HOME/.tugboat.yml
1. Start SOCKS connection
1. Test with `tugboat help`, use `-v` for verboseness
1. See more Tugboat CLI documentation here

## Known issues
1. The generated URLs do change and are not constant/static, e.g. https://pr165-z82nl225gxrzbpcmfxt34th673gtwpmu.tugboat.vfs.va.gov/ will change on a new commit to e.g. https://pr165-a18cl225gxrzbpcmfxt34th673gtwpmu.tugboat.vfs.va.gov/. If this is not good please let us know, and we can come up with a solution
1. You cannot search logs with a browser right now, it is a known issue. The alternative is to use the `tugboat` CLI tool to view logs which we are working on figuring out how to use with the proxy.
1. You cannot scroll the logs while they are outputting, you can only scroll once they are done. If you want to see previous output then use the Tugboat CLI tool with `tugboat log <service id>` and scroll that way.
