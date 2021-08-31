# Build Release Deploy (BRD)

The "BRD" system is designed and maintained by the VSP DevOps Team using the 
[DevOps GitHub Repository](https://github.com/department-of-veterans-affairs/devops/ansible).

The BRD system is used for many different apps in different languages. It uses 
Ansible roles to standardize a testing and release process across all of these 
apps.

See the [README.md](https://github.com/department-of-veterans-affairs/devops/blob/master/README.md) 
file from the DevOps Repo for more information. (This is a private repository. 
For access, talk to your supervisor or see the [VA.gov-team git repository issue queue](https://github.com/department-of-veterans-affairs/va.gov-team).)

## BRD OS

The BRD system uses the same OS for all supported apps: Amazon Linux v1. 

This is roughly equivalent to CentOS 6. To get this working, numerous variable 
overrides and tasks are needed. 
## CMS Server Configuration

The BRD system contains an Ansible role for the CMS, located in the 
`ansible/build/roles/cms` folder of [the DevOps Repo](https://github.com/department-of-veterans-affairs/devops/tree/master/ansible/build/roles/cms).

The CMS role includes the `geerlingguy.drupal` role which includes all the core
 requirements for Drupal.

It also includes a number of customizations to ensure it works inside the BRD 
and VAEC Enterprise Cloud network.

See the [`main.yml`](https://github.com/department-of-veterans-affairs/devops/blob/master/ansible/build/roles/cms/meta/main.yml) file for the roles and variable overrides needed to get CMS 
running on BRD servers. 

## CMS Release Process

The BRD system uses a standard release process for all of the supported apps:
  
1. A [Jenkins job](http://jenkins.vfs.va.gov/job/deploys/job/cms-auto-deploy/) reads the source git repository and checks the latest commit 
of the default branch.
2. If all commit status checks are passing, it automatically tags and verifies 
a GitHub release: https://github.com/department-of-veterans-affairs/va.gov-cms/releases
3. A new GitHub release triggers a **Build**, **Deploy**, and **Test** of a new server image.
4. At first the build goes to **Stage**. 
5. At a pre-scheduled time each day, a new release is announced, and can be stopped 
within one hour. If not stopped, the latest **Build** image is deployed to **Production**.

### Canceling/Delaying the Automatic Release

1. Disable the [auto-deploy prod job](http://jenkins.vfs.va.gov/job/deploys/job/cms-auto-deploy/).
  - If too late, and the auto deploy prod job has already been triggered and we are in the 60 minute warn window, cancel the job and any downstream jobs that were triggered as well.
  - Update the [site-alert in CMS](https://prod.cms.va.gov/admin/config/system/site-alerts) to remove any site-alert that was created by the auto-deploy job.
3. When release is ready [create a new site-alert](https://prod.cms.va.gov/admin/config/system/site-alerts) letting users know when the deploy is happening.
  - This will depend on the urgency, so it could be 5 minutes or could be 60 minutes or anywhere in between.
4. Re-enable the [auto-deploy prod job](http://jenkins.vfs.va.gov/job/deploys/job/cms-auto-deploy/).
5. [Trigger the auto-deploy prod job manually](http://jenkins.vfs.va.gov/job/deploys/job/cms-auto-deploy/build?delay=0sec) and fill out the warn time, e.g. 5 min or 60 min.

## CMS Ansible Tasks

See https://github.com/department-of-veterans-affairs/devops/tree/master/ansible/cms 
to view the additional Ansible tasks that are available to run on the site:

- `cms_cron_run.yml`	Cron job for the site.
- `cms_db_backup.yml`	Backup Database,
- `cms_db_sanitize.yml`	Sanitize Database Backup.
- `cms_efs_backup.yml`  Backup files directory.

## CMSCI Release Process

@todo 

----------------------------


[Table of Contents](../README.md)
