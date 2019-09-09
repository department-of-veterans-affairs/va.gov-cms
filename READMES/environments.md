# Environments

Domains for this application are below, they both correspond to a frontend (FE) domain that consumes data from the CMS via GraphQL API endpoint at /graphql:

| Environement | Drupal (CMS)           | Frontend (FE)              |
| ------------ | ---------------------- | -------------------------- |
| DEV          | dev.cms.va.gov         | dev.va.gov                 |
| STAGING      | staging.cms.va.gov     | staging.va.gov             |
| PROD         | prod.va.gov            | www.va.gov                 |
| [LOCAL](local.md)        | va-gov-cms.lndo.site   | va-gov-web.lndo.site  -or- va-gov-cms.lndo.site/static |
| UNITY        |  pr###.ci.cms.va.gov   |  pr###.ci.cms.web.va.gov -or-  pr###.ci.cms.va.gov/static |



## Hosting Architecture
Hosted on VA GovCloud in AWS GovCloud
SOCKS proxy or PIV+GFE hardware required for accessing VA internal network.


In process of migrating to VSP’s platform in VA GovCloud (including Jenkins and CMS sites) - often referred to as ‘unity project’
* [Jenkins](http://jenkins.vfs.va.gov/) controls many of the backup and deployment tasks
* [DevShop](http://devshop.cms.va.gov) controls the pull request environments as well as on demand Unity environments and running of cms and Front End tests.
Elijah & John Pugh are currently working on replacing homegrown scripts with a DevShop implementation (https://devshop.cloud/) within VA cloud

Resources
https://va-gov.atlassian.net/wiki/spaces/VAGOV/pages/103448589/VA.gov+CMS+DevOps+2.0+Architecture+Notes
https://va-gov.atlassian.net/wiki/spaces/VAGOV/pages/28770332/CMS+Infrastructure+CI+CD+Architecture+Proposal+3


[Table of Contents](../README.md)
