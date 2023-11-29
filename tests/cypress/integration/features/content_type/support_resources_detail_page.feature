@content_type__support_resources_detail_page
Feature: Content Type: Resources and Support Detail Page

  Scenario: Foo Bar
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/support_resources_detail_page"
    And I click to collapse "Foo"
    And I click to expand "Bar"
    Then I can fill in field with selector "#foobar" with fake text
    And I should see an element with the selector "#foo-bar-button"
# Add media modal is tested in basic_requirements.feature
    And I should see "Add a link to foo"
    When I click the "Add Call to action" button
    Then I can fill in "Link" field with fake link
