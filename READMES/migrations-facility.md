# Migrations: Facilities

1. [Facility Migrations](#facility-migrations)
   1. NCA (National Cemetery Administration) Facilities
   1. VAMC (VA Medical Center) Facilities
      - [VAMC Status](#vamc-status-migration)
      - [System and Facility Health Services](#system-and-facility-health-services)
   1. VBA (Veterans Benefits Administraion) Facilities
   1. Vet Centers
1. [Status Changes to Lighthouse](vamc-facilities.md#status-changes-to-lighthouse)

![Facilities updates and actions](images/VA-facilities.png)

## Facility Migrations
Facility migrations occur nightly and the four types of facilities are updated
with any data from the [Facility API](interfaces.md#facilities-api) including
the creation of new facilities, updating titles, addresses, etc.   The facility
is connected to the facility API by its unique "Facility Locator API ID"
(field_facility_locator_api_id).  These migrations do not handle removing or
deleting any facilities. A facility that needs to be removed, must be deleted by
hand.  The nightly migrations are handled as part of our tasks-periodic.yml and
are triggered by Jenkins.  Revisions for any saves are created and attributed
to the user "CMS Migrator"

  1. NCA (National Cemetery Administration) Facilities - va_node_facility_nca
  1. VAMC (VA Medical Center) Facilities - va_node_health_care_local_facility
  1. VBA (Veterans Benefits Administraion) Facilities - va_node_facility_vba
  1. Vet Centers - va_node_facility_vet_centers

### VAMC Status Migration
VAMC Statuses are updated by a separate migration `va_node_health_care_local_facility_status` that runs every hour. It grabs [multiple CSV sources](../docroot/modules/custom/va_gov_migrate/config/install/migrate_plus.migration.va_node_health_care_local_facility_status.yml) (one per system) which are scraped from TeamSite (hosted by [EWIS](https://github.com/department-of-veterans-affairs/devops/blob/master/docs/External%20Service%20Integrations/EWIS.md)) and updates the fields:
- "Operating status" (`field_operating_status_facility`)
- "Operating status - more info" (`field_operating_status_more_info`)

Changes to operating status also get [pushed to Lighthouse](vamc-facilities.md#status-changes-to-lighthouse).


### System and Facility Health Services
These are created and run as needed as part of the VAMC Upgrade teams effort to get all the services into the CMS.  VAMC upgrade team will provide separate CSVs, one for system health services and one for facility health services.

There are dependencies in the migration that will cause a migrated item to be skipped and create a migration message:
* System Health Service Dependencies
   * National Service Taxonomy term must exist.
   * The System must exist.
* Facility Health Dependencies
   * System Health Service must exist (which is why the system health service migration must be run first)
   * VAMC facility must exist.

A migration should not be considered complete if there are ANY migrate messages logged.


#### Migration Commands
* lando drush migrate:status {migration_id}  - gives the status of the migration. Number of items total, number unprocessed.
* lando drush migrate:reset-status {migration_id}  - Resets a migration that may have not completed due to a fatal error.
* lando drush migrate:rollback {migration_id}  - Rolls back all items tracked by the migration.
* lando drush migrate:messages {migration_id} - List any messages from the last run of the migration. Subsequent runs of the migration will remove ald messages.  When run in the UI, this list only includes any messages from the last batch (which my only be the last 25 things migrated).
*  Useful options
   *  --id-list={ID}  - Limits the command to just a specific row/item in the migration.   Useful for rolling back and re-migrating a specific troublesome item. The ID is the row ID based on what unique ID is defined in the migration.
   *  --limit={quantity}  - limits the action to a specific number of items.

###  Workflow
  1. Edit or create a new migration configuration in docroot/modules/custom/va_gov_migrate/config/install
  2. Use `lando migrate-sync` to copy the config into config/sync imported it and export it again.  Always work in `va_gov_migrate/config/install` and then sync.  This preserves any comments in the migration yml.
  3. Preflight any new data from the VAMC upgrade team in libre office by importing the CSV with the import config set to separator: ; and encapsulation: '.  Look for jumped columns missing data or the appearance of wrapping quotes.
  4. Append the data to an existing CSV or add it as new (if you are dealing with thousands, add it as new.)  Name the file as a match to the id of the migration you are creating.
  5.  Run the System Health Service migration.  Look for presence of migrate messages.  The messages will indicate the problems with the data.  Fix, rollback, repeat until there are no messages created and the row count of the data, matches the created count.
  6. Run the Facility Health Service migration.  Look for messages. The messages will indicate the problems with the data.  Fix, rollback, repeat until there are no messages created and the row count of the data, matches the created count.



[Table of Contents](../README.md)
