Feature: The VA Website is accesible to users.

@api @errors @user
  Scenario: Make sure PIV access is an option
    Given I am at "/user"
    Then I should see "Click here to use Smartcard." in the "#edit-simplesamlphp-auth-login-link" element

