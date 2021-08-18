# CMS-login

More info at https://github.com/department-of-veterans-affairs/va.gov-team-sensitive/blob/master/platform/cms/cms-sso-saml-iam-login-braindump.md

## How to request a CMS user account

### For developers with SOCKS/CAG access but no PIV card

Ask another CMS developer to create a Drupal account with password authentication

### For Editors

Please see the [CMS knowledge base](https://prod.cms.va.gov/help/cms-basics/how-to-request-a-cms-account) for the most current information.

## Technical Details

- SSOi is handled by the `simplesaml_php` module.  It connects existing accounts to the Active Directory user via their email address and user name.
- Once the connection has been established by the the user logging in with their PIV card, the system will update their CMS account with username and email address changes from their Active Directory account.
- Logging in via username and passwords will be turned off once the SSOi system has proven to be reliable.
- Config settings are split to allow debugging data on DEV but not STAGING or PROD.
- Accounts are connected in authmap by VAUID (a number that is specific to a single user.)
- Email addresses are synced at each login for changes with adUPN (the user's email address) and that email is also used to connect existing accounts to initial logins with SSOi.
- Usernames are synced at each login to the adUPN (the user's email address).

## Account Matching

- If the `simplesaml_php` module is able to authenticate the user with the authentication name provided by SSOi, it will log the user in. (See `externalLoginRegister` in the `SimplesamlphpDrupalAuth` service)
- If not, the module will attempt to match the name provided with a Drupal user account name (See `externalRegister` in the `SimplesamlphpDrupalAuth` service)
- If this does not succeed, It will call `hook_simplesamlphp_auth_existing_user` as a last-ditch attempt to match a user. We [implement this hook](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/f4bfe6ce7c226668d715b28ff5ec176ea76827e0/docroot/modules/custom/va_gov_login/va_gov_login.module#L28) in the `va_gov_login` module and attempt to match the user by email address.

## Sample SSOi Response
![Sample simplesaml response](images/ssoi-response.png)
