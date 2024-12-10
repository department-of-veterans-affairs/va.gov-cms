Feature: Media entities

  Scenario: Log in and confirm that field_duration has h:m:s format
    Given I am logged in as a user with the "administrator" role
    And I am at "media/add/video"
    Then I should see "Video duration in Hours:Minutes:Seconds"
    Then I am at "media/video/1278"
    Then the element with selector "input.hms-field" should have attribute "value" matching expression "0:01:45"

  @critical_path
  Scenario: Log in and create a document media entity.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "document" media

  @critical_path
  Scenario: Log in and create an external document media entity.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "document_external" media

  @critical_path
  Scenario: Log in and create an image media entity.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "image" media

  @critical_path
  Scenario: Log in and create a video media entity.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "video" media

  @critical_path
  Scenario: Log in and create a document media entity, a knowledge base article that refers to it, and verify a downloadable link is created.
    Given I am logged in as a user with the "content_admin" role
    When I create a "document" media
    And I create a "documentation_page" node and continue
    And I add a main content block with a link to a "Document" file
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I save the node
    Then I should see a "document" downloadable file link

  @critical_path
  Scenario: Log in and create an image media entity, a knowledge base page that refers to it, and verify a downloadable link is created.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "image" media
    And I create a "documentation_page" node and continue
    And I add a main content block with a link to a "Image" file
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I save the node
    Then I should see a "image" downloadable file link

  @critical_path
  Scenario: Log in and create a video media entity, a knowledge base page that refers to it, and verify a link is created.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "video" media
    And I create a "documentation_page" node and continue
    And I add a main content block with a link to a "Video" file
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I save the node
    Then I should see a "video" file link
