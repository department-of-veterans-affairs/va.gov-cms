<?php

namespace Drupal\va_gov_consumers;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Provides a form alter builder for VA Consumers module.
 *
 * This class implements the TrustedCallbackInterface to ensure that
 * form alter callbacks are trusted and can be invoked securely.
 * It is responsible for modifying or extending Drupal forms related
 * to VA Consumers functionality.
 *
 * @package Drupal\va_gov_consumers
 */
class VaConsumersFormAlterBuilder implements TrustedCallbackInterface {

  /**
   * Returns an array of trusted callback method names.
   *
   * This method is used by the Form API to specify which methods in this class
   * are considered safe to be called.
   *
   * @return string[]
   *   An array of method names that are trusted callbacks.
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

  /**
   * Pre-render callback for altering forms.
   *
   * This static method is intended to be used as a pre-render callback
   * for Drupal forms. It allows for modifications to the form array
   * before it is rendered.
   *
   * @param array $form
   *   The form array to be altered.
   * @param string $form_id
   *   The unique string identifying the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function preRender($form, $form_id, $form_state) {
    _va_gov_consumers_modify_va_form_fields($form, $form_id, $form_state);
    _va_gov_consumers_modify_vamc_system_related_fields($form, $form_id, $form_state);
    _va_gov_consumers_modify_facility_fields($form, $form_id, $form_state);
    _va_gov_consumers_disable_vha_api_form_fields($form, $form_id);
    _va_gov_consumers_disable_non_vha_facilities_api_form_fields($form, $form_id);
  }

}
