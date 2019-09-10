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
    1. [Decoupled Drupal Implementation](#decoupled-drupal-implementation)
    1. [Continuous Integration](#continuous-integration)
1. **Developer Info**
    1. [Getting Started](READMES/getting-started.md)
    1. [WEB & CMS Integration](READMES/unity.md)
    1. [Workflow](READMES/workflow.md)
    1. [Project Conventions](READMES/project-conventions.md)
    1. [Environments](READMES/environments.md)
        1. [CI Environments](READMES/cms-ci.md)
        1. [Local - Lando](READMES/local.md)
        1. [BRD Environments](READMES/brd.md)
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
[Architecture Overview](READMES/overview.md) for high level details.

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

### Decoupled Drupal Implementation

The public website seen at [VA.gov](https://www.va.gov) is a static site: just HTML, CSS, and images.

The source code used to generate the public site is called *vets-website* or *Front-end* or **WEB**, and is availalble 
at [github.com/department-of-veterans-affairs/vets-website](https://github.com/department-of-veterans-affairs/vets-website).

The **CMS** codebase now includes the **WEB** codebase as a dependency: the version is set in `composer.json`. It is 
downloaded to the `./web` folder during `composer install`.

When the content and code updates are ready for release, the **WEB** Build process is kicked off, it reads 
content from the [CMS](https://cms.va.gov) via GraphQL (and other locations), and outputs HTML, CSS, and images.

See [WEB & CMS Integration](READMES/unity.md) for full details on how the WEB and CMS projects work together.

### Continuous Integration & Testing

The **CMS** project is running continuous integration and testing (**CMS-CI**) with [DevShop](https://getdevshop.com).

A customized DevShop is deployed to [devshop.cms.va.gov](https://devshop.cms.va.gov). The customizations are contained 
in the **DEVOPS** repo, which is *not* open source. See [DevShop Customizations](readmes/devshop.md)

All pull requests submitted to the [va.gov-cms](https://github.com/department-of-veterans-affairs/va.gov-cms) project 
get a running instance of the **CMS** on their branch, along with a dedicated **WEB** instance. See [Deployment Workflow](READMES/deployment.md) 
for more details.

Access to DevShop is also restricted to CAG.  See [Getting Access](READMES/access.md).  


