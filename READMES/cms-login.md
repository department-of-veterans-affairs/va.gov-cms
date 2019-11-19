# CMS-login

## The CMS uses A SimpleSAML connection to VA's Active Directory:

How to request a user account:
1. Send an email to vacmssupport@va.gov, cc'ing your VA Product Owner and including your:
1.1 VA Email Address associated with your PIV card
1.2 Requested Role (editor, reviewer, publisher)
2. A user administrator will verify the request is approved with the PO via email 
3. Once approved, the user administrator will create your account and assign the requested role(s) and you will receive an email with your username and temporary password
4. You may then log into Drupal (cms.va.gov) while on the VA Network only using either your PIV or username/password

## Technical Details
  * SSOi is handled by the simplesaml_php module.  It connects existing accounts to the Active Directory user via their email address and user name.
  * Once the connection has been established by the the user logging in with their PIV card, the system will update their CMS account with username and email address changes from their Active Directory account.
  * Logging in via username and passwords will be turned off once the SSOi system has proven to be reliable.
  * Config settings are split to allow debugging data on DEV but not STAGING or PROD.
  * Accounts are connect in authmap by VAUID (a number that is specific to a single user.)
  * Email addresses are synced at each login for changes with adUPN (the user's email address) and that email is also used to connect existing accounts to initial logins with SSOi.
  * Usernames are synced at each login to the adUPN (the user's email address).

##Sample SSOi Response
![Sample simplesaml response](images/ssoi-response.png)
