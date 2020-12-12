## To sync the CMS to CMS-TEST environment, follow these steps:
1. Run the job here http://jenkins.vfs.va.gov/job/cms-test/job/cms-to-cms-test-prod-db-sync
1. Run the database backup job here http://jenkins.vfs.va.gov/job/cms-test/job/cms-test-db-backup-prod/
1. Enable, then run the sanitize DB job here > http://jenkins.vfs.va.gov/job/cms-test/job/cms-test-db-sanitize/, then disable it again. This will sanitize the newly synced PROD DB in CMS-TEST (not CMS) and make available for downstream CMS-TEST environments. 
1. Enable the http://jenkins.vfs.va.gov/job/testing/job/cms-test/ job so that webhooks trigger the build and deploy to DEV and STAGING.
1. (BE VERY CAREFUL HERE)   
`git pull --rebase upstream master` then  `git push --force upstream-test master` (need to have these remotes setup, yours may be named differently).
This will trigger a webhook to run this job, http://jenkins.vfs.va.gov/job/testing/job/cms-test/ and will automatically trigger a build and then deploy it to DEV and STAGING with the appropriate database state to allow tests to pass. If the job was disabled or the webhook never made it Jenkins (the firewall/TIC) blocks it sometimes), you need to trigger the [build](http://jenkins.vfs.va.gov/job/builds/job/cms-test/) and deploy jobs yourself. Remember that only the STAGING deploy runs tests that send commit status to GitHub. We need a passing commit status for the auto-deploy job to run, if you are testing that job for instance. Once the build is done, you can manually trigger the PROD deploy job here as well http://jenkins.vfs.va.gov/job/deploys/job/cms-test-vagov-prod/. 

## To quickly restore an older version of the database to cms-test
1. Pull latest master from va.gov-cms and push to va.gov-cms-test # `git push --force upstream-test` if needed, be careful
1. Get commit from that was deployed at time of backup https://github.com/department-of-veterans-affairs/va.gov-cms-test/commits
1. Copy S3 URL from https://console.amazonaws-us-gov.com/s3/object/dsva-vagov-prod-cms-backup
1. SSM into server with `ssm-session vagov-prod cms-test auto`
1. `sudo -u apache -s` # become apache user
1. `cd /var/www/cms`
1. `git fetch`
1. `git checkout <commit-from-above>`
1. `source /etc/sysconfig/httpd; PATH=$PATH:/usr/local/bin composer nuke`
1. `source /etc/sysconfig/httpd; PATH=$PATH:/usr/local/bin composer install`
1. `source /etc/sysconfig/httpd; PATH=$PATH:/usr/local/bin drush status` # verify using the test database
1. `aws --region us-gov-west-1 s3 cp s3://dsva-vagov-prod-cms-backup/database/drupal8-db-prod-2020-12-02-15-00.sql.gz . `
1. `gunzip drupal8-db-prod-2020-12-02-15-00.sql.gz`
1. `source /etc/sysconfig/httpd; PATH=$PATH:/usr/local/bin drush sql-drop --yes`
1. `source /etc/sysconfig/httpd; PATH=$PATH:/usr/local/bin drush sql-cli < drupal8-db-prod-2020-12-02-15-00.sql`
