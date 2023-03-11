Feature: Access translation tools.

@api @user @translation
  Scenario: Translation admins can access configuration and interface translation.
    Given I am logged in as a user with the "translation_manager" role
    And I am at "/admin/config/regional/translate"
    Then I should see "User interface translation"

    And I am at "/admin/config/regional/config-translation"
    Then I should see "Configuration translation"
