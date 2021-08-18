# CMS-login

More info at https://github.com/department-of-veterans-affairs/va.gov-team-sensitive/blob/master/platform/cms/cms-sso-saml-iam-login-braindump.md

## How to request a CMS user account

### For developers with SOCKS/CAG access but no PIV card

Ask another CMS developer to create a Drupal account with password authentication

### For Editors

Please see the [CMS knowledge base](https://prod.cms.va.gov/help/cms-basics/how-to-request-a-cms-account) for the most current information.

## Technical Details
  * SSOi is handled by the simplesaml_php module.  It connects existing accounts to the Active Directory user via their email address and user name.
  * Once the connection has been established by the the user logging in with their PIV card, the system will update their CMS account with username and email address changes from their Active Directory account.
  * Logging in via username and passwords will be turned off once the SSOi system has proven to be reliable.
  * Config settings are split to allow debugging data on DEV but not STAGING or PROD.
  * Accounts are connected in authmap by VAUID (a number that is specific to a single user.)
  * Email addresses are synced at each login for changes with adUPN (the user's email address) and that email is also used to connect existing accounts to initial logins with SSOi.
  * Usernames are synced at each login to the adUPN (the user's email address).

## Sample SSOi Response
![Sample simplesaml response](images/ssoi-response.png)
