Feature: The VA Website is accessible to users.

@api @errors @user
  Scenario: The media admin content page should not present a Grid view.
    Given I am logged in as a user with the "content_admin" role
    And I am on "/admin/content/media"
    Then the xpath '//a[@data-drupal-link-system-path="admin/content/media-grid"]' should have no matches
