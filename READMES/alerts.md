# CMS Alerts
## Overview
CMS Alerts are managed by Prometheus Alertmanager. Metrics that are scraped by Prometheus from CMS infrastructure are available to create alerts inside rules files. Alerts are categorized by two different severities: warn and page.
When an alert is configured to 'warn' it is routed to a Non-Critical receiver then appears in PagerDuty. When an alert is configured to 'page' it is routed to a Critical receiver then appears in PagerDuty, and a message in #cms-team Slack channel.

## Where Are Alerts Configured
Alerts for CMS are configured by `.rules` files stored under `devops/ansible/deployment/config/prometheus/rules/` in the below two files:

cms.rules
cms-utility.rules

How alerts are routed to which receivers (i.e Critical, Non-Critical) are configured by `alertmanager.yml.j2` found under `devops/ansible/deployment/config/prometheus/`

## Configured Alerts

| Name      | Purpose | Threshold | Severity | Prometheus Server |
| ----------- | ----------- | ----------- | ----------- | ----------- |
| CMSInstanceHighCPUCritical      | Reports high CPU usage on all servers tagged with `purpose:cms` | CPU > 70% for 3m | page | dev,staging,prod |
| SiteReachableCritical   | Curl command on CMS login page to check availability for Prod | Script returns no success for 2m | page | utility |
| SiteReachableNonCritical   | Curl command on CMS login page to check availability for dev and staging | cript returns no success for 5m | warn | utility |
| GqlTimeCritical   | Reports extremely long GraphQL query times for content builds | Query time >= 30m | page | utility |
| GqlTimeNonCritical   | Reports long GraphQL query times for content builds        | 30m > Query time >= 15m | warn | utility |

Alerts can be viewed on prometheus via `http://prometheus-[ENV].vfs.va.gov:9090/prometheus/alerts`