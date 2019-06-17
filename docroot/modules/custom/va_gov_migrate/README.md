# VA Migration

## VA.gov Migrations
The page migrations pull content directly from the vagov-content github repo and the VA.gov website. But menu migrations
get their data from json files kindly provided by AdHoc and stored in `va_gov_migrate/data`. 

That means the new page content gets added/updated automatically
during the migration, but if a menu changes, we'll need to add the new json file to the repo in order for the changes to take 
effect when the migration is run.

### Error Reporting
Be sure to go to Reports - Recent Log Messages (`/admin/reports/dblog`) after you run a migration to make sure that there were 
no content errors.

To generate CSV reports, go to `/admin/config/structure/va_gov_migrate/config`, check 'Create CSV files', and save.

### Migrating Content

To run VA.gov migrations:
1. Go to `/admin/structure/migrate/`.

#### Benefits pages

1. Choose `Records benefits hub` migration group
2. Make sure the migration labels match the hub(s) you want to migrate.
3. Migrate the detail pages.
    
    - Press the 'Execute' button for the detail migration.
    - Press the 'Execute' button on the next page

4. Migrate the menu.
    
    - Press the 'Execute' button for the menu migration.
    - Press the 'Execute' button on the next page

5. Migrate the landing page.
    - Press the `Execute` button next to the hub migration.
    - Check the `Update` checkbox on the next page.
    - Press `Execute`. 
