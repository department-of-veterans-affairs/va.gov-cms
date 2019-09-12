# Testing

The code for cms.VA.gov undergoes numerous tests before merging, and tests
are run before deployment and release.

The automated test suite for cms.VA.gov is defined in the [tests.yml](tests.yml)
 file and is run using the [Yaml-Tests](https://github.com/provision-ops/yaml-tests) tool, allowing the same command to be used local development, in CMS
 -CI and for production releases.

The *Yaml Tests* Composer plugin is required by the main va.gov-cms 
`composer.json` file.

## Goals

To adopt a strong test driven culture, the testing tools must:

1. Run the same tests in multiple environments with minimal configuration and
 a single command.
2. Allow developers to define tests to include in the suite and to write the
 tests.
3. Provide feedback to developers as quickly as possible and make test output as
 readable and accessible as possible.

The **Yaml Tests** tool was designed with these goals in mind.

## VA.gov CMS Test Suite

Always refer to the file `tests.yml` for the canonical list of required tests
 that are included in the automated testing system, and are required to pass 
 before merge or deployment,
 
There are 4 main types of tests:

1. **Static Tests:**
  Static tests are run as a Git Commit hook: Developers cannot commit code if
  any of these tests fail. Static tests only require the source code. No site
  is needed. 

    See the [hooks/pre-commit file](../hooks/pre-commit) for the exact
  command run before git commit.
    1. `va/tests/phpcs` - "PHP CodeSniffer" tests ensure coding standards
     are met.
    1. `va/tests/phplint` - Ensures no syntax errors are present.
1. **Deployment Tests:** These commands are run during a production
 deployment. By treating them as tests, developers can identify failures in
  the deployment process before they go to production.
    1. `va/deploy/0-composer` - Composer Install.
    1. `va/deploy/1-cache` - Cache Rebuild.
    1. `va/deploy/2-update` - Database Update.
    1. `va/deploy/3-config` - Configuration Import.
    
    *NOTE: The tests are run in the order listed in `tests.yml`. The numbers
     here are to keep them in order when listed on GitHub.*
1. **WEB Integration Tests**
    1. `va/web/build` - Build the front-end from the current site. (Alias for
     `composer va:web:build`).
    1. `va/web/unit` - Run the front-end unit tests. (Not yet merged. See
     [PR547](https://github.com/department-of-veterans-affairs/va.gov-cms/pull/547))
     
    The long term goal is to run *all* of the **WEB** project's tests in our
     test
    suite, but more work is needed in the **WEB** codebase to make that
    possible.
     
1. **Functional Tests** 
    1. `va/tests/phpunit` - The CMS PHPUnit Tests include a number
     of tests, including Creating Media, testing GraphQL, Performance tests
     , Security, and more. See the [tests/phpunit folder](tests/phpunit) to
      see all the PHPUnit tests. 
      
        Run a specific PHPUnit test with the "path" argument: 
        
        ```
        lando phpunit {Path-to-test}
        ```
       
        Run a group of PHPUnit tests:
       
        ```sh
        lando phpunit . --group security
        ```
      
    1. `va/tests/behat` - The Behat test suite includes:
        1. *Content-Edit-Web-Rebuild test:* 
        
            This test is critical: it ensures the CMS does not break the WEB
             build.
             
             See [tests/behat/features/content.feature](../tests/behat/features/content.feature)
        
        1. *Permissons Test:* 
          
            See [tests/behat/features/perms.feature](../tests/behat/features/perms.feature)
           
        1. *Drupal Spec Tests:* The DST tool enforces the desired structure of
         the Drupal site by generating Gherkin Feature files. See 
         [tests/behat/drupal-spec-tool](../tests/behat/drupal-spec-tool/) folder
          for all of the tests and more information on managing the Drupal Spec 
          Tool.
      
        Run a specific behat test with the `--name` or `--tags` options:
        
        ```
        lando behat --tags=spec 
        ```
       
## Running Tests

The main way to run Yaml-tests is the `composer yaml-tests` command.

Run `composer yaml-tests --help` for more information.

### Composer Command:  `composer yaml-tests` 

All composer commands can be shortened to any unique string, so `composer y
` is an alias for `composer yaml-tests`.

Run `composer y --help` to see more options.

### Bin dir executable: `./bin/yaml-tests`

There is a `bin/yaml-tests` file provided with this package. You can run it
 from the project root.
 
*NOTE: The `bin` directory is automatically included in the $PATH for all
 Composer commands, including yaml-tests itself.*
 
  See [Composer Paths](#composer-configbinpath-and-path) for more information
   on Composer and $PATH.
 
### Local Testing with Lando: `lando test`

This project is configured to work with Lando out of the box.

Lando commands are listed in [`.lando.yml`](../.lando.yml). There are some
 helper commands that map to Composer Yaml-test commands.
 

 | Lando Command        | Composer Command
 |--------------        |----------------
 |lando test            | composer yaml-tests 
 |lando test va/deploy  | composer yaml-tests va/deploy 
 |lando web-build       | composer va:web:build  
 |lando phpunit         | composer yaml-tests  va/tests/phpunit
 |lando web-build       | composer va:web:build  
 |lando behat           | cd /app/tests/behat && /app/bin/behat
 
*NOTES:* 
  - Any arguments passed to the `lando` command are passed through to the
 composer command.
  - Any Composer command can be run inside a Lando container after you call
   `lando ssh`.
   
@TODO: Standardize this mapping on Yaml-tests. It will continue to improve
 with features like timing, profiling, output logging, etc.
 
### Limit tests to run
You can add an argument to filter the tests to run:
                   
 ```sh
 # Run the entire test suite.
 composer yaml-tests
 
 # Run `va/tests/phpunit` only
 composer yaml-tests phpunit
 
 # Run all `va/deploy/*` tests.
 composer yaml-tests va/deploy
 ```
 

## GitHub Integration 
 
The Yaml-Tests tool also integrates with GitHub, providing pass/fail commit
 status for each test listed in `tests.yml`, and posting errors as comments
  on the commit's page on GitHub.com.

### Branch Enforcement Rules
   
All of the tests in `tests.yml` are required to pass before a Pull Request
 can be merged. This is enforced by GitHub.com and is configurable: See the
  [Branches section of the repository's Settings](https://github.com/department-of-veterans-affairs/va.gov-cms/settings/branches).  

![GitHub comment with the output from a failed test.](github-test-fail-comment.png)

If an individual test fails, the Yaml-test tool creates a comment on the
 commit with the failed test results. 
 The test results are also logged in DevShop. 

### GitHub Statuses API

The API used by Yaml Tests and GitHub for testing code is called the
 "Statuses API": https://developer.github.com/v3/repos/statuses/
 
It stores test results attached to the commit, based on SHA.

Yaml-tests reads the SHA of git repository, runs the test, and sends the state
to GitHub Status API, which sends it along to the users.

What you end up seeing is something like this: 

![GitHub Commit Statuses, some failing, some passing.](github-commit-status.png)

*NOTE: The GitHub API stores this information attached to the Commit, not to
 the PR.*
 
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
  an alias for `yaml-tests` but with a different file and with a filter, add
   this to `composer.json`:
 
  ```json
  {
    "scripts": {
      "special-tests": [
        "which yaml-tests",
        "yaml-tests myuniquetests --file=custom.yml"
      ]
    }
  }
  ``` 

 Or, if you want to run `drush` or `npm` (or any other script in the `bin` dir) as a
  test, just call the script name:
  
  ```yaml
  # tests.yml example that runs commands from the project's ./bin directory.
  example/drush/status: drush status
  example/drush/version: drush --version
  example/npm/which: which npm
  example/npm/version: npm --version
  ```

The `which npm` command helps you find out which file is actually being run.

In this project's case, `which npm` would print `/path/to/va.gov-cms/bin/npm`.


## Fortify security scans

Fortify scans are run manually. @TODO: Add documentation on Fortify

## Nightwatch accessibility testing

Nightwatch is not currently included in the CMS test suite.

@TODO: Add Nightwatch tests using composer npm-asset and add to `tests.yml`

## Manual visual regression

@TODO: Document what this means.


[Table of Contents](../README.md)
