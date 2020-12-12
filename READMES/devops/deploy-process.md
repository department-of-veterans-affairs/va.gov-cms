
## Purpose
The purpose of this table is to show the steps the deploy process follows and what the end user experiences.

## Process

Task | Accessible Server | Task run from Server | CMS Available to Users | CMS tar version
---- | ----------------- | -------------------- | ------------------ | ---------------
Deploy Process Kicked off | Current | Current| Yes | Current
Notification in Slack | Current | Jenkins | Yes | Current
Wait 60 minutes | Current | Jenkins | Yes | Current
Get latest passing commit | Current | Jenkins | Yes | Current
Find AMI for latest passing commit | Current | Jenkins | Yes | Current
Enable Site Alert | Current | Current | No | Current
Create CMS Export tar file | Current | Current |No | Current
Activate "Maintenance Mode" | Current | Current | No | Backup
Back up files to S3 | Current | Current | No | Backup
Add new AMI to load balancer | Current | Jenkins | No | Backup
New server comes online but not not live | Current | New | No | Backup
Download files from s3 to new server | Current | New | No | Backup
Clear Drupal Cache | Current | New | No | Backup
Run Drupal migrations | Current | New | No | Backup
Run config import | Current | New | No | Backup
Clear Drupal cache | Current | New | No | Backup
Create CMS Export tar file | Current | New | No | Backup
Turn on Deploy Mode to have CMS export serve old files | Current | New | No | Backup
New Server is Online | New | New | No | Backup
Disable Site Alert | New | New | Yes | Backup
Run full CMS content export | New | New | Yes | Backup
Disable Deploy Mode | New | New | Yes | Live
