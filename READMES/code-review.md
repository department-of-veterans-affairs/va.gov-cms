# Code Review

One (or more) lead developers is assigned to take the responsibility of merging all pull requests. This ensures consistency in quality control as well as identifying any potential issues with related, open pull requests.  GIthub is configured to not allow merging a Pull Request until it has been reviewed and approved.

A small group of one or more person(s) is selected to be integrators. All commits are reviewed by this group. If work is done by an integrator, their work should be reviewed by a fellow integrator (As if they were a developer.  You can't review your own code.).

## Pull Request Review Process
1.  When a Pull Request is created.  A Unity environment is created automatically where tests are run on the CMS and Front End.
1.  If the tests pass, it will show as passed in GitHub.
1.  After the tests, pass, you can request that someone review the PR.
1.  PR is code reviewed by integrator team for code formatting, code quality, correctness, syntax. Any developer outside the integrator team is welcome to perform code reviews but should not merge.
1.  Reviewer reviews the code in GitHub.
1.  Reviewer reviews the work on the Unity test environment for completeness and correctness, following the manual QA test left by the developer in the ticket.

[Table of Contents](../README.md)
