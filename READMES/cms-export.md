# CMS Export System Documentation


## Overview

The CMS Export system is a term for an unconventional approach to make all published content and file assets in the CMS available as two TAR archive files. The TAR files are generated on demand and always up to date with the most recently published content. 

This system was chosen for performance and scalability, but also found to be superior over traditional Restful, JSON API or existing GraphQL approach since the primary use case for the build system is to always request a full content export. 

The content and file assets are made available for the consumption of the Content Build process at the following two endpoints (metrics are as of this writing):

1. Endpoint: [https://prod.cms.va.gov/cms-export/content](https://prod.cms.va.gov/cms-export/content) \
Filename: cms-content-export-latest.tar \
Size: 64MB \
Files: 22,688  \
Response time: ~0.5s 

2. Endpoint: [https://prod.cms.va.gov/cms-export/asset](https://prod.cms.va.gov/cms-export/asset) \
Filename: cms-asset-export-latest.tar \
Size: 879MB \
File: 5,934 \
Response time: ~1.5s


### Motivation

A system that builds and deploys content from prod.cms.va.gov to [www.va.gov](www.va.gov) within a minute of being published is paramount to providing modern editorial experience within a decoupled CMS architecture. There are editors who are migrating to the CMS and are expecting to publish content in under a minute based on experiences with the Teamsite CMS. 

There are three main components that were taking up time in the content build & deploy process. The build script, the content request from CMS and the deployment itself. If we were to use traditional API methods and better caching management it is estimated that maybe we could get the entire process down to 30 seconds and the build script would also have to be refactored to make many hundreds and eventually thousands of requests. The requests would be made faster because of better caching but there would have been thousands _per build_, and multiplying that by a multitude of PRs that build off of PROD, would have been quite large.

The approach taken to export all content as JSON basically moved the cache to disk and solved this issue mentioned above. 

As this is a 100% static build process, the usual instant publishing aspect of Drupal is not applicable in this case and outside the box thinking was required as the CMS was already taking up 90+ seconds for PROD builds (and 3-4 minutes on DEV/STAGING builds) just to serve the existing ~1,000 items of content and would only slow more as content increased, while also pushing memory consumption higher and higher on both the CMS server and Jenkins build server.

We discussed incremental build approaches and decided against it for 3 reasons:

*   Timeline, we initially had a short timeline of just a month or two
*   Complexity, incremental build approaches introduce complexity which introduces risk, and given the timeline the extra risk was not something we wanted to explore, given that we had experimented with a full content build system using the CMS Export (Tome Sync) approach and it was very fast. We could achieve our sub-1 minute total build and deploy goal without the extra risk that came with incremental build approaches. 
*   We still needed fresh, non-incremental builds on occasion, and those would still need a full content request. The way around that would have been to manage a base content state in a central content store, and incrementally update that state and have everything fetch from that state store. 


## How does it work

A key part of the approach was based on the fact that we have very fast, high IOPS SSDs and high bandwidth connections. This allowed us to use just TAR for on demand archiving/tarring of the file folders, instead of using compression e.g. gzip. As gzip would take 10+ seconds for the same sub-second operation, and the savings from gzip were not enough to save time on network transfer as we have 3Gbps network links now and can achieve up to 10Gbps with the use of AWS EC2 placement groups, and the future shows that high bandwidth network links will only continue to increase. 

**@todo &lt;diagram showing how it is consumed by the build system>**

**@todo &lt;diagram showing the deployment state backup/restoration>**


## Implementation Notes

Every piece of published content in the CMS is exported starting at the Node level, which is made up of fields and sub-entities that are embedded inside the Node. For example a regular piece of content is made up of about 10 JSON files total exported per piece of content (a node). The top level JSON file references the entity UUID of the sub-entities, and the frontend build script stitches together all of these references (very quickly).

The way we achieved this is by using the Drupal 8 module, Tome Sync. With Tome Sync there was an initial, one-time export on prod.cms.va.gov to the sites/default/files/cms-content-export folder which initially exported about ~22,000 JSON files. Then, upon every publish operation it updates just the files that changed, on disk.

### Modifications to Tome Sync

4 modifications were necessary to the va_gov_content_export.module:

1. Breadcrumbs
2. Entity ID
3. “processed” field
4. Metatags

### Modifications to the CMS deployments/server infrastructure

In order to make this performant on the CMS we had to transition from an AWS EFS network disk volume to a native AWS EBS disk volume. 

The deployments were modified to backup the state of the content export folder before deployment, the site put into a mode where editors were not allowed to upload any more files for about 10 minutes and then the new deployment instance pulls down the state and restores it, then the editors can upload files again.

## Caveats

This only works with a single instance, and not in a multiple instance High Availability setup (HA) which we had not upgraded to yet anyways and have plans to do so. 

## Future work

1. Work is in progress to come up with a solution to allow the CMS Export to work in a High Availability setup (HA) which we will need in the months to come so that we can scale the CMS load over multiple instances and availability zones to increase redundancy. 
2. Work is in progress to decrease this deployment window to less than 5 minutes. 
3. The GraphQL system is still being used for a part of the build process, specifically the sidebar menus. The time for that request is minimal, 1-2 seconds. Moving that into the export system should be considered for maintainability and to reduce complexity, but the lift on that was not worth the 2 seconds in savings right now. 
