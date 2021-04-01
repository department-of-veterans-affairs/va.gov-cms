These instructions are not tested yet (from memory), proceed with caution.

## Scenarios
### 1) A `drush deploy` fails because of a boostrap failure (open upstream issue https://github.com/drush-ops/drush/issues/4702)
Continue the deployment manually:
1. `source /etc/sysconfig/httpd; /usr/local/bin/drush deploy`
1. `source /etc/sysconfig/httpd; /usr/local/bin/drush va-gov-enable-deploy-mode`
1. Run http://jenkins.vfs.va.gov/job/cms/job/deploy-live-prod/

### 2) Bad code e.g. VBO module update breaks some drush commands needed for deploy. The deploy job fails, the deploy live job has not run yet.
#### 2A) ROLLBACK: Complete deploy manually: drush deploy (updatedb & config:import) HAS NOT run yet . prod.cms.va.gov is still pointed at old instance (in deploy mode)
1. Abandon/remove new instance with:
   ```
   aws autoscaling complete-lifecycle-action \
      --region us-gov-west-1 \
      --auto-scaling-group-name "dsva-vagov-prod-cms-asg" \
      --lifecycle-hook-name launch-hook \
      --lifecycle-action-result ABANDON \
      --instance-id i-0f256f4eae72d5c87
   ```
1. Scale-in ASG from 2 to 1 (this may change later as we move to HA)
1. Remove latest launch-template (first need to set latest-1 to default)
1. Set deployment_version tag on existing instance from ‘previous’ to ‘latest’
1. drush cache:rebuild
1. Run post-live job (removes site-alerts and lets users know deploy is done)
1. rm /var/www/cms/docroot/sites/default/settings/settings.deploy.active.php (settings.deploy.inactive.php should still exist) (this brings in all the traffic again)
#### 2B) drush deploy (updatedb & config:import) HAS run
1. TODO: Need to revert to database snapshot just before deploy started


## Post-Tasks
1. If Periodic job was disabled for any reason, make sure to re-enable it here.
   http://jenkins.vfs.va.gov/job/cms/job/cms-periodic-prod/
