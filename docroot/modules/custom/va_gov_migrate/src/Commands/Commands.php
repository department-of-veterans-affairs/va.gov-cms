<?php

namespace Drupal\va_gov_migrate\Commands;

use Drush\Commands\DrushCommands;
use Drupal\va_gov_migrate\Service\VaGovMigrateService;

/**
 * Drush commands related to migrations.
 */
class Commands extends DrushCommands {

  /**
   * The VA gov Migrate Service.
   *
   * @var \Drupal\va_gov_migrate\Service\VaGovMigrateService
   */
  protected $vaGovMigrateService;

  /**
   * Constructor for this set of drush commands.
   *
   * @param \Drupal\va_gov_migrate\Service\VaGovMigrateService $va_gov_migrate_service
   *   VA gov VaGovMigrateService service.
   */
  public function __construct(
    VaGovMigrateService $va_gov_migrate_service,
  ) {
    parent::__construct();
    $this->vaGovMigrateService = $va_gov_migrate_service;
  }

  /**
   * Clean up bad node revisions.
   *
   * @command va:gov-clean-revs
   * @aliases vg-cr,va-gov-clean-revs
   */
  public function cleanRevs() {
    $messages = $this->vaGovMigrateService->cleanRevs();
    $this->logger->log('success', $messages['success']);
    $this->logger->warning($messages['warning']);
  }

  /**
   * Archive IntranetOnly forms in the CMS.
   *
   * @command va_gov_migrate:archive-intranet-only-forms
   * @aliases va-gov-archive-intranet-only-forms
   */
  public function archiveIntranetOnlyForms() {
    $this->vaGovMigrateService->archiveIntranetOnlyForms();
  }

  /**
   * Flag any facilities that no longer exist in Facilty API.
   *
   * @command va_gov_migrate:flag-missing-facilities
   * @aliases va-gov-flag-missing-facilities
   */
  public function flagMissingFacilities() {
    $this->vaGovMigrateService->flagMissingFacilities();
  }

}
