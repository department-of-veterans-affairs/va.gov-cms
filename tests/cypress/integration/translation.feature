Feature: Access translation tools.

@api @user @translation
  Scenario: Translation admins can access configuration and interface translation.
    Given I am logged in as a user with the "translation_manager" role
    And I am at "/admin/config/regional/translate"
    Then I should see "User interface translation"

    And I am at "/admin/config/regional/config-translation"
    Then I should see "Configuration translation"

  Scenario: Content admins can not access configuration and interface translation.
    Given I am logged in as a user with the "content_admin" role
    Then I should receive status code 403 when I request "/admin/config/regional/translate"
    And I should receive status code 403 when I request "/admin/config/regional/config-translation"
