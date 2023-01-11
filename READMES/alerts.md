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

----

[Table of Contents](../README.md)
