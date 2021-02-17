 Feature: CMS Users are able to manually crop images
  In order to control the presentation of images
  As anyone involved in the project
  I need to be able to manually crop images

  @image_crop
  Scenario: Manually cropping an image should lead to correctly cropped derivatives
    Given I am logged in as a user with the "content_admin" role
    And I create a "image" media
    Then I should see the what's new in the CMS block

