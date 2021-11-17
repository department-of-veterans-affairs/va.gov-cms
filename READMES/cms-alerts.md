## Alert Levels

| Alert Level  | Action                                   |
|--------|------------------------------------------------|
| Urgent | PagerDuty Critical >> Slack #cms-notifications |
| High   | `@here` in Slack                               |
| Medium | Slack #cms-notifications @cms-alerts-medium    |
| Low    | None                                           |

## Drupal Log Level to Alert Level


| Drupal Level               | Alert Level  |
|----------------------------|--------|
| Emergency, Alert, Critical | Urgent |
| Error                      | High   |
| Warning, Notice            | Medium |
| Informational, Debug       | Low    |

## Alerts
| Source                                                                                                                                     | Alert Path                                                                               | Support Severity Level |
|--------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------|------------------------|
| [GraphQL API](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md)                 | Uses a synthetic monitor in Datadog                                                      | High                   |
| JSD Widget                                                                                                                                 | Uses a synthetic monitor in Datadog to PagerDuty (non-critical)                          | Medium                 |
| DNS Status                                                                                                                                 | Uses a synthetic monitor in Datadog to PagerDuty (critical)                          | Urgent                 |
| Drupal Warning                                                                                                                             | Drupal to Sentry to Datadog to Slack                                                     | Medium                 |
| Drupal Error                                                                                                                               | Drupal to Sentry to Datadog to Slack                                                     | High                   |
| Drupal Critical                                                                                                                            | Drupal to Sentry To Datadog to PagerDuty                                                 | Urgent                 |
| Drupal Info                                                                                                                                | Not Sent to Sentry                                                                       | Low                    |
| Drupal Alert                                                                                                                               | Drupal to Sentry To Datadog to PagerDuty                                                 | Urgent                 |
| Drupal Notice                                                                                                                              | Drupal to Sentry To Datadog to Slack                                                     | Medium                 |
| Drupal Debug                                                                                                                               | Not Sent to Sentry                                                                       | Low                    |
| Drupal Emergency                                                                                                                           | Drupal to Sentry To Datadog to PagerDuty                                                 | Urgent                 |
| [TeamSite Facility Status](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md)    | Datadog to Slack                                                                         | Medium                 |
| Prod CMS Down                                                                                                                              | Datadog to PagerDuty                                                                  | Urgent                 |
| [Drupal Post Content Webhook](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md) | GovDelivery, Drupal `post_api` to Slack                                                  | High                   |
| Drupal Flag List (`/flags_list`)                                                                                                            | Datadog to Slack | High                   |
| Content Build Fails                                                                                                                        | Github Actions to `#vfs-platform-builds` in Slack                                               | High                   |
| [Forms API](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md)                   | Datadog to PagerDuty (non-critical)                                                      | Medium                 |
| [Health Service Descriptions](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/downstream_dependencies.md) | Drupal `post_api` to Slack                                                               | Medium                 |
| Tugboat Base Preview Accessible                                                                                                             | [Issue Logged](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/6307) | Medium                 |
| Tugoboat Server Resource                                                                                                                   | [Issue Logged](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/4562) | High                   |
| [GovDeliveryAPI](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/upstream-dependencies.md)                | Trigger Drupal Error                                                                     | High                   |
| Periodic Job failure in Jenkins                                                                                                                    | Jenkins to Slack                                                                         | Medium                 |
| Daily Job in Jenkins                                                                                                                       | Jenkins to Slack                                                                         | Medium                 |
| Prod Deploy Warn                                                                                                                        | Jenkins to Slack                                                                         | Medium                   |
| Prod Deploy Start                                                                                                                        | Jenkins to Slack                                                                         | Medium                   |
| Prod Deploy Failure                                                                                                                        | Jenkins to Slack                                                                         | High                   |
| Prod Deploy Success                                                                                                                        | Jenkins to Slack                                                                         | Medium                 |
| Non-Prod Deploy Failures                                                                                                                   | Jenkins to Slack                                                                         | Medium                 |
| Non-Prod CMS Down                                                                                                                          | Datadog to PagerDuty (non-critical)                                                      | Medium                 |
| Staging Test Failures                                                                                                                        | Jenkins to Slack                                                                         | High                   |


