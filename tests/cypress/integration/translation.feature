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

  Scenario: Content admin can create a node and then translate it.
    Given I am logged in as a user with the "content_admin" role
    And I create a "landing_page" node
    And I click the "Translate" link
    And I click the button with selector "ul.dropbutton a[hreflang=es]"
    And I fill in field with selector "#edit-field-intro-text-wrapper textarea" with value "This is not Spanish."
    And I fill in field with selector "#edit-revision-log-wrapper textarea" with value "This is a revision message."
    And I click the button with selector "form#node-landing-page-form input#edit-submit"
    Then I should see "This is not Spanish."

