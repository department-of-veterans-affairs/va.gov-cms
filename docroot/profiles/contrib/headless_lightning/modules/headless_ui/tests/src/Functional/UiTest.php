<?php

namespace Drupal\Tests\headless_ui\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * @group headless
 * @group headless_ui
 * @group foo
 */
class UiTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning_headless';

  public function test() {
    $assert = $this->assertSession();

    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $node_type = $this->drupalCreateContentType();
    $this->assertSame(DRUPAL_DISABLED, $node_type->getPreviewMode());
    $this->assertFalse($node_type->displaySubmitted());

    $this->drupalGet('/admin/structure/types/add');
    $assert->statusCodeEquals(200);
    $assert->fieldNotExists('display_submitted');
    $assert->fieldNotExists('options[promote]');
    $assert->fieldNotExists('options[sticky]');
    $assert->fieldNotExists('preview_mode');

    $this->drupalGet('/node/add/page');
    $assert->statusCodeEquals(200);
    $assert->pageTextNotContains('Promotion options');

    $this->assertNoManageDisplayLink(
      Url::fromRoute('entity.node_type.collection')
    );
    $this->assertNoManageDisplayLink(
      Url::fromRoute('entity.media_type.collection')
    );
    $this->assertNoManageDisplayLink('/admin/config/people/accounts');
    $this->assertNoManageDisplayLinks('node_type');
    $this->assertNoManageDisplayLinks('media_type');
  }

  protected function assertNoManageDisplayLinks($entity_type) {
    /** @var \Drupal\Core\Entity\EntityInterface[] $entities */
    $entities = $this->container
      ->get('entity_type.manager')
      ->getStorage($entity_type)
      ->loadMultiple();

    foreach ($entities as $entity) {
      $this->assertNoManageDisplayLink($entity->toUrl('edit-form'));
    }
  }

  protected function assertNoManageDisplayLink($path) {
    $assert = $this->assertSession();

    $this->drupalGet($path);
    $assert->statusCodeEquals(200);
    $assert->linkNotExists('Manage display');
  }

}
