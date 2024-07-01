# Quality Assurance

This document will act as an in-repo collection of links while we attempt to bootstrap a new Quality Assurance initiative.

All of this is very much in flux at present.

- CMS-QA Confluence Space
  - [Strategy](https://vfs.atlassian.net/wiki/spaces/~821090906/pages/2304933915/Quality+Assurance+and+Testing+Strategy) -- an outline of the strategy we use to evaluate issues impacting QA, and how we address them in practice.
  - [Current Challenges](https://va-gov.atlassian.net/wiki/spaces/CMSQA/pages/1812987905/Current+Challenges+Facing+the+Project) -- a survey of some of the more noteworthy QA challenges identified at this time, and how we're attempting to assess and deal with them.

[Table of Contents](../README.md)

### Ideal State of Testing - As of 6/10/2024

**Unit Testing:**
Unit testing in the va.gov-cms repository will be performed using PHPUnit.  Ideally, this will be a collaborative effort performed by Drupal Engineers and Test Engineers.  Our goal will be to meet the Platform standard of at least 80% code coverage in each of the following categories: Lines, Functions, Statements, and Branches.

**API Testing:**
Necessity of API test coverage is TBD at this point in time.  If there are custom endpoints made available to external consumers of the CMS, coverage should be added for those.  Another potential candidate would be testing GraphQL queries to retrieve data from the CMS.

**E2E Testing:**
End-to-end testing in the va.gov-cms repository will continue to be performed using Cypress.  Ideally, this will be a collaborative effort performed by Drupal Engineers and Test Engineers.  This testing should be focused on critical functions replicating common workflows for admins and editorial staff using the CMS.

E2E testing should be divided into specific test runs and run on varying schedules in order to help improve the speed at which developers receive feedback on changes, as well as improve the performance of our release pipeline.  Proposed test runs and schedules are outlined below:

1. **Critical Path: **

   - A collection of tests from the overall regression test suite should be categorized as “critical path”.  These should cover functions within the CMS that are considered “essential” for admins and editorial users to be able to perform their duties.  The “critical path” suite should be limited to a conservative number of tests to ensure the suite can run quickly and frequently within daily development workflows.

   - The “critical path” suite of tests should be run in all PR environments as well as the daily CI/CD pipeline (BRD).

2. **Regression:**

   - A collection of tests that ensures the application still behaves as expected after changes have been introduced.  This is essentially the full suite of automated end-to-end tests, so long as the functionality is still relevant to CMS users.

   - The full “regression” test suite should be run nightly so that each day’s changes are still fully tested.  Running on a nightly schedule can help provide frequent feedback to daily changes, hopefully without obstructing developers workflows or slowing the daily CI/CD pipeline (BRD).

Initially, I would suggest reporting the results of the critical path run in the daily CI/CD pipeline as well as the nightly regression run into the Platform’s instance of TestRail.  This will help provide a historical record of test performance to help identify when breaking changes are introduced.

**Smoke Testing:**
A small number or single test script proving the CMS is accessible and functioning should be performed after production releases.  If limited by login, this may be able to be replaced by synthetic monitoring scripts that prove the CMS Login page is accessible.

**Manual/Exploratory Testing:**
1. Manual: Any functionality in the CMS that is not easily automated, login for instance, should be tested manually on a regular basis.  Frequency of this testing can vary depending on the criticality of the functionality in question.

2. Exploratory: Exploratory testing sessions with a given scope could be performed as needed with large feature releases.  This is a good way to examine user experience and integration points between features that were developed and tested in isolation.

**Synthetic Monitoring:**
Synthetic monitors in Datadog should be utilized to help ensure “up-time” of the CMS application in our non-production and production environments.  This can be as simple as the existing monitors ensuring the Login page is accessible in both Staging and Production.

Further discovery can be performed on whether synthetic monitoring can provide more detailed live coverage of the CMS application across environments.