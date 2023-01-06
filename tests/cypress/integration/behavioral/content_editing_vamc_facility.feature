@content_editing_vamc_facilities
Feature: CMS Users may effectively create and edit VAMC facilities
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and create VAMC Facility as an editor
    When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
    And my workbench access sections are set to "372"
    Then I am at "node/1141/edit"

    # Edit facility
    When I select the "High" radio button
    Then the element with selector "#cke_edit-field-supplemental-status-more-i-0-value iframe" should contain "COVID-19 health protection: Levels high"
    When I select the "Medium" radio button
    Then the element with selector "#cke_edit-field-supplemental-status-more-i-0-value iframe" should contain "COVID-19 health protection: Levels medium"
    When I select the "Low" radio button
    Then the element with selector "#cke_edit-field-supplemental-status-more-i-0-value iframe" should contain "COVID-19 health protection: Levels low"


