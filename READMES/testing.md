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
tests.

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

## Goals

To adopt a strong test driven culture, the testing tools must:

1. Run the same tests in multiple environments with minimal configuration and
a single command.
2. Allow developers to define tests to include in the suite and to write the
tests.
3. Provide feedback to developers as quickly as possible and make test output
as readable and accessible as possible. (e.g. GitHub pull request comments with
failure reasons)

## Scope

To avoid entanglement of tests, tests should adhere, when possible, to their own area of concern. Practice separation of concerns as much as possible. There are three areas of concern.

1. **CMS** - This is the functioning of being able to login, edit and publish content. It's boundary of concern ends at the GraphQL endpoints.
2. **Front-end** - This is the Metalsmith build that creates the HTML front-end from the content accessed at the GraphQL endpoints of the CMS.
3. **Content** - This is the realm of making sure menu links and other links in content work. 508 testing is also part of content testing.

Entanglement should be avoided because it causes people from the non-relevant team to spend time solving issues that are not in their area of concern.
Example: _Developers chasing down a mis-entered content link is not a good use of time._
End to End tests should be achieved when possible, by each area of concern providing coverage for their particular area.

## VA.gov CMS Test Suite

Always refer to the file `tests.yml` for the canonical list of required tests that are included in the automated testing system, and are required to pass before merge or deployment,

There are 3 main types of tests:

1.  **Static Tests:**
    Static tests are run by [git pre-commit hooks](https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks#_committing_workflow_hooks): Developers cannot commit code if
    any of these tests fail. Static tests only require the source code. No active database is needed.

    See the [hooks/pre-commit file](../hooks/pre-commit) for the exact
    command run before git commit.

    Each static test should also be run by a corresponding [Github action](https://docs.github.com/en/actions) and block PR merges on failure. Github Actions are added and edited in the [Github workflows directory](../.github/workflows). When adding a new GitHub Action, our preferred process to minimize technical debt and maintenance is the following:
    1. When possible, use a well-supported action from the open-source community. The [reviewdog](https://github.com/reviewdog) organization on GitHub is often a good place to start looking.
    1. If the Action cannot meet our requirements without modifications, resolve in this order:
      1. Modify the existing Action for configurability and attempt to contribute the modification upstream
      1. If the contribution is not accepted or greater modifications are needed, create a new Action in a repo under the DSVA GitHub organization
        1. If possible, try to contribute this new Action upstream under the [reviewdog](https://github.com/reviewdog) space

    Existing tests:
    1. `va/tests/phpcs` - "PHP CodeSniffer" tests ensure coding standards
       are met.
    1. [`CodeQL`](../.github/workflows/codeql.yml) - Automated vulnerability scanning
    1. [`ESlint`](/.github/workflows/eslint.yml) - JavaScript linting
    1. [`PHPCS`](../.github/workflows/phpcs.yml) - PHP linting
    1. [`StyleLint (modules)`](../.github/workflows/stylelint-modules.yml) - JavaScript style checks for custom Drupal module code
    1. [`StyleLint (themes)`](../.github/workflows/stylelint-themes.yml) - JavaScript style checks for custom Drupal theme code
    1. [`PHPStan`](../.github/workflows/phpstan.yml) - Static code analysis

1.  **WEB Integration Tests** (e.g. WEB == FE decoupled [content-build](https://patch-diff.githubusercontent.com/raw/department-of-veterans-affairs/content-build/) repo)

1.  **Functional Tests**

    1. `va/tests/phpunit` - The CMS PHPUnit Tests include a number of functional tests, including creating media, testing GraphQL, performance and security. See the [tests/phpunit folder](tests/phpunit) to see all the PHPUnit tests.

        Utilizing the DrupalTestTraits library with PHPUnit gives developers the ability to bootstrap Drupal and write tests in PHP without an abstraction layer provided by Gherkin. PHPUnit is the preferred tool to write tests due to its speed of execution.

        Run all tests:

        ```
        ddev phpunit
        ```

        Run a specific test with the "path" argument:
        The path can be to a specific test file, or a directory with tests.

        ```
        ddev phpunit-run {Path-to-test}

        ddev phpunit-run docroot/modules/contrib/config_split/tests/src/Kernel/ConfigSplitCliServiceTest.php
        ```

        Run a specific test:

        ```
        ddev phpunit-run {Path-to-test} --filter {test-function-name}

        ddev phpunit-run docroot/modules/contrib/config_split/tests/src/Kernel/ConfigSplitCliServiceTest.php --filter testGrayAndBlackListExport
        ```

        Run a group of PHPUnit tests:

        ```sh
        ddev phpunit-run . --group security
        ```

    1. `va/tests/behat` - The Behat test suite includes:

        1. _Content-Edit-Web-Rebuild test:_

            This test is critical: it ensures the CMS does not break the WEB build.

            See [tests/behat/features/content.feature](../tests/behat/features/content.feature)

        1. _Permissons Test:_

            See [tests/behat/features/perms.feature](../tests/behat/features/perms.feature)

        1. _Drupal Spec Tests:_ The DST tool enforces the desired structure of the Drupal site by generating Gherkin Feature files. See [tests/behat/drupal-spec-tool](../tests/behat/drupal-spec-tool/) folder for all of the tests and more information on managing the Drupal Spec Tool and [VA's Spec tool doc here](https://airtable.com/invite/l?inviteId=invOjKEIyZCQY5YRy&inviteToken=eea85291ef1cd72ce9560c5a833a18673ef10a92050f9210e878702e81ec49b3&utm_source=email).

        Run a specific behat test with the `--name` or `--tags` options:

        ```sh
        ddev behat --tags=dst
        ```

    1. `va/tests/cypress` - The [Cypress](https://github.com/cypress-io/cypress) test suite includes end-to-end behavioral and accessibility tests.

          For local development, it's recommended to run Cypress from your host machine, not within ddev.  This requires that the browser be installed, so, from the project root on the _host_ machine, run:
          
          ```sh
          node ./node_modules/.bin/cypress install
          ```

          To run a specific test:


          ```sh
          node_modules/.bin/cypress run --spec "tests/cypress/integration/behavioral/content_release.feature"
          ```

          To run and debug cypress tests in a web UI, run the following commands from the project root on your local machine (_not_ within ddev):

          ```sh
          npm run test:cypress:interactive
          ```

          You will see a window with a list of tests. Just click on the name of any test to run it within a browser.


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

## Related Tools

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

#### Otherwise...

This test is run by a GitHub Action.  Click the "Details" link in the row of
the failed test.  This should take you to a view of the run details, which
should contain the logged information.

----

[Table of Contents](../README.md)
