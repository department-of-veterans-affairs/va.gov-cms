<?php

namespace tests\phpunit\Performance;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm the performance of edit preview.
 */
class PreviewTest extends ExistingSiteBase {

  /**
   * A test method to deterine the amount of time it takes to load preview.
   *
   * @group performance
   * @group all
   * Disable this test since we don't have a preview server running for CI/PR
   * environments and it is breaking builds.
   * @group disabled
   *
   * @dataProvider benchmarkTime
   */
  public function testPreviewPerformance($benchmark) {

    $account = $this->createUser();
    $account->addRole('administrator');
    $account->save();

    $this->drupalLogin($account);

    $node = $this->createNode([
      'title' => 'Testo',
      'type' => 'news_story',
      'uid' => $account->id(),
    ]);
    $node->setNewRevision(TRUE);
    $node->revision_log = 'Created revision for node in preview';
    $node->setRevisionCreationTime(REQUEST_TIME);

    // Save it as unpublished and associate with user.
    $node->setRevisionUserId($account->id());
    $node->set('moderation_state', 'draft');
    $node->setPublished()->save();

    $nid = $node->id();

    $this->visit('/node/' . $nid . '/edit');

    $hostip = getenv('LANDO_HOST_IP');

    $host = \Drupal::request()->getHost();

    switch ($host) {
      case 'localhost':
        $url = 'http://' . $hostip . ':3001/preview?nodeId=' . $nid;
        break;

      case 'test1.cms.va.gov':
        $url = 'https://vetsgov-pr-9681.herokuapp.com/preview?nodeId=' . $nid;
        break;

      case 'dev.cms.va.gov':
        $url = 'http://preview-dev.vfs.va.gov/preview?nodeId=' . $nid;
        break;

      case 'stg.cms.va.gov':
      case 'staging.cms.va.gov':
        $url = 'http://preview-staging.vfs.va.gov/preview?nodeId=' . $nid;
        break;

      case 'cms.va.gov':
      case 'prod.cms.va.gov':
        $url = 'http://preview-prod.vfs.va.gov/preview?nodeId=' . $nid;
        break;

      default:
        $url = 'http://preview-prod.vfs.va.gov/preview?nodeId=' . $nid;
        break;
    }

    // Start timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    $this->visit($url);

    $hasGoBack = $this->getSession()->getPage()->hasContent('Go back to editor');

    $this->assertTrue($hasGoBack, 'The preview page did not load properly');

    // End timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $microsecs = ($endtime - $starttime);

    // Test assertion.
    $secs = number_format($microsecs, 3);
    $this->assertLessThan($benchmark, $secs, __METHOD__ . "\nOperation took " . $secs . " seconds which is longer than the benchmark of " . $benchmark . " seconds.\n");

    $message = __METHOD__ . "\nOperation took " . $secs . " seconds compared to the benchmark of " . $benchmark . " seconds.\n";
    fwrite(STDERR, print_r($message, TRUE));
  }

  /**
   * Returns benchmark time to beat in order for test to succeed.
   *
   * @return array
   *   Array containing entity type as string and expected count as int
   */
  public function benchmarkTime() {
    return [
      [5],
    ];
  }

}
