@api @mock_va_gov_urls
Feature: The VA Website is generated inside the Drupal CMS code.
  In order to confirm cms items are being included in static build
  As anyone involved in the project
  I need to run tests against the CMS and the WEB site in one shot.

@frontend
  Scenario: Ensure the static site builds an index.html file with content "Access and manage your VA benefits and health care"
    Given I am at "/static/"
    Then I should see "Access and manage your VA benefits and health care" in the "h1.homepage-heading" element
    Then print current URL

  @errors @cms_in_static @frontend
  Scenario: Log in, edit, publish, unpublish, and save nodes and see changes in the CMS and the front end website. Confirm cms menu items are in static site build.
    When I am logged in as a user with the "administrator" role

    # Test content editing.
    And I am at "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services"
    And I am at "node/2/edit"
    And I fill in "Page title" with "VA blind and low vision rehabilitation services - EDITED"
    And I press "Save"
    Then I should see "Benefits detail page VA blind and low vision rehabilitation services - EDITED has been updated."
    And I should see "Recent changes" in the ".view-right-sidebar-latest-revision" element

    # Test content publishing.
    Then I set the node with title "VA blind and low vision rehabilitation services - EDITED" to "published"

    # Test content unpublishing.
    Then I set the node with title "VA health care" to "unpublished"
    Then print current URL

    # Build the static site.
    When I run "cd .. && composer va:web:build"
    And I am at "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
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
    And I am on "/static/health-care"
    Then the response status code should be 403

    # Re-publish the VA health care page to prevent broken link warnings.
    Then I set the node with title "VA health care" to "published"
    And I am on "/health-care"
    Then I should see "Published" in the ".view-id-right_sidebar_latest_revision" element
