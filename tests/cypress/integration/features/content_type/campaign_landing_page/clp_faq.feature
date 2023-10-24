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
