<?php

namespace tests\phpunit;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to assure the performance of edit preview.
 */
class PreviewPerformance extends ExistingSiteBase {

  /**
   * A test method to deterine the amount of time it takes to load preview.
   *
   * @group performance
   * @group all
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

      case 'dev.va.agile6.com':
        $url = 'http://preview-dev.vfs.va.gov/preview?nodeId=' . $nid;
        break;

      case 'stg.va.agile6.com':
        $url = 'http://preview-staging.vfs.va.gov/preview?nodeId=' . $nid;
        break;

      case 'va.gov':
        $url = 'http://preview-live.vfs.va.gov/preview?nodeId=' . $nid;
        break;

      default:
        $url = 'http://preview-live.vfs.va.gov/preview?nodeId=' . $nid;
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
    $this->assertLessThan($benchmark, $secs, "\nOperation took " . $secs . " seconds which is longer than the benchmark of " . $benchmark . " seconds.\n");

    $message = "\nOperation took " . $secs . " seconds compared to the benchmark of " . $benchmark . " seconds.\n";
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
      [2],
    ];
  }

}
