# Code Review

## Guiding principles

* Main/Master branch must always deployable
* Tests must pass 
* The cycle time from Pull Request open to Pull Request merged should be minimized
* PR Owners must be empowered and trusted to merge code
* PR Reviewer must Approve Pull Request
* Automated Test Suite must be trustworthy and inspire confidence

## Personas

Let's start the discussion by talking about different personas and focus on their responsibilities.

* Pull Request (PR) Owner
* Pull Request (PR) Reviewer/Tester
* Tech Lead
* Product Owner
* Product Manager

While the PR Owner has ownership of the changes introduced in the Pull Request, both the PR Owner and PR Reviewer take shared responsibility for the quality of the changes.

### PR Owner

A PR Owner is either the author of the code, the creator of the Pull Request, or another designated person. There should be only a single PR Owner per Pull Request. A PR Owner can be anyone with a Github account.

The PR Owner will be the person `assigned` to the pull request.

*Code Owner Responsibilities:*

* All changes introduced within the Pull Request
* Responding to questions and requested changes to the Pull Request
* Passing tests
* Adding new tests
* Adding documentation
* Merging the Pull Request when it's approved, tests are passing and the PR meets a Definition of Done.
* Validating the changes in the Pull Request fulfill the needs of the Product Owner and ACs in related issue
* Communicating any status about the deploy process, extra downtime, etc to the appropriate team members and Product Owner.
* Provide notes on how to test and any necessary screenshots of changes.
* Request a review from a Tech Lead if necessary.
* Request a review from a Product Owner on CI if necessary.

### PR Reviewer

A PR Reviewer is one or more person(s) responsible for reviewing all the changes a Pull Request introduces. There can be multiple PR Reviewers per PR. A PR Reviewer can be anyone that is part of the department-of-veterans-affairs/va.gov-cms group. If a change introduced by a Pull Request introduces an architectural change or a significant new feature, a Tech Lead and a Product Manager (as needed) should be the PR Reviewer.

* A PR Reviewer will self assign or be assigned by the PR Owner as a Reviewer on the Pull Request.
* PR Reviewer Responsibilities
* Test Code changes on CI
* Document what was tested providing screenshots if necessary.
* Follow up with questions/feedback for the PR Code Owner in a timely manner
* Approve Pull Request or provide detailed feedback on why the Pull Request was not approved.
* Request a review from a Tech Lead if necessary.
* Request a review from a Product Manager if necessary.
* Request a review from a Product Owner on CI if necessary. Note: issues that require PO review will be tagged using `Needs PO review` label. PRs addressing issues that need PO review should also be labelled.

### Tech Lead

The Tech Lead is accountable for the code quality and architecture practices of the CMS application. In respect to the code review process, the tech lead must be added as a PR Reviewer if a change introduced by a Pull Request contains an architectural change or a significant new feature.

### Product Owner

The Product Owner is a person who makes and prioritizes feature requests. This person is normally a part of VA/DEPO organization. The Product Owner is not directly involved in the PR Review process. The Product Manager decides whether the Product Owner should be brought in to review changes on the CI environment.

### Product Manager

The Product Manager is a person on the CMS team who works with the Product Owner on defining feature requests and priorities. The Product Manager can participate in the PR Review process and be brought in to review changes on the CI environment as specified in User Story Acceptance Criteria. The Code Owner will keep the Product Manager up to date on status.

## PR Review process

1. A Pull Request is created in Github as a `Draft`.
1. The PR Owner is set as the Assignee of the Pull Request.
1. Add `Core Application Team` or `Product Team Support` label.
1. If a PR is blocked by another PR add the `DO NOT MERGE` label and add `[DO NOT MERGE]` or `[WIP]` to the PR title.
1. The PR Owner will test the changes in CI. When the PR Owner is confident the changes are ready for review, the PR Owner will set the Pull Request to `Ready for Review`.
1. PR Owner will request one or multiple PR Reviewers for the Pull Request as needed.
1. If a change introduced by a Pull Request introduces an architectural change or a significant new feature, the Tech Lead will take the role of the PR Reviewer on the Pull Request.
1. PR Reviewer will look at the code and make comments/suggestions.
1. PR Reviewer will test the code in the CI environment to make sure the request features are implemented and no other regressions exist.
1. PR Reviewer will provide feedback on the Pull Request using the Review Changes feature. Good feedback practice is to leave a single review and not several individual comments on the Pull Request.
1. The PR Owner will provide changes requested by the Code Review and answer all questions.
1. When PR Reviewer has identified that the changes introduced by the Pull Request are of high quality, address the related issue and will not introduce new regressions, the Pull Request is approved.
1. The PR Owner merges the Pull Request.

## Automated Code Quality Review Tools

The following automated code tools must pass before a Pull Request can be merged.

* eslint
* PHPCS
* PHPMD
* PHPStan
* stylelint

## Definition of Done

* Automated Tests Pass
* Code Quality Tools pass
* Manual Code Review Approved
* Acceptance Criteria in related issue are met


[Table of Contents](../README.md)
