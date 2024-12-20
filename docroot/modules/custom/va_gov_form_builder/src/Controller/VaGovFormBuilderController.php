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
   * The active tab in the form builder.
   *
   * @var 'forms'|'content'|'layout'
   */
  private $activeTab;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->drupalFormBuilder = $container->get('form_builder');

    return $instance;
  }

  /**
   * Returns a render array representing the page with the passed-in form.
   *
   * @param string $formName
   *   The filename of the form to be rendered.
   * @param string $nid
   *   The node id, passed in when the form in question edits an existing node.
   */
  private function getFormPage($formName, $nid = NULL) {
    // @phpstan-ignore-next-line
    $form = $this->drupalFormBuilder->getForm('Drupal\va_gov_form_builder\Form\\' . $formName, $nid);

    return [
      '#type' => 'page',
      'content' => $form,
      // Add custom data.
      'form_builder_page_data' => [
        'active_tab' => $this->activeTab,
      ],
      // Add styles.
      '#attached' => [
        'library' => [
          'va_gov_form_builder/va_gov_form_builder_styles',
        ],
      ],
    ];
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
    $this->activeTab = 'forms';
    return $this->getFormPage('Intro');
  }

  /**
   * Start-conversion page.
   */
  public function startConversion() {
    $this->activeTab = 'forms';
    return $this->getFormPage('StartConversion');
  }

  /**
   * Name-and-date-of-birth page.
   */
  public function nameAndDob($nid) {
    $this->activeTab = 'content';
    return $this->getFormPage('NameAndDob', $nid);
  }

}
