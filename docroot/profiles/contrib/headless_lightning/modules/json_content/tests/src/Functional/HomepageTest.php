<?php

namespace Drupal\Tests\headless_ui\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @group headless
 * @group json_content
 */
class HomepageTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning_headless';

  public function test() {
    $assert = $this->assertSession();

    // Anon users see login form on homepage
    $this->drupalGet('<front>');
    $assert->statusCodeEquals(200);
    $form = $assert->elementExists('css', '#user-login-form');
    $assert->fieldExists('Username', $form);
    $assert->fieldExists('Password', $form);

    // Authenticated users without the "access content overview" permission are
    // not redirected from the homepage.
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);
    $this->drupalGet('<front>');
    $assert->pageTextContains('This site has no homepage content');
    $assert->addressEquals('/');

    // Authenticated users with the "access content overview" permission are
    // redirected to the /admin/content page.
    $account = $this->drupalCreateUser(['access content overview']);
    $this->drupalLogin($account);
    $this->drupalGet('<front>');
    $assert->addressEquals('/admin/content');

    // The "Back to site" link does not appear in the toolbar when on an admin
    // page.
    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);
    $this->drupalGet('/admin');
    $assert->elementExists('css', 'nav#toolbar-bar');
    $assert->linkNotExists('Back to site');
  }

}
