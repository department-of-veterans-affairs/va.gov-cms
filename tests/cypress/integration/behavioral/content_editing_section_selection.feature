@content_editing_section_selection
Feature: CMS Users may effectively select a section and have it winnow correctly
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and create content
    When I am logged in as a user with the "content_admin" role

    # Create our initial draft
    # We need to target an existing node
    # ("Geriatrics - Minneapolis VA Medical Center")
    # to prevent unique validation failure.
    Then I am at "node/51887/edit"
    # Non-Lovell test
    And I select option "---VA Alaska health care" from dropdown "Section"
    Then an option with the text "VA Alaska health care" from dropdown with selector "#edit-field-facility-location" should be selectable
    Then an option with the text "VA Alaska health care" from dropdown with selector "#edit-field-regional-health-service" should be selectable


    # Lovell TRICARE test
    And I select option "----Lovell - TRICARE" from dropdown "Section"
    Then an option with the text "Lovell Federal TRICARE health care" from dropdown with selector "#edit-field-facility-location" should be selectable
    Then an option with the text "Lovell Federal TRICARE health care" from dropdown with selector "#edit-field-regional-health-service" should be selectable


    # Lovell VA test
    And I select option "----Lovell - VA" from dropdown "Section"
    Then an option with the text "Lovell Federal VA health care" from dropdown with selector "#edit-field-facility-location" should be selectable
    Then an option with the text "Lovell Federal VA health care" from dropdown with selector "#edit-field-regional-health-service" should be selectable

    # Lovell Federal umbrella test
    Then an option with the text "Lovell Federal health care" from dropdown with selector "#edit-field-administration" should not be selectable
