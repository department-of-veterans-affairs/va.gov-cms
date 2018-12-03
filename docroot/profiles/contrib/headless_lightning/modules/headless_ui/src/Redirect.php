<?php

namespace Drupal\headless_ui;

use Doctrine\Common\Inflector\Inflector;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class Redirect {

  public static function entityForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();

    $redirect = [
      static::class,
      Inflector::camelize($form_object->getBaseFormId()),
    ];

    if (is_callable($redirect)) {
      static::applyHandler($form['actions'], $redirect);
    }
  }

  /**
   * @TODO Make this public in \Drupal\lightning\FormHelper.
   */
  protected static function applyHandler(array &$actions, callable $handler, $handler_type = '#submit') {
    foreach (Element::children($actions) as $key) {
      if (isset($actions[$key][$handler_type])) {
        $actions[$key][$handler_type][] = $handler;
      }
    }
  }

  public static function nodeForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('view.content.page_1');
  }

  public static function mediaForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('view.media.media_page_list');
  }

  public static function userForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('view.user_admin_people.page_1');
  }

}
