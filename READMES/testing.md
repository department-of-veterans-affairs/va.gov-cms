# Testing

The code for cms.VA.gov undergoes numerous tests before merging, and tests
are run before deployment and release.

The test suite for cms.VA.gov is defined in the [tests.yml](tests.yml) file
 and is run using the [Yaml-Tests](https://github.com/provision-ops/yaml-tests) 
 tool, allowing the same command to be used local development, in CMS-CI, and 
 for production releases.
 
## GitHub Integration and Enforcement 
 
The Yaml-Tests tool also integrates with GitHub, providing pass/fail commit
 status for each test listed in `tests.yml`, and posting errors as comments
  on the commit's page on GitHub.com.
  
All of the tests in `tests.yml` are required to pass before a Pull Request
 can be merged. This is enforced by GitHub.com and is configurable: See the
  [Branches section of the repository's Settings](https://github.com/department-of-veterans-affairs/va.gov-cms/settings/branches).  

## Test Suite

There are 3 main types of tests:

1. **Static Tests:**
  Static tests are run as a Git Commit hook: Developers cannot commit code if
  any of these tests fail. Static tests only require the source code. No site
  is needed.
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
     
    The target is to run *all* of the **WEB** project's tests in our test
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
       

## Test Runner
The [Yaml-Tests](https://github.com/provision-ops/yaml-tests) Composer Plugin was created 
to standardize the way tests were run and make it easy to maintain the suite in
code. 

Run the `yaml-tests` command from the root of the git repository. You can add
 an argument to only run tests where the entered string is in the name.
  
If you are inside the container/server:

```sh
# Run the entire test suite.
composer yaml-tests

# Run `va/tests/phpunit` only
composer yaml-tests phpunit

# Run all `va/deploy/*` tests.
composer yaml-tests va/deploy
```

OR if you are using Lando and are in your native terminal, the `lando test
` command is an alias for `composer yaml-tests`

```
$ lando test
```

Always refer to the file `tests.yml` for the list of tests that are included in
 the automated testing system.

## @TODO: Document Additional Testing:

## Fortify security scans
## Nightwatch accessibility testing
## Manual visual regression

[Table of Contents](../README.md)
