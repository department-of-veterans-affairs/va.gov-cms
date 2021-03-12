<?php

namespace tests\phpunit\Content;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm existence and correctness of metatags.
 */
class MetaTagTest extends ExistingSiteBase {

  /**
   * Tests the existence of the twitter:image:alt metatag.
   *
   * @group functional
   * @group all
   * @group metatag
   */
  public function testImageAltTags() {
    $author = $this->createUser();
    $author->addRole('content_editor');
    $author->save();
    $this->drupalLogin($author);
    $name = uniqid();
    $node = $this->createNode([
      'title' => "$name test",
      'type' => 'health_care_local_facility',
      'uid' => $author->id(),
    ]);
    $node->get('field_media')->generateSampleItems(1);
    $media = $node->get('field_media')->referencedEntities()[0];
    $image = $media->get('image')->getValue()[0];
    $image['alt'] = uniqid();
    $media->set('image', $image);
    $media->save();
    $node->setPublished(TRUE)->save();
    $nid = $node->id();
    $this->visit("/node/$nid");
    $page = $this->getSession()->getPage();
    $html = $this->getSession()->getPage()->getHtml();
    $queryString = '//meta[contains(@name, "twitter:image:alt") and contains(@content, "' . $image['alt'] . '")]';
    $tag = $page->find('xpath', $queryString);
    $this->assertTrue(!empty($tag), 'Page does not have twitter:image:alt metatag.');
    $queryString = '//meta[contains(@property, "og:image:alt") and contains(@content, "' . $image['alt'] . '")]';
    $tag = $page->find('xpath', $queryString);
    $this->assertTrue(!empty($tag), 'Page does not have og:image:alt metatag.');
  }

}
