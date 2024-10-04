@content_type__news_story
Feature: Content Type: Story

  Scenario: Log in and create a story
    Given I am logged in as a user with the "content_admin" role
    Then I create a "news_story" node