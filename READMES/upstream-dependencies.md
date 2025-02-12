# Upstream Dependencies

## Background

This document lists known external dependencies for the CMS.
Official points of contact for each service are listed.
Other points of contact for some services are listed on [DSVA Slack](https://dsva.slack.com/archives/CT4GZBM8F/p1628284192216100).

## Runtime
| Service| Content| Mode| Monitoring| Escalation Contact| Notes|
|------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [GovDelivery](https://granicus.com/solution/govdelivery/)| Situation updates & alerts are sent to subscribed users via GovDelivery| Uses [govdelivery_bulletins](https://github.com/department-of-veterans-affairs/va.gov-cms/tree/main/docroot/modules/custom/va_gov_govdelivery) module to post data to the GovDelivery API endpoint| [See Below](#govdelivery)| [https://support.granicus.com/s/contactsupport](https://support.granicus.com/s/contactsupport)|||
| Lighthouse Facilities API | VAMC, Vet Center, Regional Office, Cemetery, and health services data [See Below](#facility-cemetery-and-health-services-via-lighthouse-api)| [Nightly Facility Migration](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/READMES/migrations-facility.md); [See Below](#lighthouse-facilities-api)| https://valighthouse.statuspage.io | [#cms-lighthouse](https://app.slack.com/client/T03FECE8V/C02BTJTDFTN) slack channel - @facilities-team <br/> Adam Stinton (LH engineer)<br/> VA PO = Michelle Middaugh |  API paths are [overridden](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/docroot/modules/custom/va_gov_migrate/config/install/migrate_plus.migration.va_node_facility_nca.yml#L22) by [settings.php](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/docroot/sites/default/settings.php#L146) |
| Lighthouse Forms API | Migration imports from VA Forms DB & creates/updates “VA Form” nodes and forms metadata (Form name, dates, PDF filename, status, administration) | [Nightly Forms Migration](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/READMES/migrations-forms.md)| https://valighthouse.statuspage.io | #va-forms slack channel <br/> @public-websites-team | |
| Github Action (manual & automatically triggered content releases)| Editors can trigger a content release either manually or by editing certain content types| [Calls jenkins webhook](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/READMES/cms-content-release.md#automatic)| [http://jenkins.vfs.va.gov/computer/](http://jenkins.vfs.va.gov/computer/)| Ops team (use #vfs-platform-support)||
| Slack (notifications)| Post API failure alerts, Teamsite facility status failure alerts| Drupal calls Slack webhook| [https://status.slack.com/](https://status.slack.com/)|||
| [SSOi](https://dvagov.sharepoint.com/sites/OITEPMOIAM/playbooks/Pages/IAM%20URLs.aspx) (must be on VA network to access doc) | CMS users are authenticated with the VA Single Sign On service (SSOi)| See [README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/READMES/cms-login.md#technical-details| https://iamportal.iam.va.gov/iamv2/index.php (must be on VA network)                   | https://iamportal.iam.va.gov/iamv2/help/contactUs.php (must be on VA network)||
| [Unbound DNS](https://vfs.atlassian.net/wiki/spaces/OT/pages/1474594384/Unbound) | Forwards (and caches) DNS requests from TIC to AWS Route 53 DNS servers | Facilitates access to internal services from within network, e.g. CAG access to CMS | [Datadog](https://vagov.ddog-gov.com/synthetics/details/qbs-9w2-hd8?live=1h) synthetic | Ops team (use #vfs-platform-support) | |
### GovDelivery

**Monitoring**

* Alerting
   * Slack notifications via Sentry from Drupal errors.
* Error logs
   * `/admin/reports/dblog?type%5B%5D=govdelivery_bulletins` (ephemeral)
   * https://sentry.vfs.va.gov/organizations/vsp/issues/?query=logger%3Agovdelivery_bulletins
* Internal
   * (pending) https://github.com/department-of-veterans-affairs/va.gov-cms/issues/6189
* External
   * [https://status.granicus.com/](https://status.granicus.com/)

The following services can affect the CMS's functionality or data at any time.

### Lighthouse Facilities API 

#### Content

* [Facility migration README](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/READMES/migrations-facility.md)
* VHA: VAMC & Vet Center Facility metadata and statuses
* NCA: Cemetery metdata and statuses
* VBA: Regional Office metadata and statuses
* Facility services for VAMC, Vet Centers, and Regional Offices 

#### Mode

* Nightly migration pulls data from the Lighthouse API from the following [periodic tasks](/tasks-periodic.yml):
   * `va/background/daily/migrate/nca_facility`
   * `va/background/daily/migrate/vba_facility`
   * `va/background/daily/migrate/vet_centers_facility`
   * `va/background/daily/migrate/health_care_local_facility`

* Migration configs:
   * [migrate_plus.migration.va_node_facility_nca.yml](/config/sync/migrate_plus.migration.va_node_facility_nca.yml)
   * [migrate_plus.migration.va_node_facility_vba.yml](/config/sync/migrate_plus.migration.va_node_facility_vba.yml)
   * [migrate_plus.migration.va_node_facility_vet_centers.yml](/config/sync/migrate_plus.migration.va_node_facility_vet_centers.yml)
   * [migrate_plus.migration.va_node_facility_vet_centers_mvc.yml](/config/sync/migrate_plus.migration.va_node_facility_vet_centers_mvc.yml)
   * [migrate_plus.migration.va_node_facility_vet_centers_os.yml](/config/sync/migrate_plus.migration.va_node_facility_vet_centers_os.yml)
   * [migrate_plus.migration.va_node_health_care_local_facility.yml](/config/sync/migrate_plus.migration.va_node_health_care_local_facility.yml)


## Build time

The following services can affect the deployment process' ability to fully build the CMS for testing or production deployment.

| Service                                 | Url                 | Status                                                         | Escalation                                                                                                                                                                                     | Notes                                                                                                                                                                                                 |
|-----------------------------------------|---------------------|----------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [Remi Repo](https://rpms.remirepo.net/) | rpms.remirepo.net   | none                                                           | Tweet [@RemiRepository](https://twitter.com/RemiRepository) and open issue at [https://forum.remirepo.net/](https://forum.remirepo.net/)                                                       | Remi Repo is used to pull in the PHP 7.3 libraries and dependencies in our AMI builds. This won't be used when we switch from Amazon Linux 1 to Amazon Linux 2 when we move to containers on ArgoKube |
| Drupal packages                         | packages.drupal.org | [@drupal_infra on Twitter](https://twitter.com/drupal_infra)   |                                                                                                                                                                                                | Drupal packages is used to download Drupal contrib modules                                                                                                                                            |
| [Packagist](https://packagist.org)      |                     | [https://status.packagist.org/](https://status.packagist.org/) | Tweet at [@packagist](https://twitter.com/packagist). It is used by thousands of sites so highly likely that someone knows about any issues before we do and that it will be resolved quickly. | Packagist is used to install our PHP dependencies that are required by Drupal custom and contrib modules.                                                                                             |
| [Github](https://github.com)            |                     | [https://www.githubstatus.com/](https://www.githubstatus.com/) | Use the #github_information channel in DSVA slack                                                                                                                                              | The codebase is stored in github, and the deployment process depends on it to pull code and push status and code quality messages to our pull requests.                                               |
| [ZenHub](https://www.zenhub.com)        |                     | https://twitter.com/zenhubstatus                               |                                                                                                                                                                                                | ZenHub is a project management layer on top of GitHub Issues that we use.                                                                                                                             |
| [Docker Hub](https://hub.docker.com/)   |                     | [https://status.docker.com/](https://status.docker.com/)       | Contact support@docker.com and/or tweet [@Docker](https://twitter.com/Docker)                                                                                                                  | We use Docker Hub to pull down container images for our CI environments in Tugboat.                                                                                                                   |

## Composer
There are a number of services that Composer uses to download and install dependencies. These services are used during the build process and are not required for runtime.
