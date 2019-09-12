Feature: The VA Website is accesible to users.

@api @errors @user
  Scenario: Make sure PIV access is an option
    Given I am at "/user"
    Then I should see "Login with PIV or other Smartcard." in the "#edit-simplesamlphp-auth-login-link" element

