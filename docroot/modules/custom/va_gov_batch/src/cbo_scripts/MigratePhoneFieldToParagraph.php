<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\telephone\Plugin\Field\FieldType\TelephoneItem;
use libphonenumber\PhoneNumberUtil;

/**
 * Migrate Staff profile phone field to phone paragraph.
 */
class MigratePhoneFieldToParagraph extends BatchOperations implements BatchScriptInterface {

  /**
   * The target field name.
   *
   * @var string
   */
  protected string $targetFieldName = 'field_telephone';

  /**
   * The source  field name.
   *
   * @var string
   */
  protected string $sourceFieldName = 'field_phone_number';

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return <<<TITLE
    For:
      - VACMS-17860: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/17860.
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Migrates (not a formal Drupal migration) telephone field values into a
    paragraph entity reference field with discrete phone, extension, and labels.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'Phone migration ended processing with a total @total processed and @completed completed.';
  }

  /**
   * {@inheritDoc}
   */
  public function getItemType(): string {
    return 'node:field_telephone';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return \Drupal::entityQuery('node')
      ->condition('type', 'person_profile')
      ->accessCheck(FALSE)
      ->condition($this->sourceFieldName, operator: 'IS NOT NULL')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    $node = Node::load($item);
    $paragraphs = [];
    foreach ($node->get($this->sourceFieldName) as $field_value) {
      try {
        $telephoneParagraph = $this->createParagraph($node, $field_value);
        $telephoneParagraph->save();
        $paragraphs[] = $telephoneParagraph;
      }
      catch (EntityStorageException $e) {
        $message = "Unable to create paragraph. Reason provided: " . $e->getMessage();
        $this->batchOpLog->appendError($message);
        return $message;
      }
    }
    // Set the new phone value(s) on the node.
    $node->set(name: $this->targetFieldName, value: array_map(callback: fn($paragraph) => [
      /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ], array: $paragraphs));

    $message = 'Telephone migration complete for node ' . $node->id();
    $this->saveNodeRevision($node, $message);
    return $message;
  }

  /**
   * Creates the paragraph.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param \Drupal\telephone\Plugin\Field\FieldType\TelephoneItem $value
   *   The field item.
   *
   * @return \Drupal\paragraphs\ParagraphInterface
   *   The paragraph.
   */
  private function createParagraph(NodeInterface $node, TelephoneItem $value): ParagraphInterface {
    assert(!empty($value->getValue()['value']));
    // Breakout $value into discrete phone and extension values.
    $value = $value->getValue()['value'];
    $data = $this->extractPhoneAndExtension($value);
    $phone = $data['phone'];
    $extension = $data['extension'];
    return Paragraph::create([
      'type' => 'phone_number',
      'field_phone_number' => $phone,
      'field_phone_extension' => $extension,
      'status' => 1,
      'revision_default' => 1,
      'isDefaultRevision' => 1,
      'parent_type' => 'node',
      'parent_field_name' => $this->targetFieldName,
      'parent_id' => $node->id(),
      'revision_translation_affected' => 1,
    ]);
  }

  /**
   * Extracts phone and extension into distinct values.
   *
   * @param string $input
   *   The phone and extension as a single concatenated string. eg:
   *   503-555-1212, x1234.
   *
   * @return array
   *   The extracted and parsed phone and extension. Phone will be in the
   *   nnn-nnn-nnnn format while the extension will contain only integers.
   */
  public static function extractPhoneAndExtension(string $input): array {
    $phoneNumberUtil = PhoneNumberUtil::getInstance();
    $phoneNumberMatcher = $phoneNumberUtil->findNumbers($input, 'US');
    $phoneNumberMatcher->next();
    $extension = $phoneNumberMatcher->current()?->number()->getExtension() ?? '';
    $phone = $phoneNumberMatcher->current()?->number()->getNationalNumber() ?? '';
    $phoneLength = strlen($phone);

    // Destination field allows only 12 digits for phone, and truncating will
    // result in data loss.
    if ($phoneLength > 12) {
      $message = sprintf('Phone number is too long. Number provided: %s', $phoneLength);
      \Drupal::logger('va_gov_batch::phone_length_exceeded')->error($message);
      $phone = $message;
    }

    // If phone length is 10 digits, format it in the nnn-nnn-nnnn format.
    if ($phoneLength === 10) {
      $phone = preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $phone);
    }

    return [
      'phone' => $phone,
      'extension' => $extension,
    ];
  }

}
