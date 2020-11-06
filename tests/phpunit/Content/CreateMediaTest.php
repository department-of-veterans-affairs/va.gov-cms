<?php

namespace tests\phpunit\Content;

use weitzman\DrupalTestTraits\ExistingSiteBase;
use Drupal\media\Entity\Media;

/**
 * A test to confirm ability to create media.
 */
class CreateMediaTest extends ExistingSiteBase {

  /**
   * A test method to determine the ability and time to create media.
   *
   * @group performance
   * @group functional
   * @group all
   *
   * @dataProvider benchmarkTime
   */
  public function testCreateMedia($benchmark) {

    // Start timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    // Creates media. Will be automatically cleaned up at the end of the test.
    $file = file_save_data(uniqid(), 'public://test' . uniqid() . '.txt');

    $media_document = Media::create([
      'bundle' => 'document',
      'name' => 'test',
      'uid' => '1',
      'field_media_file' => [
        'target_id' => $file->id(),
      ],
    ]);
    $media_document->setPublished(TRUE)
      ->save();

    $media_document->setName(uniqid())
      ->setPublished(TRUE)
      ->save();

    $this->assertNotEmpty($media_document->getName(), 'Failed to create media');

    // End timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $microsecs = ($endtime - $starttime);

    // Cleanup the file.
    $file->delete();

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
      [2],
    ];
  }

}
