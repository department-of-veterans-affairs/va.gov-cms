<?php

namespace Drupal\va_gov_form_builder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the VA Form Builder experience.
 *
 * Note:
 *
 * Drupal also has a something called "form builder"
 * that is utilized in this controller (`drupalFormBuilder`).
 * This `FormBuilder` service is responsible for building and
 * processing forms in Drupal, and is called here so the controller can
 * return the form that should be rendered for the given route.
 *
 * The naming can be confusing, so it's important to remember that
 * `VaGovFormBuilder` is the tool built by this module (`va_gov_form_builder`),
 * and used for building Veteran-facing VA forms, while Drupal's
 * `FormBuilder` (service) is responsible for building and processing
 * forms _within_ Drupal.
 */
class VaGovFormBuilderController extends ControllerBase {

  /**
   * The Drupal form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  private $drupalFormBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->drupalFormBuilder = $container->get('form_builder');

    return $instance;
  }

  /**
   * Entry point for the VA Form Builder. Redirects to the intro page.
   */
  public function entry() {
    return $this->redirect('va_gov_form_builder.intro');
  }

  /**
   * Intro page.
   */
  public function intro() {
    return $this->drupalFormBuilder->getForm('Drupal\va_gov_form_builder\Form\Intro');
  }

  /**
   * Start-conversion page.
   */
  public function startConversion() {
    return $this->drupalFormBuilder->getForm('Drupal\va_gov_form_builder\Form\StartConversion');
  }

  /**
   * Name-and-date-of-birth page.
   */
  public function nameAndDob($nid) {
    // @phpstan-ignore-next-line
    return $this->drupalFormBuilder->getForm('Drupal\va_gov_form_builder\Form\NameAndDob', $nid);
  }

}
