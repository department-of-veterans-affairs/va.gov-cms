@text_validation
Feature: Text fields are validated
  In order to ensure a consistent experience for users
  As an editor
  I need my text fields validated

  Scenario: Long rich text fields are checked for any absolute links to the CMS.
    Given I am logged in as a user with the "content_admin" role
    And I create a "health_care_region_detail_page" node
    And I click the edit tab
    And I click the "Add Content block" button
    And I scroll to element "div.ck-content"
    And I fill in ckeditor "edit-field-content-block-0-subform-field-wysiwyg-0-value" with '<a href="https://staging.cms.va.gov/">test</a>'
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I save the node
    Then I should see "1 error has been found: Text"
    And I should see "\"test\" uses a URL ( https://staging.cms.va.gov/ ) that's only available on the VA network. Update the link to a valid public-facing page."

  Scenario: Long plain text fields are checked for any absolute links to the CMS.
    Given I am logged in as a user with the "content_admin" role
    And I create a "health_care_region_detail_page" node
    And I click the edit tab
    And I fill in 'Page introduction' with "https://prod.cms.va.gov/"
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I save the node
    Then I should see "1 error has been found: Page introduction"
    And I should see "The text contains a URL ( https://prod.cms.va.gov/ ) that's only available on the VA network. Update the link to a valid public-facing page."
