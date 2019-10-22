@api
Feature: The VA Website is generated inside the Drupal CMS code.
  In order to confirm cms items are being included in static build
  As anyone involved in the project
  I need to run tests against the CMS and the WEB site in one shot.

@frontend
  Scenario: Ensure the static site builds an index.html file with content "Access and manage your VA benefits and health care"
    Given I am at "/static/"
    Then I should see "Access and manage your VA benefits and health care" in the "h1.homepage-heading" element
    Then print current URL

  @unpublished_content @frontend
  Scenario: Unpublish health-care and confirm it is not in build
      """
      Necessary to run the build twice in this file to toggle
      node from unpublished to published in static build
      """
    When I am logged in as a user with the "administrator" role
    Given I set the node with title "VA health care" to "unpublished"
    When I run "cd .. && composer va:web:build"
    And I am on "/static/health-care"
    Then the response status code should be 403

  @errors @cms_in_static @frontend
  Scenario: Log in, edit, publish, unpublish, and save nodes and see changes in the CMS and the front end website. Confirm cms menu items are in static site build.
    When I am logged in as a user with the "administrator" role
    And I am at "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services"
    And I am at "node/2/edit"
    And I fill in "Page title" with "VA blind and low vision rehabilitation services - EDITED"
    And I press "Save"
    Then I should see "Benefits detail page VA blind and low vision rehabilitation services - EDITED has been updated."
    Then I set the node with title "VA health care" to "published"
    Then print current URL
    When I run "cd .. && composer va:web:build"
    And I am at "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services - EDITED"
    And I am at "/static/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services - EDITED"
    Then print current URL
    Given I am at "/static/health-care"
    Then the response status code should be 200
 