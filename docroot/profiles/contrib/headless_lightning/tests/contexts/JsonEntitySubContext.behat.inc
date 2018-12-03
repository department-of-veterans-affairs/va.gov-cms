<?php

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Subcontext for interacting with entities that are rendered as JSON.
 */
class JsonEntitySubContext extends DrupalSubContextBase {

  /**
   * @When I edit the item named :name
   */
  public function iEditTheItemNamed($name) {
    $this->assertSession()
      ->elementExists('named', ['link', "Edit $name"])->click();
  }

}
