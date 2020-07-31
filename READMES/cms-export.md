# CMS Export System Documentation


## Overview

The CMS Export system is a term for an unconventional approach to make all published content and file assets in the CMS available as two TAR archive files. The TAR files are generated on demand and always up to date with the most recently published content.

This architecture was chosen for performance and scalability, but also found to be superior over traditional Restful, JSON API or GraphQL approaches since the primary use case for the build system is to always request a full content export

The content, file assets, and content schema are made available for the consumption of the Content Build process at the following three endpoints (metrics are as of this writing):
### Endpoints

1. Content Endpoint: [https://prod.cms.va.gov/cms-export/content](https://prod.cms.va.gov/cms-export/content) \
Filename: cms-content-export-latest.tar \
Size: 64MB \
Files: 22,688  \
Response time: ~0.5s

2. Asset Endpoint: [https://prod.cms.va.gov/cms-export/asset](https://prod.cms.va.gov/cms-export/asset) \
Filename: cms-asset-export-latest.tar \
Size: 879MB \
Files: 5,934 \
Response time: ~1.5s

3. Schema Endpoint: [https://prod.cms.va.gov/openapi/jsonapi?_format=json](https://prod.cms.va.gov/openapi/jsonapi?_format=json) \
Description:  The is an OpenApi schema that describes the data structure of the CMS content. Provided by the
[openapi module](https://www.drupal.org/project/openapi). This same data is included as a file in the content export TAR as '/cms-export-content/meta/schema.json' at the instant the TAR is requested. \
Authorization: basic_auth for api_consumer role. (same as GraphQL endpoint) \
Size: ~4MB \

### Motivation

A system that builds and deploys content from prod.cms.va.gov to [www.va.gov](www.va.gov) within a minute of being published is paramount to providing modern editorial experience within a decoupled CMS architecture. There are editors who are migrating to the CMS and are expecting to publish content in under a minute based on experiences with the Teamsite CMS.

There are three main components that were taking up time in the content build & deploy process. The build script, the content request from CMS and the deployment itself. If we were to use traditional API methods and better caching management it is estimated that maybe we could get the entire process down to 30 seconds and the build script would also have to be refactored to make many hundreds and eventually thousands of requests. The requests would be made faster because of better caching but there would have been thousands _per build_, and multiplying that by a multitude of PRs that build off of PROD, would have been quite large.

The approach to export all content as JSON has moved the cache to disk and solved the issue mentioned above.

As this is a 100% static build process, the usual instant publishing aspect of Drupal is not applicable in this case and outside the box thinking was required as the CMS was already taking up 90+ seconds for PROD builds (and 3-4 minutes on DEV/STAGING builds) just to serve the existing ~1,000 items of content and would only slow more as content increased, while also pushing memory consumption higher and higher on both the CMS server and Jenkins build server.

We discussed incremental build approaches and decided against it for 3 reasons:

*   **Timeline**. We initially had a short timeline of 1-2 months.
*   **Complexity**. Incremental build approaches introduce complexity and risk. Given the timeline, the extra risk was not something we wanted to explore. We had experimented with a full content build system using the CMS Export (Tome Sync) approach and it was very fast. We could achieve our sub-1 minute total build and deploy goal without the extra risk that came with incremental build approaches.
*   **Full Export Still Needed**. We would have still needed fresh, non-incremental builds on occasion, and those would still need a full content request. The way around that would have been to manage a base content state in a central content store, and incrementally update that state and have everything fetch from that state store, which would have added complexity.

## How does it work

A key part of the CMS Export architecture is based on the fact that we have very fast, high IOPS SSDs and high bandwidth connections. This allowed us to use TAR for on demand archiving/tarring of the file folders, instead of using compression such gzip. As gzip would take 10+ seconds for the same sub-second TAR operation, and the savings from gzip were not enough to save time on network transfer as we have 3Gbps network links now and can achieve up to 10Gbps with the use of AWS EC2 placement groups, and the future shows that high bandwidth network links will only continue to increase.

TODO: Link to diagram showing how it is consumed by the build system


## Implementation Notes

Every piece of published content in the CMS is exported starting at the Node level, which is made up of fields and sub-entities that are embedded inside the Node. For example a regular piece of content is made up of about 10 JSON files total exported per piece of content (a node). The top-level JSON file references the entity UUID of the sub-entities, and the frontend build script stitches together all of these references (very quickly).

The way we achieved this is by using the Drupal 8 module, [Tome Sync](https://git.drupalcode.org/project/tome/-/tree/8.x-1.x/modules/tome_sync). With Tome Sync there was an initial, one-time export on prod.cms.va.gov to the sites/default/files/cms-content-export folder which initially exported about ~22,000 JSON files. Then, upon every publish operation it updates just the files that changed, on disk.

### Modifications to Tome Sync

4 modifications were necessary to Tome Sync's output which we did with the [va_gov_content_export.module](https://github.com/department-of-veterans-affairs/va.gov-cms/tree/master/docroot/modules/custom/va_gov_content_export). These changes were not contributed back upstream because the use case for Tome was different than ours and the maintainer specifically made Tome for exporting a site, so that the entire state could be saved in a git repository and re-imported back up. The intent was to _only_ store information that could not be regenerated, and breadcrumbs, metatags, and the "processed" field are all generated items.

1. Add breadcrumbs - [#3092559](https://www.drupal.org/project/tome/issues/3092559)
1. Add Entity ID - [#3089524](https://www.drupal.org/project/tome/issues/3089524)
1. Add “processed” field (computed WYSIWYG field) - [#3096687](https://www.drupal.org/project/tome/issues/3096687)
1. Add Metatags
1. Do not export config
1. Changed how Tome Sync interacted with the file system
1. Add the ability to exclude entity types from exporting [#3114961](https://www.drupal.org/project/tome/issues/3114961)

### Modifications to the CMS deployments/server infrastructure

In order to make this performant on the CMS we had to transition from an AWS EFS network disk volume to a native AWS EBS disk volume.

The deployments were modified to the following sequence of events:

* the state of the content export folder before deployment is backed up
* the site is put into a mode where editors are not allowed to upload any more files for about 10 minutes
* the new deployment instance pulls down the state and restores it
* the editors can upload files again

TODO: Create a diagram showing the deployment state backup/restoration

## Caveats

1. This architecture only works with a single instance right now, and not in a multiple instance High Availability setup (HA) which we had not upgraded to yet anyways but have plans to do so in https://github.com/department-of-veterans-affairs/va.gov-cms/issues/1716.
1. Content model changes require a new, full export on deploy otherwise only newly published content would get the content model updates. Until the automation export on content model change story is complete in https://github.com/department-of-veterans-affairs/va.gov-cms/issues/1851. Ideally the new export steps below would be just after the `drush config:import` Ansible task in the deploy but we cannot pause a deploy so it needs to be after the deploy. Here are the manual steps for a fresh export that captures the content model changes:
    1. Login to server then `cd /var/www/cms`
    1. Run the export (Should take about 3 minutes): `sudo -u apache bash -c 'source /etc/sysconfig/httpd; /usr/local/bin/drush va-gov-cms-export-all-content  --process-count=8 --entity-count=500 --delete-existing'`

## Roadmap

1. [WIP] https://github.com/department-of-veterans-affairs/va.gov-cms/issues/1716 - Explore a solution to allow the CMS Export to work in a High Availability setup (HA) which we will need in the months to come so that we can scale the CMS load over multiple instances and availability zones to increase redundancy. This solution requires a high performance networked filesystem of which EFS is _not_ high performance.
1. [WIP] https://github.com/department-of-veterans-affairs/va.gov-cms/issues/1713 - Decrease deployment window to less than 5 minutes.
1. [WIP] https://github.com/department-of-veterans-affairs/va.gov-cms/issues/1849 - Automate the export based on content model changes by looking for changes to the [/tests/behat/drupal-spec-tool](https://github.com/department-of-veterans-affairs/va.gov-cms/tree/master/tests/behat/drupal-spec-tool) folder.
1. The GraphQL system is still being used for a part of the build process, specifically the sidebar menus. The time for that request is minimal, 1-2 seconds. Moving that into the export system should be considered for maintainability and to reduce complexity. The effort required to implement was not worth the 2 seconds in savings at this time.
