<?php

namespace Drupal\va_gov_content_release\Form\StubbedForms;

use Drupal\va_gov_content_release\Form\GitForm;

/**
 * A stubbed version of the GitForm class.
 *
 * Submissions of this form will always succeed.
 */
class GitFormSuccess extends GitForm {

  /**
   * {@inheritDoc}
   */
  public function resetFrontendVersion() {
  }

  /**
   * {@inheritDoc}
   */
  public function setFrontendVersion(string $gitRef) {
  }

}
