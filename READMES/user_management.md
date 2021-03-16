# CMS User Management

## Login Issues

### Account temporarily blocked

If a user makes too many attempts to log in with an incorrect password in a short time, their account will be temporarily blocked. The following message will be displayed:

> There have been more than 5 failed login attempts for this account. It is temporarily blocked. Try again later or request a new password.

To resolve this issue:
1. Log in as an administrator.
1. Visit **Home > Administration > People > Flood Unblock**.
1. Check the box in each row next to the user's username/email address.
1. Click the `Remove selected items from the flood table` button.

## Bulk User Import Procedure

To import users:

1. go to [the import page](https://prod.cms.va.gov/migrate_source_ui)
1. Select "User Import (supports csv)" from the **Migrations** dropdown
1. Upload a CSV file in the following format:
```
email,roles,sections
test_user@va.gov,"content_editor,content_admin",""
another_user@va.gov,"content_editor,content_publisher","VA Wilmington health care, VA Coatesville health care"
```

Notes:
- The email address will be used for the user's name.
- The role column uses role machine names - to find them, go to [the roles page](https://prod.cms.va.gov/admin/people/roles) and click the **Edit** link for a role. The machine name will be listed next to the Role name.
- To find section names, go to [the sections page](https://prod.cms.va.gov/admin/structure/taxonomy/manage/administration/overview). If a given section name can not be found or matches more than one section, a message will be displayed, and you'll need to fix the CSV or manually add the section.
- Only email addresses ending in `@va.gov` will be imported
- If importing on BRD, (prod/staging/dev) all accounts will have SSO enabled and be blocked by default.
- If not importing on BRD, all accounts will be enabled and have their password set to `drupal8`.
