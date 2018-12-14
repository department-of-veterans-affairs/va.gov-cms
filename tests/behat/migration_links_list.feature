@api
Feature: Links List Migration
  As a developer
  I want to make sure that content was properly migrated

  @migration @linkslist
  Scenario: Ensure that multiple link lists on the same page are imported
    Given I am logged in as a user with the administrator role
    When I visit "/veterans-programs-health-and-wellness"
    Then I should see 2 ".paragraph--type--list-of-link-teasers" elements

  @migration @linkslist
  Scenario: Ensure that all the links in both lists are imported
    Given I am logged in as a user with the administrator role
    When I visit "/veterans-programs-health-and-wellness"
    Then I should see 10 ".paragraph--type--link-teaser" elements

  @migration @linkslist
  Scenario: Ensure that the link content came through correctly
    Given I am logged in as a user with the administrator role
    When I visit "/veterans-programs-health-and-wellness"
    Then I should see "VA Health and Wellness Programs to Help You Care for Your Body and Mind" in the ".field--name-field-title" element
    And I should see "Nutrition and Food Services" in the ".field--name-field-link" element
    And I should see "Find out how to connect with a VA-registered dietitian nutritionist or get help learning to prepare healthy meals in our Healthy Teaching Kitchens at some VA facilities. You can also access healthy recipes and nutrition information for specific conditions (like cancer, diabetes, and neurological disorders)." in the ".field--name-field-link-summary" element
