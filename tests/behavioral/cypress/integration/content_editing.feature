@content_editing
Feature: CMS Users may effectively create & edit content
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and confirm that System-wide alerts can be created and edited
    When I am logged in as a user with the "content_admin" role

    # Create our initial draft
    # We need to target an existing node
    # ("Operating status - VA Pittsburgh health care")
    # to prevent unique validation failure.
    Then I am at "node/1010/edit"
    And I click the "Add new banner alert" button
    And I select option "Information" from dropdown "Alert type"
    And I fill in "Title" with "[Test Data] Alert Title"
    And I fill in ckeditor "edit-field-banner-alert-form-1-field-body-0-value" with "[Test Data] Alert Body"
    And I click the "Create banner alert" button
    And I wait "5" seconds
    And I click the "Save draft and continue editing" button
    Then I should see "Pages for the following VAMC systems"
    And I should see "[Test Data] Alert Title"
