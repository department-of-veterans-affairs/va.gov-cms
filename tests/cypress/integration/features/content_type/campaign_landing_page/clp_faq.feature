@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable FAQ page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "FAQs"
    And I enable the page segment
    And I click the "Add Page-Specific Q&A" button
    Then I can fill in "Question" field with fake text
    And I can fill in "Text" field with fake text
    And I should see "Add Reusable Q&A"
    And I should see "Add a link to more FAQs"

  Scenario: Test FAQ page segment requirements
    Given I am logged in as a user with the "content_admin" role
    Then I create a "campaign_landing_page" node and continue

    # Test maximum FAQs cannot be exceeded.
    When I click to expand "FAQs"
    And I enable the page segment within selector "#edit-group-faqs"
    And I click the "Add Page-Specific Q&A" button
    And I fill in "Question" field with fake text
    And I fill in ckeditor "edit-field-clp-faq-paragraphs-0-subform-field-answer-0-subform-field-wysiwyg-0-value" with "Adding Page-Specific Q&As..."
    And I click the "Add Reusable Q&A Group" button
    And I click to expand "Q&As"
    And I select 10 items from the "Add Reusable Q&As" Entity Browser modal
    And I wait "2" seconds
    And I fill in field with selector "#edit-revision-log-0-value" with fake text
    And I save the node
    Then I should see an element with the selector "#edit-field-clp-faq-paragraphs-0-subform-field-question-0-value.error"
    And I should see "Remove Page-Specific or Reusable Q&As"

    # Test fewer than minimum FAQs cannot be added.
    When I click the button with selector "[data-drupal-selector='edit-field-clp-reusable-q-a-0-top'] .paragraphs-dropdown-toggle"
    And I click the button with selector "[name='field_clp_reusable_q_a_0_remove']"
    And I fill in field with selector "#edit-revision-log-0-value" with fake text
    And I save the node
    Then I should see an element with the selector "#edit-field-clp-faq-paragraphs-0-subform-field-question-0-value.error"
    And I should see "Add Page-Specific or Reusable Q&As"

    # Test required Q&As if FAQ segment is enabled
    When I click the button with selector "[data-drupal-selector='edit-field-clp-faq-paragraphs-0-top'] .paragraphs-dropdown-toggle"
    And I click the button with selector "[name='field_clp_faq_paragraphs_0_remove']"
    And I fill in field with selector "#edit-revision-log-0-value" with fake text
    And I save the node
    Then I should see "A minimum of 3 Q&As is required when the FAQ page segment is enabled. Disable the FAQs page segment if there are no Q&As to add."

    # Test that no Q&A is required if the FAQ page segment is disabled
    When I click to expand "FAQs"
    And I disable the page segment
    And I fill in field with selector "#edit-revision-log-0-value" with fake text
    And I save the node
    Then the element with selector ".messages__content" should contain "Campaign Landing Page"
    And the element with selector ".messages__content" should contain "has been updated."

