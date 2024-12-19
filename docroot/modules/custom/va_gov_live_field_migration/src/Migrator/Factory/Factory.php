<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Factory;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_live_field_migration\Database\DatabaseInterface;
use Drupal\va_gov_live_field_migration\FieldPurger\FieldPurgerInterface;
use Drupal\va_gov_live_field_migration\Migrator\MigratorInterface;
use Drupal\va_gov_live_field_migration\Migrator\StringToStringLongMigrator;
use Drupal\va_gov_live_field_migration\Migrator\TextToStringLongMigrator;
use Drupal\va_gov_live_field_migration\Reporter\ReporterInterface;
use Drupal\va_gov_live_field_migration\State\StateInterface;

/**
 * Factory for Migrator objects.
 */
class Factory implements FactoryInterface {

  use StringTranslationTrait;

  /**
   * The reporter service.
   *
   * @var \Drupal\va_gov_live_field_migration\Reporter\ReporterInterface
   */
  protected $reporter;

  /**
   * The migration state service.
   *
   * @var \Drupal\va_gov_live_field_migration\State\StateInterface
   */
  protected $state;

  /**
   * The database service.
   *
   * @var \Drupal\va_gov_live_field_migration\Database\DatabaseInterface
   */
  protected $database;

  /**
   * The entity display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type repository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  protected $entityTypeRepository;

  /**
   * The field purger service.
   *
   * @var \Drupal\va_gov_live_field_migration\FieldPurger\FieldPurgerInterface
   */
  protected $fieldPurger;

  /**
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    TranslationInterface $stringTranslation,
    ReporterInterface $reporter,
    StateInterface $state,
    DatabaseInterface $database,
    EntityDisplayRepositoryInterface $entityDisplayRepository,
    EntityFieldManagerInterface $entityFieldManager,
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeRepositoryInterface $entityTypeRepository,
    FieldPurgerInterface $fieldPurger
  ) {
    $this->stringTranslation = $stringTranslation;
    $this->reporter = $reporter;
    $this->state = $state;
    $this->database = $database;
    $this->entityDisplayRepository = $entityDisplayRepository;
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeRepository = $entityTypeRepository;
    $this->fieldPurger = $fieldPurger;
  }

  /**
   * {@inheritDoc}
   */
  protected function getReporter(): ReporterInterface {
    return $this->reporter;
  }

  /**
   * {@inheritDoc}
   */
  protected function getState(): StateInterface {
    return $this->state;
  }

  /**
   * {@inheritDoc}
   */
  protected function getDatabase(): DatabaseInterface {
    return $this->database;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityDisplayRepository(): EntityDisplayRepositoryInterface {
    return $this->entityDisplayRepository;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityFieldManager(): EntityFieldManagerInterface {
    return $this->entityFieldManager;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityTypeManager(): EntityTypeManagerInterface {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityTypeRepository(): EntityTypeRepositoryInterface {
    return $this->entityTypeRepository;
  }

  /**
   * {@inheritDoc}
   */
  public function getStringToStringLongMigrator(string $entityType, string $fieldName): MigratorInterface {
    return new StringToStringLongMigrator(
      $this->reporter,
      $this->state,
      $this->database,
      $this->stringTranslation,
      $this->entityDisplayRepository,
      $this->entityFieldManager,
      $this->entityTypeManager,
      $this->entityTypeRepository,
      $this->fieldPurger,
      $entityType,
      $fieldName
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getTextToStringLongMigrator(string $entityType, string $fieldName): MigratorInterface {
    return new TextToStringLongMigrator(
      $this->reporter,
      $this->state,
      $this->database,
      $this->stringTranslation,
      $this->entityDisplayRepository,
      $this->entityFieldManager,
      $this->entityTypeManager,
      $this->entityTypeRepository,
      $this->fieldPurger,
      $entityType,
      $fieldName
    );
  }

}
