@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable FAQ page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "FAQs"
    And I enable the page segment
    Then I click the "Add Q&A" button
    And I fill in "Question" field with fake text
    And I fill in "Answer" field with fake text
    Then I click the "Add Call to action" button
    And I fill in "Link" field with fake link
    And I fill in "Link text" field with fake text
