@platform__linky
Feature: Linky

  @ignore
  Scenario: Confirm that Linky replaces external URLs correctly.
    Given I am logged in as a user with the "content_admin" role
    And I create a "press_release" node
    When I edit the node
    And I fill in ckeditor "edit-field-press-release-fulltext-0-value" with fake text including links
    Then an element with the xpath '//*[@id="edit-field-press-release-fulltext-wrapper"]//div[contains(@class, "ck-content")]//a' should exist
    When I set the Cypress variable "linkyHref" to the value of the attribute "href" of the element with xpath '//*[@id="edit-field-press-release-fulltext-wrapper"]//div[contains(@class, "ck-content")]//a'
    And I scroll to element '#edit-field-press-release-fulltext-wrapper'
    And I really click the link with xpath '//*[@id="edit-field-press-release-fulltext-wrapper"]//div[contains(@class, "ck-content")]//a'
    Then I should see the value of the Cypress variable "linkyHref"
