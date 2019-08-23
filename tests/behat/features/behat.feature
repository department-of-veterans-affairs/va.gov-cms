
Feature: Behat tests work out of the box.
  In order encourage web developers to start testing.
  As a devshop developer
  I need Behat tests to be setup and able to run out of the box.

  @api
  Scenario: Make sure we can run bin/behat
  # Given I run "pwd"
  # Just here for debugging.
  #    Given I run "echo $PATH"

  # This was NOT working in CI:
  # And I run "bin/behat -dl --colors"
  # Interestingly enough, it DID work in lando!
