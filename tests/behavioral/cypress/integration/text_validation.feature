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
    And I scroll to element 'div.cke_inner'
    And I fill in ckeditor "field-content-block-0" with '<a href="https://staging.cms.va.gov/">test</a>'
    And I save the node
    Then I should see "1 error has been found: Text"
    And I should see 'The link "test" contains an absolute CMS URL ( https://staging.cms.va.gov/ ).'

  Scenario: Long plain text fields are checked for any absolute links to the CMS.
    Given I am logged in as a user with the "content_admin" role
    And I create a "health_care_region_detail_page" node
    And I click the edit tab
    And I fill in 'Page introduction' with "https://prod.cms.va.gov/"
    And I save the node
    Then I should see "1 error has been found: Page introduction"
    And I should see "The text contains an absolute CMS URL ( https://prod.cms.va.gov/ )."
