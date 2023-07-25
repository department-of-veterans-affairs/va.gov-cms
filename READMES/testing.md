# Testing

The CMS codebase is tested several times in the development lifecycle:

- Prior to commit, changed files only (for performance reasons) are
  individually statically analyzed and linted.
- Commits that are part of a pull request are tested by two suites of tests:
  - linting, static analysis, and unit tests in GitHub Actions
  - functional/behavioral tests in Tugboat previews
- The functional/behavioral test suite is executed once more on Staging before
  being added to the final release.

## Pre-Commit Hooks

The pre-commit tests will vary by environment:

- on GitHub, no checks are performed.  This is fine because most changes made
  via GitHub are documentation updates, Dependabot updates to package
  dependencies, reverted PRs, etc.  And the full suite of tests will be run
  anyway.
- on other environments without `ddev` installed or running, no checks are
  performed.  This is again more-or-less fine -- if someone's not running
  `ddev`, they might have a good reason for that.  If they're working on a PR,
  the full suite of tests will be run anyway.
- on local environments with `ddev` installed and running, we run a set of
  lints and static analysis tests.  These can be found in [`precommit.sh`](../scripts/precommit.sh).

## Linting, Static Analysis, and Unit Tests

The "fast" suite of tests are composed of linting, static analysis, and unit
tests.  These are collocated because they are very fast and can be run both
independently and simultaneously.

At present, these include:

- **PHPStan**, a PHP static analyzer focusing on maintainability and verifiable
  behavior.
- **PHP_CodeSniffer**, a PHP static analyzer focusing on code quality and
  consistency with a style guide.
- **PHP**'s built-in linter, which is fast and may catch other issues.
- **Check CER Fields**, a script which checks for the presence of Corresponding
  Entity Reference fields.
- **Check Revision Logs**, a script which checks for the presence of revision
  log fields in node forms; they tend to disappear unexpectedly.
