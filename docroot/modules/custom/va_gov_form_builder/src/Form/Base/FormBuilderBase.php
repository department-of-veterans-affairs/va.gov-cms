<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract base class for Form Builder steps.
 */
abstract class FormBuilderBase extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Digital Forms service.
   *
   * @var \Drupal\va_gov_form_builder\Service\DigitalFormsService
   */
  protected $digitalFormsService;

  /**
   * Flag indicating whether this form is in "create" mode.
   *
   * @var bool
   */
  protected $isCreate;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, DigitalFormsService $digitalFormsService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->digitalFormsService = $digitalFormsService;
    $this->isCreate = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('va_gov_form_builder.digital_forms_service')
    );
  }

}
