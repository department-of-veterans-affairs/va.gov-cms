@api
Feature: Alert component
  As a user
  I want to make sure that alert add widget component is there

  @spec @alert
  Scenario: Ensure that Alert Component link is on page
    Given I am logged in as a user with the administrator role
    When I visit "/node/add/page"
    When I click on the element with selector "#field-content-block-alert-add-more"
    Then I should see "Alert Message"
    When I fill in "#edit-title-0-value" with the text "Alert Test Node"
    And I fill in "field_content_block[1][subform][field_alert_heading][0][value]" with "test alert title"
    And I fill in "field_content_block[1][subform][field_alert_message][0][value]" with "test alert body"
    And I click on the element with selector "#edit-submit"
    Then I should see "Basic page Alert Test Node has been created."
    When I click on the element with selector "[title='Edit Alert Test Node']"
    Then I click on the element with selector "#field-content-block-alert-add-more"
    Then I should see "test alert title"
    And I should see "test alert body"
