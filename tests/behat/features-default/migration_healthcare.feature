@api
Feature: Healthcare Migration
  As a developer
  I want to make sure that content was properly migrated

  @migration @healthcare
  Scenario: Ensure that an early url was imported
    Given I am logged in as a user with the administrator role
    When I visit "/champva-benefits"
    Then the "h1" element should contain "CHAMPVA Benefits"
    And I should see "How do I get CHAMPVA benefits?" in the "Content" region

  @migration @healthcare
  Scenario: Ensure that last url was imported
    Given I am logged in as a user with the administrator role
    When I visit "/veterans-programs-health-and-wellness"
    Then the "h1" element should contain "Veterans Programs For Health And Wellness"
    And I should see "Our Veterans programs for health and wellness offer information, resources, and treatment options to help you stay healthy." in the "Content" region

  @migration @healthcare
  Scenario: Ensure that random page was imported
    Given I am logged in as a user with the administrator role
    When I visit "/after-you-apply-health-care-benefits"
    Then the "h1" element should contain "After You Apply for Health Care Benefits"
    And I should see "You may have requested a doctor’s appointment when you applied (either in person or on your application)" in the "Content" region
    And I should see "Last updated: October 17, 2018" in the "Content" region
