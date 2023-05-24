## To sync the CMS database to the CMS-TEST environment, follow these steps:
1. Run the job here http://jenkins.vfs.va.gov/job/cms-test/job/cms-to-cms-test-prod-db-sync
1. Enable, then run the database backup job here http://jenkins.vfs.va.gov/job/cms-test/job/cms-test-db-backup-prod/, then disable it again.
1. Enable, then run the sanitize DB job here > http://jenkins.vfs.va.gov/job/cms-test/job/cms-test-db-sanitize/, then disable it again. This will sanitize the newly synced PROD DB in CMS-TEST (not CMS) and make available for downstream CMS-TEST environments.

## To deploy the CMS to the CMS-TEST environment from the `main` branch, follow these steps:
1. Ensure that you have a git remote set up for the `va.gov-cms-test` branch (e.g. `git remote add upstream-test git@github.com:department-of-veterans-affairs/va.gov-cms-test.git`).
1. Enable the http://jenkins.vfs.va.gov/job/testing/job/cms-test/ job so that webhooks trigger the build and deploy to STAGING.
1. (BE VERY CAREFUL HERE) `git pull --rebase upstream main` then  `git push --force upstream-test main`.

This will trigger a webhook to run this job: http://jenkins.vfs.va.gov/job/testing/job/cms-test/ and will automatically trigger a build and then deploy it to CMS-TEST STAGING with the appropriate database state to allow tests to pass. 

If the job was disabled or the webhook never made it Jenkins (the firewall/TIC) blocks it sometimes), you need to trigger the [build](http://jenkins.vfs.va.gov/job/builds/job/cms-test/) and run the deploy jobs by yourself. 

Remember that only the CMS-TEST STAGING deploy runs the tests that send the commit status to GitHub. We need a passing commit status for the auto-deploy job to run, if you are testing that job for instance. 

Once the build is done, you can manually trigger the CMS-TEST PROD deploy job here as well http://jenkins.vfs.va.gov/job/deploys/job/cms-test-vagov-prod/.

## Sync file assets to CMS-TEST PROD
1. `ssm-session vagov-prod cms-test auto`
1. `sudo su --login cms` 
2. `sudo --user apache --shell`
3. `cd /var/www/cms`
4. `scripts/sync-files.sh`

## To restore an older version of the database to CMS-TEST PROD
1. Pull latest main from va.gov-cms and push to va.gov-cms-test # `git push --force upstream-test` if needed, be careful
1. Get commit from that was deployed at time of backup https://github.com/department-of-veterans-affairs/va.gov-cms-test/commits
1. Copy S3 URL from https://us-gov-west-1.console.amazonaws-us-gov.com/s3/buckets/dsva-vagov-prod-cms-backup
1. SSM into server with `ssm-session vagov-prod cms-test auto`
1. `sudo su -u cms` # become apache user
1. `git checkout <commit-from-above>`
1. `composer va:nuke`
1. `composer install`
1. `drush status` # verify using the test database
1. `aws --region us-gov-west-1 s3 cp s3://dsva-vagov-prod-cms-backup/database/drupal8-db-prod-2020-12-02-15-00.sql.gz . `
1. `gunzip drupal8-db-prod-2020-12-02-15-00.sql.gz`
1. `drush sql-drop --yes`
1. `drush sql-cli < drupal8-db-prod-2020-12-02-15-00.sql`
