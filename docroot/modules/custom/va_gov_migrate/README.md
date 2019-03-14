#VA Migration

##VA.gov Migrations
The page migrations pull content directly from the vagov-content github repo and the VA.gov website. But menu migrations
get their data from json files kindly provided by AdHoc and stored in `va_gov_migrate/data`. 

That means the new page content gets added/updated automatically
during the migration, but if a menu changes, we'll need to add the new json file to the repo in order for the changes to take 
effect when the migration is run.   

**IMPORTANT:** Running the migrations as recommended below (with `Update` checked) adds any new pages and updates existing pages. 
It does not delete any existing content, even if that content no longer exists in the migration source. To makes sure the migrated content 
doesn't include any pages that were removed from the site, first run `Rollback` on the migration, to delete previously migrated 
content, then run the migration.

To run VA.gov migrations:
1. Go to `/admin/structure/migrate/`.
2. Choose `Migrate from the VA.gov website` (The page will take a little while to load. That's normal.)

###Health Care benefits pages
 
1. Click the `Execute` button next to `Migrate Health Care benefits pages from VA.gov`.
2. Check the `Update` checkbox.
3. Click `Execute`. 

    This will first run `Migrate alert blocks from VA.gov` then the page migration.

###Health Care landing pages
 
1. Click the `Execute` button next to `Migrate all landing pages from VA.gov`.
2. Check the `Update` checkbox.
3. Click `Execute`. 

    This will first run `Migrate promo block images from VA.gov`, `Create media entities from promo block images`, `Migrate promo blocks from VA.gov` `Migrate 
    alert blocks from VA.gov`, and `Migrate support services from VA.gov`, then the page migration.

###Main menu
The main menu already contains testing data, so before migrating the main menu for the 
first time, you need to remove all of the existing menu items. You can do that quickly by going to `/admin/config/structure/va_gov_migrate/truncate-menu`
and clicking the `Remove All Menu Links From Main Menu` button. 

1. Click the `Execute` button next to `Migrate VA.gov main menu`.
2. Check the `Update` checkbox.
3. Click `Execute`. 

###Health Care sidebar menu

1. Click the `Execute` button next to `Migrate health care sidebar menu`.
2. Check the `Update` checkbox.
3. Click `Execute`. 

##Outreach asset migrations

The outreach asset migrations can't run their dependencies automatically the way pages can
so each one has to be run individually and in numerical order (all the 1's first, then the
2's, etc). This migration pulls some files and information from the live site, but the list
of pages to migrate and some information about them comes from a CSV file in `va_gov_migrate/data`.

To run asset migrations:
1. Go to `/admin/structure/migrate/`.
2. Choose `Outreach asset migrations`.
3. Execute every migration listed, in numerical order.
