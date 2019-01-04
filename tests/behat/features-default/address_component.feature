@api
Feature: Address field component
  As a user
  I want to make sure that address field component is there

  @spec @address
  Scenario: Ensure that Address field component is on page
    Given I am logged in as a user with the administrator role
    When I visit "/node/add/page"
    When I click on the element with selector "#field-content-block-address-add-more"
    Then I should see "Street address"
    When I fill in "#edit-title-0-value" with the text "Address Test Node"
    And I fill in "field_content_block[1][subform][field_address][0][address][given_name]" with "test_name_first"
    And I fill in "field_content_block[1][subform][field_address][0][address][family_name]" with "test_name_last"
    And I fill in "field_content_block[1][subform][field_address][0][address][address_line1]" with "123 Elm St."
    And I fill in "field_content_block[1][subform][field_address][0][address][locality]" with "Knoxville"
    And I fill in "field_content_block[1][subform][field_address][0][address][administrative_area]" with "TN"
    And I fill in "field_content_block[1][subform][field_address][0][address][postal_code]" with "37916"
    And I click on the element with selector "#edit-submit"
    Then I should see "Basic page Address Test Node has been created."
