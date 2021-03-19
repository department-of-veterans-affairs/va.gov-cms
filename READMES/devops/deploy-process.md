
## Purpose
The purpose of this table is to show the steps the deploy process follows and what the end user experiences.

## Process

Task | Accessible Server | Task run from Server | CMS Available to Users
---- | ----------------- | -------------------- | ------------------
Deploy Process Kicked off | Current | Current| Yes
Notification in Slack | Current | Jenkins | Yes
Wait 60 minutes | Current | Jenkins | Yes
Get latest passing commit | Current | Jenkins
Find AMI for latest passing commit | Current | Jenkins | Yes
Enable Site Alert | Current | Current | No
Activate "Maintenance Mode" | Current | Current | No
Back up files to S3 | Current | Current | No
Add new AMI to load balancer | Current | Jenkins | No
New server comes online but not not live | Current | New | No
Run Drush Deploy Command (https://www.drush.org/latest/deploycommand/) | Current | New | No
Turn on Deploy Mode to have CMS export serve old files | Current | New | No
New Server is Online | New | New | No
Disable Site Alert | New | New | Yes
Run full CMS content export | New | New | Yes
Disable Deploy Mode | New | New | Yes
