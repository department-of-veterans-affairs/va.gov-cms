# Content API Consumers

## Background

This document lists known services which depend on the CMS and the Drupal users required to consume content via Drupal's API. For a list of all of VA.gov-CMS's down stream dependencies look [here](https://github.com/department-of-veterans-affairs/va.gov-cms/edit/main/READMES/downstream_dependencies.md).

## Users

| Team      | POC | Username | Usage |
| ----------- | ----------- | ----------- | ----------- |
| Release Tools      | [#vsp-tools-fe](https://dsva.slack.com/archives/CQH357ZTP) | content_build_api       | Building content for VA.gov requires querying Drupal for that content in an authenticated way.       |
| Forms API | [#va-forms](https://dsva.slack.com/archives/CUB5X5MGF)| forms_api        | Forms migration daily tasks must be authenticated       |
| Facilities   | [#vsa-facilities](https://dsva.slack.com/archives/C0FQSS30V) | facility_api        | ?       |
| Virtual Agent   | [#va-virtual-agent-public](https://dsva.slack.com/archives/C01KTS3F493) | virtual_agent_api| ?       |
| CMS   | [#cms-platform](https://dsva.slack.com/archives/C02HX4AQZ33)| datadog_api        | Datadog Synthetic metrics monitor GraphQL endpoint and require HTTP basic authentication       |
