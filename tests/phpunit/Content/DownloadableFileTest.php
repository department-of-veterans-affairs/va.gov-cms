<?php

namespace tests\phpunit\Content;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\paragraphs\Entity\Paragraph;
use weitzman\DrupalTestTraits\ExistingSiteBase;
use Drupal\Core\File\FileSystemInterface;

/**
 * Confirm downloadable_file paragraphs are displayed correctly.
 */
class DownloadableFileTest extends ExistingSiteBase {

  /**
   * Create a File entity with the specified name and content.
   */
  public function createFile($name, $content) {
    $path = uniqid();
    $directory = "public://$path";
    \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    return file_save_data($content, "$directory/$name.txt");
  }

  /**
   * Create a Media entity.
   */
  public function createMedia(array $properties) {
    $result = Media::create($properties);
    $result
      ->setPublished(TRUE)
      ->save();
    return $result;
  }

  /**
   * Create a `document` entity.
   */
  public function createDocumentMedia($name, $content) {
    $file = $this->createFile($name, $content);
    return $this->createMedia([
      'bundle' => 'document',
      'name' => $name,
      'uid' => '1',
      'field_document' => [
        'entity' => $file,
      ],
    ]);
  }

  /**
   * Create an `image` entity.
   */
  public function createImageMedia($name, $content) {
    $file = $this->createFile($name, $content);
    return $this->createMedia([
      'bundle' => 'image',
      'name' => $name,
      'uid' => '1',
      'image' => [
        'entity' => $file,
      ],
    ]);
  }

  /**
   * Create a `video` entity.
   */
  public function createVideoMedia($name, $content) {
    return $this->createMedia([
      'bundle' => 'video',
      'name' => $name,
      'uid' => '1',
      'field_media_video_embed_field' => [
        [
          'value' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ],
      ],
    ]);
  }

  /**
   * Create a `media` entity.
   */
  public function createMediaParagraph($media) {
    $result = Paragraph::create([
      'type' => 'downloadable_file',
      'field_title' => $media->id() . ' test',
      'field_media' => [
        'entity' => $media,
      ],
    ]);
    $result->save();
    return $result;
  }

  /**
   * Document files should present a specially formatted link.
   *
   * @group functional
   * @group downloadable_file
   */
  public function testDocumentFilesPresentSpeciallyFormattedLink() {
    $author = $this->createUser();
    $author->addRole('content_editor');
    $author->save();
    $this->drupalLogin($author);
    $name = uniqid();
    $content = uniqid();
    $media = $this->createDocumentMedia($name, $content);
    $file_url = File::load($media->getSource()->getSourceFieldValue($media))->createFileUrl(FALSE);
    $paragraph = $this->createMediaParagraph($media);
    $node = $this->createNode([
      'title' => "$name test",
      'type' => 'documentation_page',
      'field_content_block' => [
        [
          'entity' => $paragraph,
        ],
      ],
      'uid' => $author->id(),
    ]);
    $node
      ->setPublished(TRUE)
      ->save();
    $nid = $node->id();
    $this->visit("/node/$nid");
    $page = $this->getSession()->getPage();
    $html = $this->getSession()->getPage()->getHtml();
    $queryString = '//a[contains(@class, "downloadable-file-link--document") and contains(@target, "_blank") and contains(@aria-label, "Download") and contains(@href, "' . parse_url($file_url, PHP_URL_PATH) . '")]';
    $link = $page->find('xpath', $queryString);
    $this->assertTrue(!empty($link), 'Page does not have downloadable file link.');
    $linkHtml = $link->getHtml();
    $this->assertEquals($linkHtml, $media->id() . ' test (TXT)', 'Downloadable file link does not match expected pattern.');
  }

  /**
   * Image files should present a specially formatted link.
   *
   * @group functional
   * @group downloadable_file
   */
  public function testImageFilesPresentSpeciallyFormattedLink() {
    $author = $this->createUser();
    $author->addRole('content_editor');
    $author->save();
    $this->drupalLogin($author);
    $name = uniqid();
    $content = uniqid();
    $media = $this->createImageMedia($name, $content);
    $file_url = File::load($media->getSource()->getSourceFieldValue($media))->createFileUrl(FALSE);
    $paragraph = $this->createMediaParagraph($media);
    $node = $this->createNode([
      'title' => "$name test",
      'type' => 'documentation_page',
      'field_content_block' => [
        [
          'entity' => $paragraph,
        ],
      ],
      'uid' => $author->id(),
    ]);
    $node
      ->setPublished(TRUE)
      ->save();
    $nid = $node->id();
    $this->visit("/node/$nid");
    $page = $this->getSession()->getPage();
    $html = $this->getSession()->getPage()->getHtml();
    $queryString = '//a[contains(@class, "downloadable-file-link--image") and contains(@target, "_blank") and contains(@aria-label, "Download") and contains(@href, "' . parse_url($file_url, PHP_URL_PATH) . '")]';
    $link = $page->find('xpath', $queryString);
    $this->assertTrue(!empty($link), 'Page does not have downloadable file link.');
    $linkHtml = $link->getHtml();
    $this->assertEquals($linkHtml, $media->id() . ' test (TXT)', 'Downloadable file link does not match expected pattern.');
  }

  /**
   * Videos should present a specially formatted link.
   *
   * @group functional
   * @group downloadable_file
   */
  public function testVideoFilesPresentSpeciallyFormattedLink() {
    $author = $this->createUser();
    $author->addRole('content_editor');
    $author->save();
    $this->drupalLogin($author);
    $name = uniqid();
    $content = uniqid();
    $media = $this->createVideoMedia($name, $content);
    $file_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    $paragraph = $this->createMediaParagraph($media);
    $node = $this->createNode([
      'title' => "$name test",
      'type' => 'documentation_page',
      'field_content_block' => [
        [
          'entity' => $paragraph,
        ],
      ],
      'uid' => $author->id(),
    ]);
    $node
      ->setPublished(TRUE)
      ->save();
    $nid = $node->id();
    $this->visit("/node/$nid");
    $page = $this->getSession()->getPage();
    $html = $this->getSession()->getPage()->getHtml();
    $queryString = '//a[contains(@class, "downloadable-file-link--video") and contains(@target, "_blank") and contains(@href, "' . parse_url($file_url, PHP_URL_PATH) . '")]';
    $link = $page->find('xpath', $queryString);
    $this->assertTrue(!empty($link), 'Page does not have downloadable file link.');
    $linkHtml = $link->getHtml();
    $this->assertEquals($linkHtml, $media->id() . ' test', 'Downloadable file link does not match expected pattern.');
  }

}
