@api
Feature: Expandable text component
  As a user
  I want to make sure that expandable text add widget component is there

  @spec @expandable_text
  Scenario: Ensure that Add Component link is on page
    Given I am logged in as a user with the administrator role
    When I visit "/node/add/page"
    When I click on the element with selector "#field-content-block-expandable-text-add-more"
    Then I should see "Text Expander"
    When I fill in "#edit-title-0-value" with the text "Expandable Test Node"
    And I fill in "field_content_block[1][subform][field_text_expander][0][value]" with "test expand title"
    And I fill in "edit-field-content-block-1-subform-field-wysiwyg-0-value" with "test expand body"
    And I click on the element with selector "#edit-submit"
    Then I should see "Basic page Expandable Test Node has been created."
    When I click on the element with selector "[title='Edit Expandable Test Node']"
    Then I click on the element with selector "#field-content-block-expandable-text-add-more"
    Then I should see "test expand title"
    And I should see "test expand body"
