### CMS Collaboration Cycle QA Discovery

Currently, the Platform Collaboration Cycle incorporates QA during the Staging Review.  Currently, five QA artifacts are expected during the Staging Review, four of which are required.  QA artifacts currently include the following:

1. **Regression Test Plan - Required**

   - The product must have a regression test plan that proves the new changes don't break previously-integrated functionality.

2. **Test Plan - Required**

   - The product must have a test plan that describes the method(s) that will be used to verify product changes.

3. **Traceability Reports - Warning**

   - The product must have a Coverage for References report that demonstrates user stories have been verified by test cases in the test plan. The product must also have a Summary (Defects) report that demonstrates that the defects discovered during QA testing were discovered by executing test cases in the test plan.

4. **E2E Test Participation - Required**

   - The product must have 1 or more End to End (E2E) tests.

5. **Unit Test Coverage - Required**

   - The overall product must have 80% or higher unit test coverage in each category: Lines, Functions, Statements, and Branches.

More details on how these artifacts are currently evaluated during the Staging Review can be found on the Platform’s [QA Standards](https://depo-platform-documentation.scrollhelp.site/developer-docs/quality-assurance-standards#QAstandards-VA.govQAStandards) page.

**Proposed additions for CMS:**
While I believe the current QA artifacts expected at the Staging Review provide a strong baseline to work from, I do believe we can enhance E2E Test Participation.  Requiring a single E2E test for a feature doesn’t necessarily provide adequate, repeatable, test coverage to help protect from future regressions. I’d like to propose that we add further clarification to the Test Plan and E2E Test Participation artifacts as described below:

1. **Test Plan** - test plans often contain many test scenarios covering less frequently encountered edge cases in addition to the core functionality the feature is trying to provide.  I request that product team(s) denote within the test plan what is considered core functionality to the feature as “Critical Path”.  

   - **Example**: A new feature for editing content may have some edge cases that, if not working correctly, create a minor bug, or inconvenience to end users.  This would not be considered “critical path”.  However, incorrect system behavior that prevents user(s) from being able to edit content as intended, would be considered “critical path”.

2. **E2E Test Participation** - As as first step, I suggest we require participating teams to indicate which of the “Critical Path” tests in the test plan are automated with E2E tests.  They should also calculate the percentage of “Critical Path” tests covered by automated E2E tests.

**Benefits:**
By requiring teams to explicitly indicate which tests within their test plans are covering core, or “critical”, functionality, and whether they are automated with E2E tests, we are bringing awareness to the highest value tests within test plans.  Calculating the percentage of “Critical Path” coverage provides a metric to identify gaps in automated test coverage, better highlighting opportunities to better guard against future regressions, and protect end users from potentially negative user experiences.  Lastly, incorporating better automated test coverage provides developers with more confidence in their ability to make changes to CMS functionality with fewer unknown negative implications.