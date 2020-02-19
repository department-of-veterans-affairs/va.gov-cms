# Environments

Domains for this application are below, they both correspond to a frontend (FE) domain that consumes data from the CMS via GraphQL API endpoint at /graphql:

| Environment       | Drupal (CMS)                                         | Frontend (FE / WEB                                                                  | Management
| -----------       | ------------                                         | ------------------                                                                  | ----------  
| DEV               | [dev.cms.va.gov](https://dev.cms.va.gov)             | [dev.cms.va.gov](https://dev.va.gov)                                                | [BRD: Jenkins](http://jenkins.vfs.va.gov/job/deploys/view/Dev/job/cms-vagov-dev/)
| STAGING           | [staging.cms.va.gov](http://staging.cms.va.gov)      | [staging.cms.va.gov](http://staging.va.gov)                                         | [BRD: Jenkins](http://jenkins.vfs.va.gov/job/deploys/view/Staging/job/cms-vagov-staging/)
| PROD              | [prod.cms.va.gov](http://prod.cms.va.gov)                    | [www.va.gov](http://www.va.gov)                                                     | [BRD: Jenkins](http://jenkins.vfs.va.gov/job/deploys/view/Prod/job/cms-vagov-prod/)
| [LOCAL](local.md) | [va-gov-cms.lndo.site](http://va-gov-cms.lndo.site)  | [va-gov-web.lndo.site](http://va-gov-web.lndo.site)
|                   |                                                      | [va-gov-web.lndo.site/static](http://va-gov-web.lndo.site)  
|                   |                                                      | [va-gov-web.lndo.site/$URL?_format=static_html](http://va-gov-web.lndo.site/$URL)  
| CI / PR           | pr###.ci.cms.va.gov                                  |  pr###.ci.cms.web.va.gov                                                            | [CMS-CI: DevShop](http://devshop.cms.va.gov/)
|                   | [devshop.cms.va.gov](https://devshop.cms.va.gov/)    |  pr###.ci.cms.va.gov/static                                                         
|                   | Visit the DevShop site to view all PR environments   |  pr###.ci.cms.va.go/$URL?_format=static_html


## Hosting Architecture

All environments are hosted on VA GovCloud in AWS GovCloud.

The primary environments, DEV, STAGING, and PROD, are hosted in the *BRD* system.

Pull Request Environments and Ad Hoc environments are hosted in the *CMS-CI* system.

SOCKS proxy or PIV+GFE hardware is required for accessing VA internal network.

### BRD: Jenkins

> Build, Release, Deploy
 
[jenkins.vfs.va.gov](http://jenkins.vfs.va.gov/)

  * Source Code: https://github.com/department-of-veterans-affairs/devops/tree/master/ansible/build/roles/cms
  * Runs Continuous Integration for about a dozen different applications with different requirements, including `vets-website`, `vets-api`, `cms`, and soon `cms-ci`.
  * BRD Process is standardized across apps using Ansible playbooks and roles. See the [DevOps Repo Documentation](https://github.com/department-of-veterans-affairs/devops/blob/master/README.md) for more information.
    * The "Build" process creates the entire server image and permantently tags and archives it as an AMI.
    * The "Deploy" process delivers those images to the 3 "environments", *DEV, STAGING, and PROD* and runs whatever 
      hooks are needed. 
    * The "Release" process continuously delivers code to each BRD Environment
    
        - Primary branch commits are automatically deployed to DEV and STAGING Environments. 
        - Git Tags and GitHub Releases are created automatically if those commits pass testing.
        - "Environments" in the context of **BRD** are really different networks. 
        - The "servers" that actually run the apps are activated AMI images, placed into the desired "environment".
        
###CMS-CI: DevShop 

> Open Source Drupal CI Platform

[devshop.cms.va.gov](http://devshop.cms.va.gov)
  
  * *CMS-CI* refers to the VA's implementation of DevShop. There are some extra modules and settings that are added to 
      DevShop specific for the VA. The extra tools are contained in the [CMS-CI role in the DevOps Repo]().
  * Provides an environment per Pull Request, and allows creation of ad-hoc environments with any name, on any desired 
    branch or Pull Request.
  * Provides a Web UI for getting information and managing these environments
  * Installed with open source Ansible roles, plus a custom playbook. 
  * Deployment of new releases of CMS-CI and DevShop is handled by BRD in way very similar to CMS.
  * Provides a complete SDLC pipeline for Drupal code: 
      * Creates new environments when a PR is open.
      * Automatically tests the environment and passes status to GitHub to allow or block merging.
      * Destroys and rebuilds PR Environments and runs the full test suite again on every git push.
      * If the PR is merged or closed, environment is destroyed.
      * Notifies GitHub of deployment success or failure, with lnks to the environments.
  * Includes the FE/WEB Build process in the Drupal CI pipeline.
      * Front-end WEB project is built inside PR environments automatically.
      * End-to-end testing of CMS+WEB with Behat: 
        1. Make CMS updates: Change content, publish state, etc.
        1. Run WEB build command to rebuild static assets.
        1. Confirm CMS change is visible in WEB static assets.
  * Runs on a single EC2 instance.
        
# Resources

- https://va-gov.atlassian.net/wiki/spaces/VAGOV/pages/103448589/VA.gov+CMS+DevOps+2.0+Architecture+Notes
- https://va-gov.atlassian.net/wiki/spaces/VAGOV/pages/28770332/CMS+Infrastructure+CI+CD+Architecture+Proposal+3


[Table of Contents](../README.md)
