
Feature: The VA Website is generated inside the Drupal CMS code.
  In order to fully test va.gov,
  As anyone involved in the project
  I need a test to run against the CMS and the WEB site in once shot.

  @api @errors
  Scenario: Ensure the static site builds an index.html file with content.
    Given I am at "/static/"
    Then I should see "Access and manage your VA benefits and health care" in the "h1.homepage-heading" element

    Then print current URL

    When I am logged in as a user with the "administrator" role
    And I am at "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services"
    When I am logged in as a user with the "administrator" role
    And I am at "node/2/edit"
    And I fill in "Page title" with "VA blind and low vision rehabilitation services - EDITED"
    And I press "Save"
    Then I should see "Benefits detail page VA blind and low vision rehabilitation services - EDITED has been updated."
    Then print current URL
    When I run "composer va:web:build"
    And I am at "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services - EDITED"
    And I am at "/web/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services - EDITED"