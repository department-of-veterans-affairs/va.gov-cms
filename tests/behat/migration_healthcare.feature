@api
Feature: Healthcare Migration
  As a developer
  I want to make sure that content was properly migrated

  @migration @healthcare
  Scenario: Ensure that first url was imported
    Given I am logged in as a user with the administrator role
    When I visit "/disputes"
    Then the "h1" element should contain "Disputes"
    And I should see "Veterans are encouraged to continue working with their VA primary care team to obtain necessary health care services regardless of adverse credit reporting or debt collection activity." in the "Content" region

  @migration @healthcare
  Scenario: Ensure that last url was imported
    Given I am logged in as a user with the administrator role
    When I visit "/apply-va-health-care"
    Then the "h1" element should contain "Apply For VA Health Care"

  @migration @healthcare
  Scenario: Ensure that random Teamsite page was imported
    Given I am logged in as a user with the administrator role
    When I visit "/health-topics-z-index"
    Then the "h1" element should contain "Health Topics A to Z Index"
    And I should see "The items in the Veterans Health A-Z Index (listed above) represent popular topics, frequent inquiries and areas of critical importance to Veterans and their caregivers. This navigational and informational tool is designed to help you quickly find and retrieve specific information. The A-Z Index is structured so that synonyms, acronyms, and cross-referencing provide multiple ways for you to access the topics and features on Veterans Health websites. The index will continue to evolve as additional topics are added." in the "Content" region

  @migration @healthcare
  Scenario: Ensure that random Metalsmith page was imported
    Given I am logged in as a user with the administrator role
    When I visit "/after-you-apply-health-care-benefits"
    Then the "h1" element should contain "After You Apply for Health Care Benefits"
    And I should see "You may have requested a doctor’s appointment when you applied (either in person or on your application)" in the "Content" region
    And I should see "Last updated: October 17, 2018" in the "Content" region


