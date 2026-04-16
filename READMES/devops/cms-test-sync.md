## To sync the CMS database to the CMS-TEST environment, follow these steps:
1. Run the job here http://jenkins.vfs.va.gov/job/cms-test/job/cms-to-cms-test-prod-db-sync
1. Enable, then run the database backup job here http://jenkins.vfs.va.gov/job/cms-test/job/cms-test-db-backup-prod/, then disable it again.
1. Enable, then run the sanitize DB job here > http://jenkins.vfs.va.gov/job/cms-test/job/cms-test-db-sanitize/, then disable it again. This will sanitize the newly synced PROD DB in CMS-TEST (not CMS) and make available for downstream CMS-TEST environments.

## To deploy the CMS to the CMS-TEST environment, follow these steps:
1. Ensure that you have a git remote set up for the `va.gov-cms-test` branch (e.g. `git remote add upstream-test git@github.com:department-of-veterans-affairs/va.gov-cms-test.git`).
1. `git pull --rebase origin main` - this will update your local `main` branch from CMS (not CMS-TEST)
1. Create a new branch off of `main`, i.e. `git checkout -b cms-test-branch-20260203`
1. `git push upstream-test cms-test-branch-20260203` (or whatever your branch name in the previous step is) - remember to use `upstream-test` in order to push the branch to the CMS-TEST repo.
1. Use `git log` to get the SHA of the branch above.
1. Go to http://jenkins.vfs.va.gov/job/builds/job/cms-test/. Start a new job, using the SHA from the previous step. Allow this to run. This creates an AMI that we will use to deploy to CMS-TEST prod and/or staging.
1. Once the above is complete, go to the deploy job page and use the same SHA from previous steps:
   - Staging: http://jenkins.vfs.va.gov/job/deploys/job/cms-test-vagov-staging/
   - Prod: http://jenkins.vfs.va.gov/job/deploys/job/cms-test-vagov-prod/


## Sync file assets to CMS-TEST PROD
1. `ssm-session vagov-prod cms-test auto`
1. `sudo su --login cms` 
2. `sudo --user apache --shell`
3. `cd /var/www/cms`
4. `scripts/sync-files.sh`

![image](https://github.com/department-of-veterans-affairs/va.gov-cms/assets/39352093/13c94408-3741-4bef-a8af-6d77890b240a)

## To restore an older version of the database to CMS-TEST PROD
1. Pull latest main from va.gov-cms and push to va.gov-cms-test # `git push --force upstream-test` if needed, be careful
1. Get the commit ID of older deployment that is intended to be restored https://github.com/department-of-veterans-affairs/va.gov-cms-test/commits
1. Copy S3 URL from https://us-gov-west-1.console.amazonaws-us-gov.com/s3/buckets/dsva-vagov-prod-cms-backup
1. SSM into server with `ssm-session vagov-prod cms-test auto`
1. `sudo su -u cms` # become apache user
1. `git checkout <commit-id-from-above>`
1. `composer va:nuke`
1. `composer install`
1. `drush status` # verify using the test database
1. `aws --region us-gov-west-1 s3 cp s3://dsva-vagov-prod-cms-backup/database/drupal8-db-prod-2020-12-02-15-00.sql.gz . `
1. `gunzip drupal8-db-prod-2020-12-02-15-00.sql.gz`
1. `drush sql-drop --yes`
1. `drush sql-cli < drupal8-db-prod-2020-12-02-15-00.sql`
