# Scalability Testing

In an effort to test scalability of the CMS and the front end web build content has been generated to simulate a larger database.

## Process to generate content

The `entity_clone` module was used to clone actual content to generate realistic content at scale.  Content from a Section can be cloned one at a time.  The logic for what content is cloned is stored within: [docroot/modules/custom/va_gov_clone/src/Plugin/VAGov/CloneHandler] folder.

To trigger a mass cloning call:
1. Enable the `va_gov_clone` module via `drush`.  This module is hidden from the admin UI so `drush` must be used.
2. Run the drush job `drush va-gov-clone:clone-all <section_tid>`.  For example `drush va-gov-clone:clone-all 335` will clone the content for `VA Ann Arbor health care` section.  Note that this currently only allows one section and does not pick up child sections.

Follow up tasks:
* Allow limits to be passed in via a command line
* Run the cloning in async.

## Current generated content (OUTDATED 8/9/21) :

All content is stored in the public S3 bucket `dsva-vagov-prod-cms-backup-sanitized` in the `benchmark` folder.


| Node Count | Pages.json link | SQL dump |
|------------|-----------------|----------|
| 14228 | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/14228-pages.json | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/14228.sql.gz |
| 22410 | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/22410-pages.json | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/22410.sql.gz |
| 50658 | NA | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/50658.sql.gz |

