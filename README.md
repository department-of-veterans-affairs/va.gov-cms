# VA.gov CMS

This is the public/open documentation for the VA.gov Content Management System (CMS) for development, QA and DevOps topics. For product, design, support, research and cross-team documentation, visit the [platform/cms docs](https://github.com/department-of-veterans-affairs/va.gov-team/tree/master/platform/cms). For private/sensitive documentation, visit the [private docs repo](https://github.com/department-of-veterans-affairs/va.gov-team-sensitive/tree/master/platform/cms). See [sensitive-guidance.md](https://github.com/department-of-veterans-affairs/va.gov-team/blob/master/platform/working-with-vsp/policies-work-norms/sensitive-guidance.md) to read about what should be public vs. private. We follow the U.S. Digital Services Playbook and [default to open/public](https://playbook.cio.gov/#play13)).

[VA.gov](https://www.va.gov) is constructed at the highest level by three projects:
- the **CMS** or **Content Management System**, in this repository
- the [**vets-website**](https://github.com/department-of-veterans-affairs/vets-website/) project, covering the many special- and general-purpose applications making up the website
- the [**content-build**](https://github.com/department-of-veterans-affairs/content-build/) project, which extracts and processes the content from the CMS for display on the website

## Contributing to this documentation

If you find any improvements to make to this documentation and you have push access, please commit your changes directly to the `main` branch. Please prefix your commit message with `[DOCS]` e.g. `[DOCS] Commit message ...`.

If you don't have push access, you can submit a pull request for review.

Thanks,

The VA.gov CMS Team

## Table of Contents

1. **Introduction**
   1. [About VA.gov](#about-vagov)
   1. [Contributing](#contributing)
   1. [High-Level Architecture](#high-level-architecture)
   1. [Infrastructure](#infrastructure)
1. **Developer Info**
   1. [Getting Started](READMES/getting-started.md)
   1. [WEB & CMS Integration](READMES/unity.md)
   1. [Workflow](READMES/workflow.md)
   1. [Project Conventions](READMES/project-conventions.md)
   1. [Code Ownership](READMES/codeowners.md)
   1. [Environments](READMES/environments.md)
      1. [CI Environments](READMES/tugboat.md)
      1. [Local - DDEV](READMES/local.md)
      1. [BRD Environments](READMES/brd.md)
      1. [HTTPS](READMES/https.md)
      1. [Environment Variables](READMES/environment-variables.md)
      1. [Kubernetes](READMES/historical/kubernetes.md)
   1. [Alerts](READMES/alerts.md)
   1. [GitHub Workflows](READMES/github-workflows.md)
   1. [Logging into BRD (e.g. production, staging) instances](READMES/brd-login.md)
1. **Quality and Testing**
   1. [Quality Assurance (QA)](READMES/qa.md)
   1. [Development Best Practices](READMES/development-best-practices.md)
   1. [Code Review](READMES/code-review.md)
   1. [Testing](READMES/testing.md)
   1. [Debugging](READMES/debugging.md)
   1. [Comparing GraphQL Output](READMES/comparing-graphql-output.md)
   1. [Dependabot Alerts](READMES/dependabot-alerts.md)
   1. [Dependabot Updates](READMES/dependabot-updates.md)
   1. [Sentry](READMES/sentry.md)
   1. [Profiling with Blackfire](READMES/blackfire.md)
   1. [Scalability Testing](READMES/scalability-testing.md)
1. **Release & Deployment**
   1. [The BRD System: Build, Release, Deploy](READMES/brd.md)
   1. [CMS Release Process](READMES/brd.md#cms-release-process)
   1. [CMS-CI Release Process (TODO)](READMES/brd.md#cmsci-release-process)
1. **Architecture**
   1. Drupal
      1. [Memcache](READMES/drupal-memcache.md)
   1. [Content Models and Documentation](READMES/content-models.md)
      1. [Centralized Content](READMES/content-model-centralized-content.md)
   1. MetalSmith
   1. [GraphQL](READMES/graph_ql.md)
   1. [Interfaces](READMES/interfaces.md) - APIs and Feature Flag
   1. Migrations (data imports)
      1. [Facility](READMES/migrations-facility.md)
      1. [Form](READMES/migrations-forms.md)
   1. [Removing deprecated fields](READMES/remove-deprecated-fields.md)
   1. [Security](READMES/security.md)
   1. [Upstream Dependencies](READMES/upstream-dependencies.md)
   1. [Downstream Dependencies](READMES/downstream_dependencies.md)
1. **CMS Users**
   1. [Login / SSOi](READMES/cms-login.md)
   2. [CMS User Notification Systems](READMES/cms-editor-notifications.md)
   3. [Drupal API Users](READMES/drupal_api_users.md)
1. **CMS Content**
   1. [Workflow](READMES/cms-content-workflow.md)
      1. [Alias Lockdown](READMES/cms-content-workflow.md#alias-lockdown)
      1. [Broken Links](READMES/broken-links.md)
      1. [Content Preview](READMES/cms-content-workflow.md#content-preview)
   1. [Content Release](READMES/cms-content-release.md)
   1. [Section Dashboards](docroot/modules/custom/va_gov_dashboards/README.md)
   1. Facilities
      1. [VAMC Facilities](READMES/vamc-facilities.md)
         1. [VA Lovell / TRICARE](READMES/vamc-facilities-lovell.md)
1. **Historical**
   1. [Main branch name change](READMES/historical/cms-branch-name-change.md)
   1. [Elasticache Investigation](READMES/historical/elasticache.md)
   1. [EWA Rules of Engagement](READMES/historical/ewa-rules-of-engagement.md)

## Introduction

### About the CMS

This repository hosts the source code for the _Content Management System_ (**CMS** or **CMS-API**) utilized by [VA.gov](https://www.va.gov).

The production instance can be found at [prod.cms.va.gov](https://prod.cms.va.gov/). Please note that access to the production CMS is limited; refer to [Getting Access](READMES/access.md) for more information.

Built on [Drupal 10.1](https://www.drupal.org), the **CMS** employs the [Composer](https://getcomposer.org) package management system. To get started, consult [Getting Started](READMES/getting-started.md).

### Contributing

All of the source code used for generating VA.gov is open source, listed under the [department-of-veterans-affairs](https://github.com/department-of-veterans-affairs)
organization on GitHub:

- **CMS**: [github.com/department-of-veterans-affairs/va.gov-cms](https://github.com/department-of-veterans-affairs/va.gov-cms) - Drupal 10.1
- **vets-website**: [github.com/department-of-veterans-affairs/vets-website](https://github.com/department-of-veterans-affairs/vets-website) - Node.js
- **vets-api**: [github.com/department-of-veterans-affairs/vets-api](https://github.com/department-of-veterans-affairs/vets-api) - Ruby
- **content-build**: [github.com/department-of-veterans-affairs/vagov-content](https://github.com/department-of-veterans-affairs/vagov-content) - Node.js, Metalsmith

The VFS team deploys all of these apps using a Jenkins server, configured with a [private repository](https://github.com/department-of-veterans-affairs/devops).

All development on these projects is done via Pull Requests.

### High-Level Architecture

The public-facing website at [VA.gov](https://www.va.gov) is a static site hosted on S3, composed of HTML, CSS, JavaScript, and images.

[This codebase](https://github.com/department-of-veterans-affairs/va.gov-cms) is dedicated to the CMS, built on Drupal 10.

The source code for generating the public site is located in the [vets-website](https://github.com/department-of-veterans-affairs/vets-website) repository. The component responsible for extracting raw content from the CMS and converting it to HTML is developed in a separate repository, [content-build](https://github.com/department-of-veterans-affairs/content-build).

#### Decoupled Drupal

The production instance of the CMS is utilized in two main ways:
- as a tool for the VA.gov Content Team to efficiently create and manage content
- as a repository and API server for the content-build process to publish content to the world at large

#### Build and Release Process

In a continuous integration context, the `content-build` project is managed as a dependency of the CMS project; the build script is executed targeting the local instance and runs all of the content queries performed as part of the normal content release process, to ensure no changes have been introduced to the CMS codebase that break compatibility. A full content build happens at the conclusion of the testing pipeline on our [Tugboat](https://tugboat.qa/) preview environments.

In the normal content release process, GitHub Actions triggers a workflow that targets a mirror (hosted in [Tugboat](https://tugboat.qa/)) of the production CMS instance. It retrieves content from the [CMS](https://cms.va.gov) via GraphQL (and other sources) and generates HTML, CSS, JavaScript, and images. These artifacts are then copied to an s3 bucket, which is then rotated into service to serve website visitors.

### Infrastructure

This section outlines only the systems utilized by the CMS. See the READMEs in the [`vets-website`](https://github.com/department-of-veterans-affairs/vets-website) or [`content-build`](https://github.com/department-of-veterans-affairs/content-build) repositories for more information about those projects.

#### CMS-CI: Pull Request and Demo/Ad-hoc Environments

- Running [Tugboat](https://www.tugboat.qa) ([docs](READMES/tugboat.md)) at [tugboat.vfs.va.gov/](https://tugboat.vfs.va.gov/). Access restricted to CAG, sign in with GitHub.
- A single "mirror" environment is regularly populated with a sanitized production database copy.
- Open Pull Requests get environments created automatically, cloned from the "mirror" environment, with URLs like:
   - [pr123-{hash}.ci.cms.va.gov](https://pr123-{hash}.ci.cms.va.gov) for the CMS
      - Cypress test logs and artifacts, see [Testing](READMES/testing.md) for details.
   - [web-{hash}.ci.cms.va.gov](http://web-{hash}.ci.cms.va.gov) for the frontend web build
   - [storybook-{hash}.ci.cms.va.gov](http://storybook-{hash}.ci.cms.va.gov) for design system documentation
- Ad-hoc environments can be created and deleted at any time by any logged in user on [tugboat.vfs.va.gov/](https://tugboat.vfs.va.gov/):
  - Should be created under "CMS Demo Environments"
  - Can be named anything and can be set to any branch or Pull Request.
  - These environments will not change or be rebuild unless the creator chooses.
  - Useful for testing and demos outside of the CMS-CI process.

#### CMS in Build-Release-Deploy: Staging and Production

The VFS Team maintains a system called **BRD**: _Build, Release, Deploy._

The system is powered by Ansible Roles in the VA's "DevOps" repository, located at [github.com/department-of-veterans-affairs/devops/tree/master/ansible/build/roles/cms](https://github.com/department-of-veterans-affairs/devops/tree/master/ansible/build/roles/cms)

The **BRD** system builds Amazon server images using Ansible, and tags those images for release along with the source code of CMS.

The VFS team then deploys those images to the _staging_ and _production_ environments inside the VAEC when the release is ready.

See [The BRD System: Build, Release, Deploy](READMES/brd.md) for more information.
