# Project Conventions

## Naming

* Modules: `va_gov_<subject>`, e.g. `va_gov_content_release`, `va_gov_user`.
* Content Types: `<content type name>`, e.g. `page`, `campaign_landing_page`.
* Fields: `field_<field name>`, e.g. `field_administration`, `field_alert_dismissable`.
* Services: `<module name>.<service name>`, e.g. `va_gov_content_release.reporter`, `va_gov_benefits.entity_event_subscriber`.

## Tickets

* The title should be clear and descriptive. Keep in mind that this ticket may contain useful information for years, so make it easy to search for and find.
* The description should contain as much detail as possible. It is great to link to a Slack thread, but it is better to summarize the salient points of the thread, copy and paste comments, *et cetera*, to make the ticket the source of truth.
* Acceptance Criteria
  * Must be **SMART**:
    * **Specific**: List the specific conditions that must be met for the ticket to be considered complete.
    * **Measurable**: Criteria should be quantifiable to avoid ambiguity.
    * **Achievable**: Ensure that the criteria are realistic and attainable.
    * **Relevant**: Criteria should be directly related to the ticket's goal.
    * **Time-Bound**: If applicable, include deadlines or time constraints.
  * If you don't know the acceptance criteria, or cannot satisfy the above conditions, open a discovery ticket to investigate the problem so that you can draft a meaningful ticket that will be more successful at addressing it.
  * `Defect` and `Critical Defect` tickets often come in with no other Acceptance Criteria than something along the lines of "this problem is fixed." These should always be something like "A solution has been identified and implemented as part of this ticket or a followup ticket has been opened to implement the solution." This should allow for reasonable estimation while acknowledging the massive risk inherent in such a ticket.
* A User Story is very helpful for ensuring that we preserve a focus on improving the user experience.
* Prefer opening multiple small tickets to a single large ticket.
  * Smaller tickets are easier to write, easier to test, easier to debug, easier to review, easier to close, and easier to report.
  * The overhead is negligible.
* Use the `Defect` label to identify tickets for issues actively and meaningfully impacting the user experience.
* Use the `Critical Defect` label to identify tickets for issues impacting the user experience to such a degree that they need to be resolved within the current sprint.
  * This will generally also be `Unplanned Work`.
  * This may involve a [Postmortem](./postmortems.md), if only an informal, internal one, if there was a substantial disruption to user experience and there was a "teachable moment".
* Use the `Incident` label to identify tickets for issues impacting the user experience to such a degree that they need to be resolved *immediately*.
  * This will almost always also be `Unplanned Work`.
  * This will often involve a [Postmortem](./postmortems.md) unless the underlying issue was out of the team's control, e.g. an upstream failure causing a service disruption.
  * This will often be opened *after* the work is done, as engineers are too busy fighting a fire to draft an informed and well-written ticket.
* Ticket status should reflect the status of the work on `main`.
  * In other words, do not close a ticket for a defect that has been resolved in an integration branch. Mark it with a `Done` label.
* If work can be done on a PR targeting `main`, do it there, *not* on a PR targeting an integration branch.

## Development

* Prefer implementing event subscribers to hook implementations.
* Do not add new code to `va_gov_backend`.
* If significantly changing code in `va_gov_backend`, prefer relocating it to a new or existing module.
* Avoid mingling multiple concerns into a PR. Keep changes small and easily testable and reversible.
* Practice clean coding principles:
  * Create new custom modules when appropriate.
  * Keep functions, class methods, and classes small, concise, single-purpose, and reusable.
  * Write unit tests for functions and methods.
  * Use meaningful variable and function names.
  * Provide clear comments and documentation in code.
  * Prefer self-documenting code to comments about implementation details (which may drift out of sync).
  * Use dependency injection wherever possible; do not instantiate services through `\Drupal::<whatever>` or `\Drupal::service(<whatever>)` calls.
  * Prefer modern object-oriented alternatives like event subscribers and other object-oriented APIs to legacy hook implementations.
  * Refactor when appropriate. You are empowered to improve the design of existing code.

### Theming

* Prefer [BEM (Block, Element, Modifier)](https://getbem.com/introduction/) syntax when possible.
* Prefer CSS custom properties instead of Sass variables.

### Contrib Modules

* If your work depends on a contrib module behaving in a specific way, add automated tests to assure consistency across updates moving forward.
* If functionality for which your team is responsible breaks as a result of a bug added by an update to a contrib module, add automated tests to guard against regressions.

## Automated Testing

* Prefer unit testing for basic functionality of classes, methods, and functions.
* Prefer "existing site base" tests for complex functionality interacting with or dependent upon hooks and/or Drupal Core functionality.
* Prefer Cypress tests for complex UI functionality, forms, etc.

## Pull Requests

* PRs should normally have a single associated ticket.
  * If there is no ticket for the work you are doing:
    * Consider whether you should really be doing it.
    * Consider whether this work is substantial enough that it should qualify as `Unplanned Work`.
    * Clear it with the tech lead of your team, and escalate to the PM, DM, and PO.
    * Open a ticket.
  * If you are opening a PR that will close multiple tickets:
    * Consider whether this is necessary or advisable.
    * Consider what repercussions this may have if an issue forces a revert of the entire PR.
* Keep PRs in Draft until they are ready to be reviewed and merged.
  * Do not rely on labels, title prefixes, etc. Only Draft mode mechanically prevents a premature merge.
* Write QA Steps that demonstrate that the PR addresses the Acceptance Criteria of the related ticket.
* If these QA Steps are automatable (and most should be), then consider adding a Cypress test.

## Code Review

* As a reviewer, use [conventional comments](https://conventionalcomments.org/) to signal your intent, e.g. **Nitpick**, **Suggestion (non-binding)**, **Request**, etc.
* Do not make unsolicited comments on PRs that do not belong to a member of your team unless you feel it poses a clear and present danger.
  * If you have a suggestion for an alternative approach, or a concern about the ticket or the approach, escalate it to your PM/DM/PO.
* Do not review PRs that do not belong to a member of your team unless your review has been requested.
  * If your review has been requested as a result of code ownership, but the code in question is owned by multiple teams, consider whether your review is necessary or valuable in this context.
  * For example, `config/sync` is massive, messy, and ownership is shared between multiple teams. It is good to be aware of changes to configuration, but it is not necessarily appropriate for you to review those changes formally.
* Do not make changes to PRs that do not belong to a member of your team without their consent and approval.
  * Instead, make suggestions, justify them, and allow the owner to approve or reject them.
* Do not merge new changes into PRs that do not belong to a member of your team (excluding Dependabot, etc) without their consent and approval.
* Do not merge a PR that does not belong to a member of your team without their consent and approval.
* Do not review PRs that are in "Draft" status unless you have been specifically requested.

----

[Table of Contents](../README.md)
