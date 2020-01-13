Feature: The VA Website is accessible to users.

@api @errors @user
  Scenario: The homepage is the log in form and the site title is as intended.
    Given I am on the homepage
    Then I should see "Log in"
    And I should see "VA.gov CMS" in the "title" element
    And I should see "Login with PIV or other Smartcard." in the "#edit-simplesamlphp-auth-login-link" element
    Then print current URL
