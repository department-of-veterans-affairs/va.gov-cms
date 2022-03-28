@api @mock_va_gov_urls
Feature: The VA Website is generated inside the Drupal CMS code.
  In order to confirm cms items are being included in static build
  As anyone involved in the project
  I need to run tests against the CMS and the WEB site in one shot.

  @errors @cms_in_static @frontend
  Scenario: Log in, edit, publish, unpublish, and save nodes and see changes in the CMS and the front end website. Confirm cms menu items are in static site build.
    When I am logged in as a user with the "administrator" role

    # Test content editing and publishing.
    And I am at "node/2/edit"
    And I fill in "Page title" with "VA blind and low vision rehabilitation services - EDITED"
    And I select "Published" from "edit-moderation-state-0-state"
    And I fill in "Revision log message" with "Test publishing"
    And I press "Save"
    Then I should see "VA blind and low vision rehabilitation services - EDITED\u003C\/a\u003E\u003C\/em\u003E has been updated."
    And I should see "Recent changes" in the ".view-right-sidebar-latest-revision" element

    # Build the static site.
    When I run "cd .. && composer va:web:build"
    When I am at "/static/"
    Then I should see "Access and manage your VA benefits and health care" in the "h1.homepage-heading" element
    Then print current URL
    When I am at "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services - EDITED"

    # Test preview functionality.
    And I follow "Preview"
    Then I should not see "Static content file does not yet exist"
    And I should see "VA blind and low vision rehabilitation services - EDITED"
    And I should not see a ".view-right-sidebar-latest-revision" element
    And the url should match "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"

    # Test direct access to static pages.
    And I am at "/static/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services - EDITED"
    Then print current URL
