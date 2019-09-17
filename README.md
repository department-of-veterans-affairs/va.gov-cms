# VA.gov CMS

Welcome to the VA.gov CMS git repository README!

We hope everything you need to know about how the [VA.gov](https://www.va.gov) website and Content Management System works is right here.

If you find any errors in this documentation, please feel free to [make an edit and submit a Pull Request](https://github.com/department-of-veterans-affairs/va.gov-cms/edit/VAGOV-2303-readme/README.md)!

Thanks,

The VA.gov Team.



## Table of Contents
1. **Introduction**
    1. [About VA.gov](#about-vagov)
    1. [Contributing](#contributing)
    1. [High Level Architecture](#highlevel-architecture)
    1. [Infrastructure](#infrastructure)
    1. [Sites](#sites)
1. **Developer Info**
    1. [Getting Started](READMES/getting-started.md)
    1. [WEB & CMS Integration](READMES/unity.md)
    1. [Workflow](READMES/workflow.md)
    1. [Project Conventions](READMES/project-conventions.md)
    1. [Environments](READMES/environments.md)
        1. [CI Environments](READMES/cms-ci.md)
        1. [Local - Lando](READMES/local.md)
        1. [BRD Environments](READMES/brd.md)
        1. [HTTPS](READMES/https.md)
    1. [Testing](READMES/testing.md)
    1. [Debugging](READMES/debugging.md)
1. **Release & Deployment**
    1. [The BRD System: Build, Release, Deploy](READMES/brd.md)
    1. [CMS Release Process](READMES/brd.md#cms-release-process)
    1. [CMS-CI Release Process](READMES/brd.md#cmsci-release-process)
1. **Architecture**
    1. Overview
    1. Drupal
    1. MetalSmith
    1. [Interfaces](READMES/interfaces.md) - API's and Feature Flags

## Introduction

### About VA.gov 

The [VA.gov](https://www.va.gov) website is made possible by a number of different tools and systems. See 
[High-Level Architecture](#highlevel-architecture) for an overview of all of the components.

This repository contains the source code for the *Content Management System* (**CMS** or **CMS-API**)
for [VA.gov](https://www.va.gov), running at [cms.VA.gov](https://cms.va.gov).

Access to the production CMS is restricted with CAG. See [Getting Access](READMES/access.md).

The **CMS** is built on [Drupal 8](https://www.drupal.org), using the [Composer](https://getcomposer.org) package management system. See [Getting Started](READMES/getting-started.md).

### Contributing

All of the source code used for generating VA.gov is open source, listed under the [department-of-veterans-affairs](https://github.com/department-of-veterans-affairs) 
organization on GitHub:

- **CMS**: [github.com/department-of-veterans-affairs/va.gov-cms](https://github.com/department-of-veterans-affairs/va.gov-cms) - Drupal 8, Lightning Distribution
- **WEB**: [github.com/department-of-veterans-affairs/vets-website](https://github.com/department-of-veterans-affairs/vets-website) - Metalsmith
- **VETS-API**: [github.com/department-of-veterans-affairs/vets-api](https://github.com/department-of-veterans-affairs/vets-api) - Ruby
- **VETS-CONTENT**: [github.com/department-of-veterans-affairs/vagov-content](https://github.com/department-of-veterans-affairs/vagov-content) - Markdown

The VFS team deploys all of these apps using a Jenkins server, configured with a private GitHub Repo: 

- **DEVOPS**: [github.com/department-of-veterans-affairs/devops](https://github.com/department-of-veterans-affairs/devops)

All development on these projects is done via Pull Requests.  See [CONTRIBUTING.md](CONTRIBUTING.md) for our PR policies.

### High-Level Architecture

The public website seen at [VA.gov](https://www.va.gov) is a static site: just HTML, CSS, and images.

The source code used to generate the public site is called *vets-website* or *Front-end* or **WEB**, and is availalble 
at [github.com/department-of-veterans-affairs/vets-website](https://github.com/department-of-veterans-affairs/vets-website).

#### Decoupled Drupal

The codebase in [this repository (va.gov-cms)](https://github.com/department-of-veterans-affairs/va.gov-cms) is for the 
**CMS**, which is built on Drupal 8. The **CMS** is not publicly available. It 
acts as a *Content API* for the **WEB** application, and a *Content Management System* for VA.gov Content Team.

The **CMS** codebase now includes the **WEB** codebase as a dependency: the version is set in `composer.json`. It is 
downloaded to the `./web` folder during `composer install`.

#### Build and Release Process 

When the content and code updates are ready for release, the **WEB** Build process is kicked off, it reads 
content from the [CMS](https://cms.va.gov) via GraphQL (and other locations), and outputs HTML, CSS, and images.

See [WEB & CMS Integration](READMES/unity.md) for full details on how the WEB and CMS projects work together.

### Infrastructure

This section outlines only the systems utilized by the CMS. For information on the **WEB** project's infrastucture, see 
[]().

#### CMS-CI: Pull Request and Ad-hoc Environments

 - Running OpenDevShop at [devshop.cms.va.gov](http://devshop.cms.va.gov). Access restricted to CAG, sign in with GitHub.
 - A single "mirror" environment is regularly populated with a sanitized production database copy.
 - Open Pull Requests get environments created automatically, cloned from the "mirror" environment, with URLS like 
 [pr123.ci.cms.va.gov](http://pr123.ci.cms.va.gov) and
   a **WEB** instance built from that PR environment's content, like [pr123.web.ci.cms.va.gov](http://pr123.web.ci.cms.va.gov).
 - Ad-hoc environments can be created and deleted at any time by any logged in user to [devshop.cms.va.gov](http://devshop.cms.va.gov): 
   - Can be named anything and can be set to any branch or Pull Request.
   - These environments will not change or be rebuild unless the creator chooses.
   - Useful for testing and demos outside of the CMS-CI process.
 - Single EC2 Instance: @TODO: List size, storage, etc.

#### CMS in BRD: Dev, Staging, Production

The VFS Team maintains a system called **BRD**: *Build, Release, Deploy.*

The system is powered by Ansible Roles in the VA's "DevOps" repository, located at [github.com/department-of-veterans-affairs/devops/tree/master/ansible/build/roles/cms](https://github.com/department-of-veterans-affairs/devops/tree/master/ansible/build/roles/cms)

The **BRD** system builds Amazon server images using Ansible, and tags those 
images for release along with the source code of CMS.

The VFS team then deploys those images to the *dev*, *staging* and *production* environments inside the VAEC when the release is ready.

See [The BRD System: Build, Release, Deploy](READMES/brd.md) for more information.