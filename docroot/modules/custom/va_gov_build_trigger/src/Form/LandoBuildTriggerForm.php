<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements build trigger form overrides for the Lando environment.
 */
class LandoBuildTriggerForm extends TugboatBuildTriggerForm {

  /**
   * Build the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['help_1']['#markup'] = $this->t('Release content to update the front end of this local environment with the latest published content changes.');

    $form['section_1']['selection']['#options']['default'] = $this->t('Use default - the front end version from the time this local environment was created.');

    $target = $this->environmentDiscovery->getWebUrl();
    $target_url = Url::fromUri($target, ['attributes' => ['target' => '_blank']]);
    $target_link = Link::fromTextAndUrl($this->t('front end URL for this local environment'), $target_url);
    $description = $this->t(
      'Once the release is completed successfully, use the @target_link to see how your content will appear to site visitors.',
      ['@target_link' => $target_link->toString()]
    );
    $form['section_3']['environment_target'] = [
      '#type' => 'item',
      '#markup' => $description,
    ];

    return $form;
  }

}
