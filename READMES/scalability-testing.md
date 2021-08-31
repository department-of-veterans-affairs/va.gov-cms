# Scalability Testing

In an effort to test scalability of the CMS and the front end web build content has been generated to simulate a larger database.

## Process to generate content

The `entity_clone` module was used to clone actual content to generate realistic content at scale.  Content from a Section can be cloned one at a time.  The logic for what content is cloned is stored within: [docroot/modules/custom/va_gov_clone/src/Plugin/VAGov/CloneHandler] folder.

To trigger a mass cloning call:
1. Enable the `va_gov_clone` module via `drush`.  This module is hidden from the admin UI so `drush` must be used.
  `source /etc/sysconfig/httpd; PATH=$PATH:/usr/local/bin drush en va_gov_clone`
2. Run the drush job `drush va-gov-clone:clone-all <section_tid>`.  For example `drush va-gov-clone:clone-all 335` will clone the content for `VA Ann Arbor health care` section.  Note that this currently only allows one section and does not pick up child sections.
  `source /etc/sysconfig/httpd; PATH=$PATH:/usr/local/bin drush va-gov-clone:clone-all 248`

Follow up tasks:
* Allow limits to be passed in via a command line
* Run the cloning in async.

Database back up uploaded to s3:

s3://dsva-vagov-prod-cms-test-backup-sanitized/benchmark/cms-08.12.2021-50k.sql.gz
on commit: 466a3b15f5e7dd0b572be4564fd394de79521cec

https://test.staging.cms.va.gov/
node count: 50442



