<?php

namespace Drupal\va_gov_backend;

class Permissions {

  public function __construct() {
    $languages = \Drupal::languageManager()->getLanguages();

    $permissions["va_gov_backend_restrict"] = [
      'title' => t("Restrict language access"),
      'description' => 'Restrict the edit access of nodes',
    ];

    foreach ($languages as $lang) {
      $permissions["va_gov_backend_{$lang->getId()}_allow"] = [
        'title' => t("Allow to edit @lang content", ['@lang' => $lang->getName()]),
      ];
    }

    return $permissions;
  }

}
