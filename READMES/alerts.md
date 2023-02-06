# CMS Alerts

## Overview

CMS Alerts are managed by [Sentry](https://sentry.vfs.va.gov/) and [DataDog](https://vagov.ddog-gov.com/).  

Runtime issues are reported to Sentry via the Raven module.

Various metrics in the CI/CD phases and at runtime are reported to DataDog.

DataDog also includes some monitors that probe correct functionality of various
responsibilities, e.g. Tugboat base previews completing successfully, CMS login
pages being accessible, etc.

When these checks fail in some way, DataDog will generally respond in one of a
few different ways:

- notify Slack directly for the awareness of team members and stakeholders

- notify PagerDuty for issues that should be remediated by the DevOps team.

## Alert Levels

| Alert Level / Target  | Action                                            |
|-----------------------|---------------------------------------------------|
| Urgent                | PagerDuty Critical >> Slack `#cms-notifications`  |
| High                  | `@here` in Slack                                  |
| Medium                | Slack `#cms-notifications` `@cms-alerts-medium`   |
| Low                   | None                                              |
| QA                    | Slack `#cms-notifications` `@cms-qa-engineers`    |

## Drupal Log Level to Alert Level

| Drupal Level               | Alert Level / Target  |
|----------------------------|-----------------------|
| Emergency, Alert, Critical | Urgent                |
| Error                      | High                  |
| Warning, Notice            | None                  |
| Informational, Debug       | None                  |

## Alerts

| Source | Alert Path | Support Severity Level |
|--------|------------|------------------------|
| [GraphQL API](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md) | DataDog to PagerDuty | High |
| JSD Widget | Datadog to PagerDuty | Medium |
| DNS | DataDog to PagerDuty | Urgent |
| Drupal Emergency/Critical/Alert/Error | Drupal to Sentry to Slack | QA |
| Drupal Warning/Info/Notice/Debug | None | None |
| [TeamSite Facility Status](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md) | Datadog to Slack | Medium |
| Prod CMS Down | Datadog to Slack, PagerDuty | Urgent |
| [Drupal Post Content Webhook](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md) | GovDelivery, Drupal `post_api` to Slack | High |
| Drupal Flag List (`/flags_list`) | Datadog to Slack | High |
| Content Build Fails | Github Actions to Datadog to PagerDuty; also reports into `#status-content-build` in Slack | High |
| [Forms API](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md) | Datadog to PagerDuty | Medium |
| [Health Service Descriptions](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md) | Drupal `post_api` to Slack | Medium |
| Tugboat Base Preview Accessible | DataDog to PagerDuty, Slack | Medium |
| Tugoboat Server Resource | DataDog to PagerDuty, Slack | High                   |
| [GovDeliveryAPI](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/upstream-dependencies.md) | Trigger Drupal Error | High |
| Periodic Job failure in Jenkins | Jenkins to Slack | Medium |
| Daily Job in Jenkins | Jenkins to Slack | Medium |
| Prod Deploy Warn | Jenkins to Slack | Medium |
| Prod Deploy Start | Jenkins to Slack | Medium |
| Prod Deploy Failure | Jenkins to Slack | High |
| Prod Deploy Success | Jenkins to Slack | Medium |
| Non-Prod Deploy Failures | Jenkins to Slack | Medium |
| Non-Prod CMS Down | Datadog to PagerDuty (non-critical) | Medium |
| Staging Test Failures | Jenkins to Slack | High |

----

[Table of Contents](../README.md)
