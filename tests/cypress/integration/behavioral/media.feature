Feature: Media entities

  Scenario: Log in and confirm that field_duration has h:m:s format
    Given I am logged in as a user with the "administrator" role
    And I am at "media/add/video"
    Then I should see "Video duration in Hours:Minutes:Seconds"
    Then I am at "media/video/1278"
    Then the element with selector "input.hms-field" should have attribute "value" matching expression "0:01:45"

  Scenario: Log in and create a document media entity.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "document" media
    
  Scenario: Log in and create an external document media entity.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "document_external" media
        
  Scenario: Log in and create an image media entity.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "image" media
    
  Scenario: Log in and create a video media entity.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "video" media
