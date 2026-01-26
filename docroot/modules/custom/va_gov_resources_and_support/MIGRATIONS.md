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
- Field: `field_tags` (Audience & Topics paragraphs)
- Paragraph field: `field_audience_beneficiares` (Audience - Beneficiaries taxonomy)
- The "All Veterans" term must exist in the `audience_beneficiaries` taxonomy.

### 3. R&S Content: Add "Records" in Topics when in Categories

**Migration Class:** `RsRecordsToTopicsMigration`

**Description:** For R&S content types, if tagged with "Records" in R&S Categories taxonomy, also add "Records" in Topics taxonomy.

**Use Case:** An R&S content type is tagged with "Records" in the R&S Categories Taxonomy and should now also be tagged with "Records" in the Topics Taxonomy.

**Requirements:**
- Content types: checklist, faq_multiple_q_a, media_list_images, support_resources_detail_page, q_a, step_by_step, media_list_videos
- Source field: `field_primary_category` (R&S Categories taxonomy)
- Field: `field_tags` (Audience & Topics paragraphs)
- Paragraph field: `field_topics` (Topics taxonomy)
- The "Records" term must exist in both `lc_categories` and `topics` taxonomies.

### 4. Campaign Landing Pages: Add "All Veterans" when Veteran Subtype Exists

**Migration Class:** `ClpVaBenefitsMigration`

**Description:** For Campaign Landing Page content types, if tagged with a specific Veteran subtype in the Select Audience field, also add "All Veterans".

**Use Case:** A Campaign Landing Page is tagged with a specific Veteran subtype in Select Audience (Beneficiaries Taxonomy) and should now also be tagged "All Veterans".

**Requirements:**
- Content type: `campaign_landing_page`
- Field: `field_clp_audience` (Audience - Beneficiaries taxonomy)
- The "All Veterans" term must exist in the `audience_beneficiaries` taxonomy.

### 5. VA Benefits Taxonomy: Add "All Veterans" when Veteran Subtype Exists

**Migration Class:** `VaBenefitsTaxonomyMigration`

**Description:** For VA Benefits taxonomy terms, if tagged with a specific Veteran subtype in the Beneficiaries field, also add "All Veterans".

**Use Case:** A VA Benefits taxonomy term is tagged with a specific Veteran subtype in Beneficiaries (Audience - Beneficiaries Taxonomy) and should now also be tagged "All Veterans".

**Requirements:**
- Entity type: Taxonomy term
- Vocabulary: `va_benefits_taxonomy`
- Field: `field_va_benefit_beneficiaries` (Audience - Beneficiaries taxonomy)
- The "All Veterans" term must exist in the `audience_beneficiaries` taxonomy.

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
drush codit-batch-operations:run VaBenefitsTaxonomyMigration
```

### List All Available Batch Operations

```bash
drush codit-batch-operations:list
```

## Migration Behavior

- **Additive:** Migrations add tags to destination fields without removing existing tags.
- **Revision Creation:** All changes create new entity revisions with the CMS Migrator user (UID: 1317). Nodes use `save_node_revision()` from script-library.php; taxonomy terms create revisions directly.
- **Forward Revisions:** Node migrations automatically process forward (draft) revisions to ensure changes are preserved when published.
- **Moderation State:** Migrations preserve the current moderation state of nodes.
- **Error Handling:** Migrations log errors and warnings but continue processing remaining entities.
- **Cardinality:** Migrations respect field cardinality limits and log warnings if limits are exceeded.
- **Batching:** Migrations use codit_batch_operations for automatic batching and progress tracking.
- **Idempotent:** Migrations can be run multiple times safely - they check for existing tags before adding.

## Logging

Migration activities are logged through multiple channels:
- **Batch Operations Log:** Built-in logging via `codit_batch_operations` module
- **Drupal Watchdog:** Service-level logging to the `va_gov_resources_and_support` logger channel
- Check logs at `/admin/reports/dblog` (Recent log messages)

## Architecture

### Base Class

All migration classes extend `BaseRsTagMigration`, which extends `BatchOperations` and implements `BatchScriptInterface` from the `codit_batch_operations` module. The base class provides:

- Common constants (vocabulary IDs, field names)
- Shared helper methods (`getTermsByVocabulary()`, `addTermsToNodeField()`, etc.)
- Standard node processing workflow with forward revision handling
- Common logging and error handling patterns

### Migration Classes

All migration classes are located in `va_gov_batch/src/cbo_scripts/` to be discovered by the batch operations system:

- **PublicationRsCategoriesToOutreachHubMigration:** Migrates R&S Categories to Outreach Hub taxonomy for Publication content type.
- **RsAddAllVeteransMigration:** Adds "All Veterans" to R&S content types when Veteran subtypes exist.
- **RsRecordsToTopicsMigration:** Adds "Records" in Topics when present in R&S Categories.
- **ClpVaBenefitsMigration:** Adds "All Veterans" to Campaign Landing Pages when Veteran subtypes exist.
- **VaBenefitsTaxonomyMigration:** Adds "All Veterans" to VA Benefits taxonomy terms when Veteran subtypes exist.

Each migration class implements:
- `getTitle()`: Human-readable title
- `getDescription()`: Detailed description
- `getCompletedMessage()`: Message template for completion
- `getItemType()`: Type of items being processed
- `gatherItemsToProcess()`: Collects entities to process
- `processOne()` or `processNode()`: Processes a single entity

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
3. Spot-check a sample of migrated entities (nodes or taxonomy terms) to verify tags were added correctly.
4. Verify that entity revisions were created with appropriate log messages.
5. For node migrations, verify that forward revisions were also updated.
6. Confirm that original tags were preserved (migrations are additive, not replacing).

## Migration Groups

### Story 1: Outreach Materials goes solo
- **PublicationRsCategoriesToOutreachHubMigration:** Migrates R&S Categories to Outreach Hub taxonomy

### Story 2: Simplifying Veteran tags
- **RsAddAllVeteransMigration:** Adds "All Veterans" to R&S content types
- **ClpVaBenefitsMigration:** Adds "All Veterans" to Campaign Landing Pages
- **VaBenefitsTaxonomyMigration:** Adds "All Veterans" to VA Benefits taxonomy terms

### Other Migrations
- **RsRecordsToTopicsMigration:** Adds "Records" in Topics when present in R&S Categories

## Notes

- Migrations preserve existing tagging data - they only add new tags, never remove existing ones.
- Moderation state is preserved during migration.
- All Story 2 migrations are independent and can be run in any order.
- Migrations that process nodes automatically handle forward revisions to prevent data loss.

