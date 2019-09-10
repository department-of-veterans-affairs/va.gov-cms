# Testing

The command to run all tests on the codebase in the same way they are run in CI:

```
  composer yaml-tests
```

OR

```
  lando test
```

Check out the file `tests.yml` for the list of tests that are included in the
automated testing system.

## Behat

Running Behat Tests:
* `lando behat`  Will run all behat tests.
* `lando behat --tags=name-of-tag`  Will run just the behat tests that have that
tag.

## PHPunit
Running Phpunit Tests:
* `lando phpunit {Path-to-test}`
to run a test group use
* `lando phpunit . --group security`


## Fortify security scans
## Nightwatch accessibility testing
## Manual visual regression


@see[
  * [Build from CMS locally with a specific FE build](builds.md#building-cms-locally-with-a-specific-fe-build)

[Table of Contents](../README.md)
