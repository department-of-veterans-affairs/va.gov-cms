# Content API Consumers

## Background

This document lists known services which depend on the CMS and the Drupal users required to consume content via Drupal's API. For a list of all of VA.gov-CMS's down stream dependencies look [here](https://github.com/department-of-veterans-affairs/va.gov-cms/edit/main/READMES/downstream_dependencies.md).

## Users

| Team      | POC | Username | Usage |
| ----------- | ----------- | ----------- | ----------- |
| Release Tools      | [#vsp-tools-fe](https://dsva.slack.com/archives/CQH357ZTP) | content_build_api       | Building content for VA.gov requires querying Drupal for that content in an authenticated way.       |
| Lighthouse Forms API | [#va-forms](https://dsva.slack.com/archives/CUB5X5MGF)| forms_api        | Forms migration daily tasks must be authenticated       |
| Facilities   | [#cms-lighthouse slack channel](https://app.slack.com/client/T03FECE8V/C02BTJTDFTN) - @facilities-team <br/> Adam Stinton (LH engineer)<br/> VA PO = Michelle Middaugh | facility_api        | Facilities API migration daily tasks must be authenticated. [Upstream notes](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/READMES/upstream-dependencies.md#lighthouse-facilities-api); [Downstream notes](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/READMES/downstream_dependencies.md)|
| Virtual Agent   | [#va-virtual-agent-public](https://dsva.slack.com/archives/C01KTS3F493) | virtual_agent_api| ?       |
| CMS   | [#cms-platform](https://dsva.slack.com/archives/C02HX4AQZ33)| datadog_api        | Datadog Synthetic metrics monitor GraphQL endpoint and require HTTP basic authentication       |

## Creating a New API User


1. Discuss the need for a new user with the Platform CMS tech lead (currently @ndouglas) and propose a name.
2. Complete/approve/merge any pull requests providing functionality the user account will rely upon:
    - roles, permissions, etc that should be assigned to the user at creation 
4. Create a task issue for the account, assign it to `CMS Team` and `DevOps`, and assign the tech lead.
    - include the name
    - include desired permissions
    - include any other information that may be desirable
    - any IAM users/roles/etc that may need access to the username and password
3. DevOps engineers will perform the following procedure:
    - create a new user in Drupal
    - assign it appropriate roles and permissions
    - assign it a temporary password
    - create an entry in SSM Parameter Store for:
      - the username (`cms/prod/drupal_api_users/<name>/username`)
      - the password (`cms/prod/drupal_api_users/<name>/password`)
  - communicate the name of the user to you.
4. Followup issue(s) should be opened for:
    - tests to ensure accurate roles and permissions
    - tests to ensure retrievability of the SSM Parameter Store values
    - additional functionality
