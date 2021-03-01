# Scalability Testing

In an effort to test scalability of the CMS and the front end web build content has been generated to simulate a larger database.

## Process to generate content

The `entity_clone` module was used to clone actually content to generate realistic content at scale.  The VISN2 and VISN4 facility nodes were clone several times.

Currently the tids for to filter the nodes is hard coded in the `scripts/clone.php` script.

To trigger a mass cloning call `drush scr scripts/clone.php`.  This will start a process which generate up to 10000 new nodes.

Follow up times:
* Change to drush 10 script rather than php file.
* Allow limits to be passed in via a command line
* Allow the tid to be passed in to clone.
* Run the cloning in async.

## Current generated content:

All content is stored in the public S3 bucket `dsva-vagov-prod-cms-backup-sanitized` in the `benchmark` folder.


| Node Count | Pages.json link | SQL dump |
|------------|-----------------|----------|
| 14228 | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/14228-pages.json | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/14228.sql.gz |
| 22410 | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/22410-pages.json | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/22410.sql.gz |
| 50658 | NA | https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/benchmark/50658.sql.gz |

