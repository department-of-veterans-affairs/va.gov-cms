# Testing

The code for cms.va.gov undergoes numerous tests before merging, and tests
are run before deployment and release.

The automated test suite for cms.va.gov is defined in the [tests.yml](../tests.yml)
file and is run using the [Task](https://github.com/go-task/task) tool, allowing
the same command to be used local development, in CMS-CI and for production releases.

Task is installed by the `install_task.sh` script.

## Goals

To adopt a strong test driven culture, the testing tools must:

1. Run the same tests in multiple environments with minimal configuration and
   a single command.
2. Allow developers to define tests to include in the suite and to write the
   tests.
3. Provide feedback to developers as quickly as possible and make test output as
   readable and accessible as possible.

## Scope

To avoid entanglement of tests, tests should adhere, when possible, to their own
area of concern. Practice separation of concerns as much as possible. There are
three areas of concern.

1. **CMS** - This is the functioning of being able to login, edit and publish
   content. It's boundary of concern ends at the GraphQL endpoints.
2. **Front-end** - This is the Metalsmith build that creates the html front-end
   from the content accessed at the GraphQL endpoints of the CMS.
3. **Content** - This is the realm of making sure menu links and other links in
   content work. 508 testing is also part of content testing.

Entanglement should be avoided because it causes people from the non-relevant
team to spend time solving issues that are not in their area of concern.
Example: _Developers chasing down a mis-entered content link is not a good use
of time._
End to End tests should be achieved when possible, by each area of concern
providing coverage for their particular area.

## VA.gov CMS Test Suite

Always refer to the file `tests.yml` for the canonical list of required tests
that are included in the automated testing system, and are required to pass
before merge or deployment,

There are 4 main types of tests:

1.  **Static Tests:**
    Static tests are run by [git pre-commit hooks](https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks#_committing_workflow_hooks): Developers cannot commit code if
    any of these tests fail. Static tests only require the source code. No site
    is needed.

    See the [hooks/pre-commit file](../hooks/pre-commit) for the exact
    command run before git commit.
    
    Each static test should also be run by a corresponding [Github action](https://docs.github.com/en/actions) and block PR merges on failure. Github actions are added and edited in the [Github workflows directory](../.github/workflows). When adding a new Github action, our preferred process to minimize technical debt and maintenance is the following:
    1. When possible, use a well-supported action from the open-source community. The [reviewdog](https://github.com/reviewdog) organization on Github is often a good place to start looking.
    1. If the action cannot meet our requirements without modifications, resolve in this order:
      1. Modify the existing action for configurability and attempt to contribute the modification upstream
      1. If the contribution is not accepted or greater modifications are needed, create a new action in a repo under the DSVA Github organization
        1. If possible, try to contribute this new action upstream under the [reviewdog](https://github.com/reviewdog) space

    Existing tests:
    1. `va/tests/phpcs` - "PHP CodeSniffer" tests ensure coding standards
       are met.
    1. [`CodeQL`](../.github/workflows/codeql.yml) - Automated vulnerability scanning
    1. [`ESlint`](/.github/workflows/eslint.yml) - Javascript linting
    1. [`PHPCS`](../.github/workflows/phpcs.yml) - PHP Linting
    1. [`StyleLint (modules)`](../.github/workflows/stylelint-modules.yml) - Javascript style checks for custom module code
    1. [`StyleLint (thees)`](../.github/workflows/stylelint-themes.yml) - Javascript style checks for custom thee code

1.  **WEB Integration Tests**

    1. `va/web/build` - Build the front-end from the current site. (Alias for
       `composer va:web:build`).
    1. `va/web/unit` - Run the front-end unit tests. (Not yet merged. See
       [PR547](https://github.com/department-of-veterans-affairs/va.gov-cms/pull/547))

    The long term goal is to run _all_ of the **WEB** project's tests in our
    test
    suite, but more work is needed in the **WEB** codebase to make that
    possible.

1.  **Functional Tests**

    1. `va/tests/phpunit` - The CMS PHPUnit Tests include a number
       of tests, including Creating Media, testing GraphQL, Performance tests
       , Security, and more. See the [tests/phpunit folder](tests/phpunit) to
       see all the PHPUnit tests.

    Utilizing the DrupalTestTraits library with PHPUnit gives developers the
    ability to bootstrap Drupal and write tests in PHP without an abstraction
    layer provided by Gherkin. PHPUnit is the preferred tool to write tests
    due to its speed of execution.

    Run the tests specific to VA

    ```
    lando phpunit
    ```

        Run a specific PHPUnit test with the "path" argument:
        The path can be to a specific test file, or a directory with tests.

        ```
        lando phpunit-run {Path-to-test}

        lando phpunit-run docroot/modules/contrib/config_split/tests/src/Kernel/ConfigSplitCliServiceTest.php
        ```

        Run a specific test:

        ```
        lando phpunit-run {Path-to-test} --filter {test-function-name}

    lando phpunit-run docroot/modules/contrib/config_split/tests/src/Kernel/ConfigSplitCliServiceTest.php --filter testGrayAndBlackListExport
    ```

        Run a group of PHPUnit tests:

        ```sh
        lando phpunit-run . --group security
        ```

    1. `va/tests/behat` - The Behat test suite includes:

       1. _Content-Edit-Web-Rebuild test:_

          This test is critical: it ensures the CMS does not break the WEB
          build.

          See [tests/behat/features/content.feature](../tests/behat/features/content.feature)

       1. _Permissons Test:_

          See [tests/behat/features/perms.feature](../tests/behat/features/perms.feature)

       1. _Drupal Spec Tests:_ The DST tool enforces the desired structure of
          the Drupal site by generating Gherkin Feature files. See
          [tests/behat/drupal-spec-tool](../tests/behat/drupal-spec-tool/) folder
          for all of the tests and more information on managing the Drupal Spec
          Tool.

       Run a specific behat test with the `--name` or `--tags` options:

       ```
       lando behat --tags=dst
       ```

    1. `va/tests/behavioral` - The [Cypress](https://github.com/cypress-io/cypress) behavioral test suite includes end-to-end logged out tests.

       To run and debug cypress tests in a web UI, run the following commands from the project root on your local machine (not within lando):

       ```
       npm i && cd tests/behavioral && ../../node_modules/.bin/cypress open
       ```

       You will see a window with a list of tests. Just click on the name of any test to run it within a browser.

## Running Tests

The main way to run tests is the `./bin/task --taskfile=tests.yml` command.

Run `./bin/task --help` for more information.

_NOTE: The `bin` directory is automatically included in the $PATH for all
Composer commands, including Task itself._

See [Composer Paths](#composer-configbinpath-and-path) for more information
on Composer and $PATH.

### Local Testing with Lando: `lando test`

This project is configured to work with Lando out of the box.

Lando commands are listed in [`.lando.yml`](../.lando.yml). There are some
helper commands that map to shell commands.

| Lando Command        | Shell Command                                            |
| -------------------- | -------------------------------------------------------- |
| lando task           | ./bin/task                                               |
| lando test           | ./bin/task --taskfile=tests.yml                          |
| lando test va/deploy | ./bin/task --taskfile=tests.yml va/deploy                |
| lando web-build      | composer va:web:build                                    |
| lando phpunit        | ./bin/task --taskfile=tests.yml va/tests/phpunit         |
| lando web-build      | composer va:web:build                                    |
| lando behat          | cd /app/tests/behat && /app/bin/behat                    |

_NOTES:_

- Any arguments passed to the `lando` command are passed through to the
  composer command.
- Any Composer command can be run inside a Lando container after you call
  `lando ssh`.

### Limit tests to run

You can add an argument to filter the tests to run:

```sh
# Run the entire test suite.
./bin/task --taskfile=tests.yml

# Run `va/tests/phpunit` only
./bin/task --taskfile=tests.yml va/tests/phpunit
```

## GitHub Integration

The Task tool also integrates with GitHub through ReviewDog, providing pass/fail commit
status for each test listed in `tests.yml`, and posting errors as comments
on the commit's page on GitHub.com.

### Branch Enforcement Rules

All of the tests in `tests.yml` are required to pass before a Pull Request
can be merged. This is enforced by GitHub.com and is configurable: See the
[Branches section of the repository's Settings](https://github.com/department-of-veterans-affairs/va.gov-cms/settings/branches).

![GitHub comment with the output from a failed test.](images/github-test-fail-comment.png)

If an individual test fails, the Task tool creates a comment on the
commit with the failed test results.
The test results are also logged in Tugboat.

### GitHub Statuses API

The API used by Yaml Tests and GitHub for testing code is called the
"Statuses API": https://developer.github.com/v3/repos/statuses/

It stores test results attached to the commit, based on SHA.

Yaml-tasks reads the SHA of git repository, runs the test, and sends the state
to GitHub Status API, which sends it along to the users.

What you end up seeing is something like this:

![GitHub Commit Statuses, some failing, some passing.](images/github-commit-status.png)

_NOTE: The GitHub API stores this information attached to the Commit, not to
the PR._

*This means
if you open a second PR with the same commits, the commit status AND the
commit comments will show in *both* pull requests.*

### Composer, `config.bin-path`, and $PATH

Composer automatically loads the directory `bin` into the PATH of any
composer command or script. More accurately, it includes the directory set in
the `config.bin-dir` section of `composer.json`.

This means you only have to include the script name when referring to them in
`composer.json` or in `tests.yml`.

For example, if you wanted to create a `composer special-tests` command as
an alias for `yaml-tasks` but with a different file and with a filter, add
this to `composer.json`:

```json
{
  "scripts": {
    "special-tests": [
      "which yaml-tasks",
      "yaml-tasks myuniquetests --file=custom.yml"
    ]
  }
}
```

Or, if you want to run `drush` or `npm` (or any other script in the `bin` dir) as a
test, just call the script name:

```yaml
# tasks.yml example that runs commands from the project's ./bin directory.
example/drush/status: drush status
example/drush/version: drush --version
example/npm/which: which npm
example/npm/version: npm --version
```

The `which npm` command helps you find out which file is actually being run.

In this project's case, `which npm` would print `/path/to/va.gov-cms/bin/npm`.

## Fortify security scans

Fortify scans are run manually.

About Drupal Security Team Coverage
When a module is covered by the Drupal Security Team it means that the team will receive reports of vulnerabilities from the Drupal community and the general public and will work with the maintainer to fix and coordinate the module and advisory release.

Symfony and other non-Drupal.org hosted libraries are all out of scope for the Drupal Security Team, though the security team will occasionally work with these projects security teams to coordinate releases or help test etc. Symfony has an active security team and process/advisories (see https://symfony.com/blog/category/security-advisories).

Composer libraries don't have any defined process nor advisories, therefore this scan offers of additional scrutiny.

Excluded directories
Drupal 8 core and contributed modules covered by the Drupal Security Team were not included in the scan.

```
  ./docroot/core/**/*"
  ./docroot/includes/**/*"
  ./docroot/modules/contrib/**/*"
  ./docroot/themes/contrib/**/*"
  ./docroot/profiles/**/*"
  ./docroot/scripts/**/*"
```

Included Vendor Libraries
Vender libraries are third party open source packages included by Drupal core and modules to add functionality. For example Drupal 9 includes the Symfony open source project which in turn may include libraries from other open source projects. Symfony has an active security team monitoring security and posting process/advisories (see https://symfony.com/blog/category/security-advisories).

Whether these third party libraries are secure involves multiple factors (and has no definitive answer) project lifetime, maintenance status, frequency/size of major changes, number of maintainers, skills of maintainers in security topics, security of the projects own dependencies, security surface area (does the project deal with user actions, data, sessions, external systems etc), security architecture and threat model, code quality, documentation etc.


[Table of Contents](../README.md)