- **ESLint**, an ECMAScript/JavaScript linter and static analysis tool.
- **PHPUnit**, a PHP testing framework that runs not only unit tests but also
  functional/behavioral tests (see [below](#functional-and-behavioral-tests)).
- **StyleLint**, a CSS/SCSS linter run on custom modules and themes.

Further details and implementation or usage notes about some of these tools may
be provided below under [Testing Tools](#testing-tools).

## Functional and Behavioral Tests

The "slow" suite of tests are functional and behavioral tests.  These mostly
depend on a full, running installation of Drupal, and furthermore rely on
details of our content model, infrastructure, implementation details, and so
forth.

At present, these include:

- **Content-Build: GraphQL** or `content-build-gql`, a script that performs the
  initial retrieval of content-build data from Drupal via GraphQL, in order to
  verify compatibility.
- **Cypress**, a behavioral test framework that verifies correct behavior by
  puppeteering a headless Chromium browser.  This is the preferred location for
  new behavioral tests and is extensible with JavaScript rather than PHP.
  To run a single test feature use:
  `ddev composer va:test:cypress -- --spec=tests/cypress/integration/<feature file name>`
- **PHPUnit**, in a separate suite from the PHPUnit tests mentioned above, runs
  functional, security, and other tests.  This is an appropriate place for some
  tests, especially those that operate at the request level, API level, etc.
- **Status-Error** is a Drush command that checks Drupal's status for issues
  like infrastructure availability, cron not running, database inconsistency,
  etc.

Further details and implementation or usage notes about some of these tools may
be provided below under [Testing Tools](#testing-tools).

## GitHub Integration

GitHub is used as the ultimate store of truth for what commits are safe to
bundle into a release and deploy to production.

### Branch Protection Rules

All of the tests in the [Branch Protection Rules](https://github.com/department-of-veterans-affairs/va.gov-cms/settings/branches)
are required to pass before the changes in a PR can be merged into `main`.

![GitHub comment with the output from a failed test.](images/github-test-fail-comment.png)

### GitHub Statuses API

The API used by our test suites and GitHub for reporting test information is
called the [Statuses API](https://developer.github.com/v3/repos/statuses/). It
stores test results attached to the commit, based on SHA.

If an individual test fails, [GitHub Status Updater](#github-status-updater)
tool marks that specific status as failing.  Additionally, optionally,
[GitHub Commenter](#github-commenter) may add a comment containing the failure
log or other relevant information in the PR thread.

What you end up seeing is something like this:

![GitHub Commit Statuses, some failing, some passing.](images/github-commit-status.png)

_NOTE: The GitHub API stores this information attached to the Commit, not to
the PR. This means if you open a second PR with the same commits, the commit
status AND the commit comments will show in both pull requests._

## Testing Tools

### PHP_CodeSniffer

PHP_CodeSniffer tokenizes PHP files and compares them with a coding standard.
It is thus mostly geared at maintaining consistency and an attractive and
modern code style.  It has some overlapping functionality with PHPStan, but
they complement one another too.

### PHPStan

PHPStan performs static analysis on the codebase and reports issues such as
references to unknown/undeclared properties, incorrect argument types in
function calls, functions that are too long or too complex, etc.

#### Magic Properties and Other Annoyances

Developing with Drupal idiomatically tends to conflict with PHPStan.

For instance, you might type code like `$node->field_address->zip_code`. If
`$node` is declared as or implied to be a `\Drupal\node\Entity\Node` object,
then PHPStan will look for a property named `$field_address` on
`\Drupal\node\Entity\Node`.  But `$node` might also be interpreted as
`\Drupal\node\NodeInterface`, or `\Drupal\Core\Entity\EntityInterface`, or any
of several other interfaces.  But functionality for accessing fields via
constructs like `$node->field_address` is implemented via "magic properties,"
to which PHPStan does not and cannot have access. As a consequence, PHPStan
will view the use of these magic properties as errors.

To permit both idiomatic Drupaling and good static analysis, we allowlist
errors that arise from this sort of use.

This can be done by adding new expressions to the `parameters.ignoreErrors`
array in [phpstan.neon](../phpstan.neon).

```yaml
parameters:
  ...
  ignoreErrors:
    - '#Access to an undefined property Drupal\\node\\NodeInterface::\$field_address\.#'
```

This is hardly ideal, but we are optimistic that [entity bundle classes](https://www.drupal.org/node/3191609)
will permit us to remove this sort of hack.

#### Baseline

It sometimes happens that a developer will duplicate or repeat some code within
our codebase and then find, much to their surprise, that PHPStan throws an
error for the new code while seeming to ignore the old code.  This has historic
reasons.

PHPStan was integrated into the codebase after a substantial amount of
development had already occurred.  Ordinarily, a PHPStan error would prevent
code from being approved and merged.  But running PHPStan initially revealed a
couple of hundred issues, almost all having to do with magic properties and
other Drupal idioms.  Rather than break the build for days or weeks to
eliminate these issues, we opted instead to generate a _baseline_ and fail only
builds that introduced new code issues.

A PHPStan baseline is simply a list of existing errors.  We maintain the
baseline in our codebase (see [phpstan-baseline.neon](../phpstan-baseline.neon)
to prevent these historical errors from interfering with our CI/CD processes.

This does have drawbacks, though; it can be confusing to have the same code in
two places and see one instance trigger a PHPStan error and the other seem to
slip through.  (And depending on where the addition is made, the error message
may be misleading and point to old code and not the new code!)  And, if the
issue is corrected (or the code removed) in the future, the baseline must be
altered to match the new error set, so removing technical debt is slightly
penalized by a maintenance burden.

But, all things considered, this seems to be the least painful way of managing
static analysis.

## Other Tools

### GitHub Commenter

The functional/behavioral tests may in some cases need to post a relevant
comment in a GitHub pull request thread.

To accomplish this, we use [GitHub Commenter](https://github.com/cloudposse/github-commenter),
and script some scaffolding around the primary test execution to derive
the comment content and handle cleaning and submitting it to GitHub.

GitHub Commenter is installed by the [`install_github_commenter.sh`](../scripts/install_github_commenter.sh)
script.

### GitHub Status Updater

The functional/behavioral tests need to inform GitHub of their success or
failure.

We do this with [Github Status Updater](https://github.com/cloudposse/github-status-updater) and
a boilerplate script in [`ci-wrapper.sh`](tests/scripts/ci-wrapper.sh).

The CI wrapper script simply executes the command; if it exits with a 0 exit
code, we report success.  If it returns a nonzero exit code, we report failure.
Any additional functionality is left up to the command being executed.

GitHub Status Updater is installed by the [`install_github_status_updater.sh`](../scripts/install_github_status_updater.sh)
script.

### Task (or Go-Task)

The functional/behavioral test suite tasks are defined in [`tests.yml`](../tests.yml).

These tests are executed using an application known as Task (or Go-Task, Go
being the programming language in which it is implemented).  Task allows us
to run some tests in parallel, collect their output, and report it through
either the Tugboat log or Jenkins, as appropriate for the environment.

Task is installed by the [`install_task.sh`](./scripts/install_task.sh) script.

## How Do I...?

### View the verbose output of the test runs on a PR?

Follow one of two approaches, depending on the type of the test.

#### If the test name begins with "va/tests/...", e.g. "va/tests/cypress"

This test is run on Tugboat.

Find the PR that contains the links to the Tugboat environment:

![Tugboat PR Comment](https://user-images.githubusercontent.com/1318579/186016897-9c2f26fb-c395-465e-9eb2-6a77363db4cf.png)

Click the link under **Dashboard** (SOCKS must be enabled to access Tugboat).

Once in the Tugboat instance dashboard, scroll down to the Preview Build Log
and click "See Full Log".

![Preview Build Log](https://user-images.githubusercontent.com/1318579/186017075-fac60359-3a9f-4ce6-9c82-23a1e4caca1a.png)

This will give you a scrollable view of all of the logged information output
since the Tugboat environment was (re)built, including all test output.

Unfortunately, and extremely frustratingly...

- it will autoscroll to the bottom until all tests have completed, and
- the text is not searchable.

If you need to find some particular string, select all and copy it to an
IDE/text editor/whatever.

Otherwise, just scroll back to find the failed test, and go from there.

### Develop or run Cypress tests locally (under DDEV)?

The preferred approach is to run interactively, e.g. `npm run test:cypress:interactive` (NOT through DDEV).  This will load appropriate environment variables and open the Chrome browser on your host machine.  If you want to confirm that the tests run headless, run `composer va:test:cypress -- --<path to spec file>`.

Don't run Cypress directly, e.g. via `./node_modules/.bin/cypress open`.  This will not load some necessary environment variables and consequently tests will not run correctly.

#### Otherwise...

This test is run by a GitHub Action.  Click the "Details" link in the row of
the failed test.  This should take you to a view of the run details, which
should contain the logged information.

----

[Table of Contents](../README.md)
