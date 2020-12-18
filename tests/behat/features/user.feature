Feature: The VA Website is accessible to users.

@api @errors @user
  Scenario: The homepage is the log in form and the site title is as intended.
    Given I am on the homepage
    Then I should see "Log in"
    And I should see "Veterans Affairs" in the "title" element
    And I should see "Login with PIV or other Smartcard." in the "#edit-simplesamlphp-auth-login-link" element
    Then print current URL

@api @errors @user
  Scenario: The media admin content page should not present a Grid view.
    Given I am logged in as a user with the "content_admin" role
    And I am on "/admin/content/media"
    Then the xpath '//a[@data-drupal-link-system-path="admin/content/media-grid"]' should have no matches
