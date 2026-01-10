# R&S Tag Migration Framework

This module provides a migration framework for migrating taxonomy tags on Resources & Support (R&S) content types.

## Overview

The migration framework supports multiple use cases for migrating taxonomy tags between different taxonomies and fields while preserving existing data.

## Available Migrations

### 1. Publication: R&S Categories → Outreach Hub Taxonomy

**Migration Class:** `PublicationRsCategoriesToOutreachHubMigration`

**Description:** Migrates tags from R&S Categories taxonomy (`field_lc_categories`) to Outreach Hub taxonomy (`field_outreach_materials_topics`) for Publication content type (`outreach_asset`).

**Use Case:** A Publication content type that is tagged with a tag in the R&S Categories Taxonomy should now be tagged with the same tag in the new Outreach Hub taxonomy.

**Requirements:**
- Source field: `field_lc_categories` (R&S Categories taxonomy)
- Destination field: `field_outreach_materials_topics` (Outreach Hub taxonomy)
- The destination field must exist on the `outreach_asset` content type before running this migration.

### 2. R&S Content: Add "All Veterans" when Veteran Subtype Exists

**Migration Class:** `RsAddAllVeteransMigration`

**Description:** For R&S content types with Audience & Topics paragraphs, if a specific Veteran subtype is tagged in Audience - Beneficiaries, also add "All Veterans".

**Use Case:** An R&S content type is tagged with a specific Veteran subtype in Audience - Beneficiaries Taxonomy and should now also be tagged "All Veterans".

**Requirements:**
- Content types: checklist, faq_multiple_q_a, media_list_images, support_resources_detail_page, q_a, step_by_step, media_list_videos
- Field: `field_audience_topics` (Audience & Topics paragraphs)
- Paragraph field: `field_audience_beneficiares` (Audience - Beneficiaries taxonomy)
- The "All Veterans" term must exist in the `audience_beneficiaries` taxonomy.

### 3. R&S Content: Add "Records" in Topics when in Categories

**Migration Class:** `RsRecordsToTopicsMigration`

**Description:** For R&S content types, if tagged with "Records" in R&S Categories taxonomy, also add "Records" in Topics taxonomy.

**Use Case:** An R&S content type is tagged with "Records" in the R&S Categories Taxonomy and should now also be tagged with "Records" in the Topics Taxonomy.

**Requirements:**
- Content types: checklist, faq_multiple_q_a, media_list_images, support_resources_detail_page, q_a, step_by_step, media_list_videos
- Source field: `field_primary_category` (R&S Categories taxonomy)
- Field: `field_audience_topics` (Audience & Topics paragraphs)
- Paragraph field: `field_topics` (Topics taxonomy)
- The "Records" term must exist in both `lc_categories` and `topics` taxonomies.

### 4. CLP and VA Benefits Taxonomy Migrations

**Migration Class:** `ClpVaBenefitsMigration`

**Description:** Placeholder migration for CLP content type and VA Benefits taxonomy migrations.

**Status:** Placeholder - specific use cases to be implemented.

## Usage

Migrations use the `codit_batch_operations` module, which provides built-in batching, logging, and Drush integration.

### Run a Specific Migration

```bash
drush codit-batch-operations:run <MigrationClassName>
```

Examples:
```bash
drush codit-batch-operations:run PublicationRsCategoriesToOutreachHubMigration
drush codit-batch-operations:run RsAddAllVeteransMigration
drush codit-batch-operations:run RsRecordsToTopicsMigration
drush codit-batch-operations:run ClpVaBenefitsMigration
```

### List All Available Batch Operations

```bash
drush codit-batch-operations:list
```

## Migration Behavior

- **Additive:** Migrations add tags to destination fields without removing existing tags.
- **Revision Creation:** All changes create new node revisions with the CMS Migrator user (UID: 1317) using `save_node_revision()` from script-library.php.
- **Moderation State:** Migrations preserve the current moderation state of nodes.
- **Error Handling:** Migrations log errors and warnings but continue processing remaining nodes.
- **Cardinality:** Migrations respect field cardinality limits and log warnings if limits are exceeded.
- **Batching:** Migrations use codit_batch_operations for automatic batching and progress tracking.

## Logging

Migration activities are logged through multiple channels:
- **Batch Operations Log:** Built-in logging via `codit_batch_operations` module
- **Drupal Watchdog:** Service-level logging to the `va_gov_resources_and_support` logger channel
- Check logs at `/admin/reports/dblog` (Recent log messages)

## Architecture

### Migration Classes

All migration classes extend `BatchOperations` and implement `BatchScriptInterface` from the `codit_batch_operations` module. They are located in `va_gov_batch/src/cbo_scripts/` to be discovered by the batch operations system:

- **PublicationRsCategoriesToOutreachHubMigration:** Use case 1 implementation.
- **RsAddAllVeteransMigration:** Use case 2 implementation.
- **RsRecordsToTopicsMigration:** Use case 3 implementation.
- **ClpVaBenefitsMigration:** Use case 4 placeholder.

Each migration class implements:
- `getTitle()`: Human-readable title
- `getDescription()`: Detailed description
- `getCompletedMessage()`: Message template for completion
- `getItemType()`: Type of items being processed
- `gatherItemsToProcess()`: Collects nodes to process
- `processOne()`: Processes a single node

**Note:** The scripts are located in `va_gov_batch/src/cbo_scripts/` because the `codit_batch_operations` module is configured to look for scripts in the `va_gov_batch` module (see `config/sync/codit_batch_operations.settings.yml`).

## Pre-Migration Checklist

Before running migrations:

1. **Verify Field Existence:** Ensure all required fields exist on the target content types.
2. **Verify Taxonomy Terms:** Ensure all required terms exist in the target taxonomies.
3. **Backup:** Consider backing up the database before running migrations on production.
4. **Test Environment:** Test migrations on a development/staging environment first.

## Post-Migration Verification

After running migrations:

1. Check migration output for success/failure counts.
2. Review log messages at `/admin/reports/dblog`.
3. Spot-check a sample of migrated nodes to verify tags were added correctly.
4. Verify that node revisions were created with appropriate log messages.

## Notes

- Migrations preserve existing tagging data - they only add new tags, never remove existing ones.
- Moderation state is preserved during migration.

