@file_upload
Feature: Files can be uploaded and attached to nodes successfully
  In order to reliably and predictably create content
  As anybody involved in the project
  I need to be able to upload and attach files to nodes.

  Scenario: We should be able to attach files to event nodes.
    Given I am logged in as a user with the "content_admin" role
    When I create a "event" node
    Then I should see "polygon_image.png"
    And I should see an image with the selector 'div.field--name-image a img'
