@content_type__support_resources_detail_page
Feature: Content Type: Resources and Support Detail Page

  @critical_path
  Scenario: Validate Tags with attention to conditional fields
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/support_resources_detail_page"
    Then I should see "Tags"

    Given I select option "- None -" from dropdown "Audience"
    Then I should not see an element with the selector "#edit-field-tags-0-subform-field-audience-beneficiares-wrapper"
    And I should not see an element with the selector "#edit-field-tags-0-subform-field-non-beneficiares-wrapper"

    Given I select option "Beneficiaries" from dropdown "Audience"
    When I check all checkboxes within "#edit-field-tags-0-subform-field-audience-beneficiares"
    Then I should see an element with the selector "#edit-field-tags-0-subform-field-audience-beneficiares-wrapper"
    And I should not see an element with the selector "#edit-field-tags-0-subform-field-non-beneficiares-wrapper"

    Given I select option "Non-beneficiaries" from dropdown "Audience"
    Then I should not see an element with the selector "#edit-field-tags-0-subform-field-audience-beneficiares-wrapper"
    And I should see an element with the selector "#edit-field-tags-0-subform-field-non-beneficiares-wrapper"
