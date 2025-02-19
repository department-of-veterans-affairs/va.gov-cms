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
class SplitMultipleExtensions extends BatchOperations implements BatchScriptInterface {

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
      - VACMS-20371: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20371
      - Splitting extensions when there are two
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Splits extensions when there are two by keeping the first and
    using the second to create a new phone paragraph
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'Phone extensions have been processed with a total @total processed and @completed completed.';
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

    $database = \Drupal::database();

    // !!!!!!!!!!!!!!!!!!!!!!!! DON'T INCLUDE THIS !!!!!!!!!!!!!!
    // There is only one of these anyway
    // Telephone field (node__field_telephone)
    // - VAMC Facility Mental health phone number (bundle = 'health_care_local_facility'
    // - Staff profile (bundle = 'person_profile')
    // - VAMC System Billing and Insurance (bundle = 'vamc_system_billing_insurance')
    $query_node_field_telephone = $database->select('paragraph__field_phone_extension', 'pfpe');
    $query_node_field_telephone->join('node__field_telephone','nft', 'nft.field_telephone_target_id = pfpe.entity_id');
    $query_node_field_telephone->addJoin('INNER', 'node_field_data', 'nfd', 'nft.entity_id = nfd.nid');
    $query_node_field_telephone->condition('pfpe.field_phone_extension_value','[^0-9]', 'REGEXP');
    $query_node_field_telephone->addField('nfd','nid');
    $query_node_field_telephone->addField('pfpe','entity_id');

    // Phone numbers paragraph field (node__field_phone_numbers_paragraph)
    // - VAMC Facility Health Service (bundle = 'health_care_local_health_service')
    // - VAMC System VA Police page (bundle = 'vamc_system_va_police')
    $query_node_field_phone_numbers_paragraph = $database->select('paragraph__field_phone_extension', 'pfpe');
    $query_node_field_phone_numbers_paragraph->join('node__field_phone_numbers_paragraph','nfpnp', 'nfpnp.field_phone_numbers_paragraph_target_id = pfpe.entity_id');
    $query_node_field_phone_numbers_paragraph->addJoin('INNER', 'node_field_data', 'nfd', 'nfpnp.entity_id = nfd.nid');
    $query_node_field_phone_numbers_paragraph->condition('pfpe.field_phone_extension_value','[^0-9]', 'REGEXP');
    $query_node_field_phone_numbers_paragraph->addField('nfd','nid');
    $query_node_field_phone_numbers_paragraph->addField('pfpe','entity_id');

    // Other phone numbers (paragraph__field_other_phone_numbers)
    // - Service location paragraph (bundle = 'service_location')
    $query_paragraph_field_other_phone_numbers = $database->select('paragraph__field_phone_extension', 'pfpe');
    $query_paragraph_field_other_phone_numbers->join('paragraph__field_other_phone_numbers','pfopn', 'pfopn.field_other_phone_numbers_target_id = pfpe.entity_id');
    $query_paragraph_field_other_phone_numbers->addJoin('INNER', 'node__field_service_location', 'nfsl', 'nfsl.field_service_location_target_id = pfopn.entity_id');
    $query_paragraph_field_other_phone_numbers->addJoin('INNER', 'node_field_data', 'nfd', 'nfsl.entity_id = nfd.nid');
    $query_paragraph_field_other_phone_numbers->condition('pfpe.field_phone_extension_value','[^0-9]', 'REGEXP');
    $query_paragraph_field_other_phone_numbers->addField('nfd','nid');
    $query_paragraph_field_other_phone_numbers->addField('pfpe','entity_id');

    // Phone (paragraph__field_phone)
    // - Service locations paragraph (bundle = 'service_location')
    $query_paragraph_field_phone = $database->select('paragraph__field_phone_extension', 'pfpe');
    $query_paragraph_field_phone->join('paragraph__field_phone','pfp', 'pfp.field_phone_target_id = pfpe.entity_id');
    $query_paragraph_field_phone->addJoin('INNER', 'node__field_service_location', 'nfsl', 'nfsl.field_service_location_target_id = pfp.entity_id');
    $query_paragraph_field_phone->addJoin('INNER', 'node_field_data', 'nfd', 'nfd.nid = nfsl.entity_id');
    $query_paragraph_field_phone->condition('pfpe.field_phone_extension_value','[^0-9]', 'REGEXP');
    $query_paragraph_field_phone->addField('nfd','nid');
    $query_paragraph_field_phone->addField('pfpe','entity_id');

    // $union_select = $query_node_field_telephone->union($query_node_field_phone_numbers_paragraph);
    $union_select = $query_node_field_phone_numbers_paragraph->union($query_paragraph_field_other_phone_numbers);
    $union_select = $union_select->union($query_paragraph_field_phone);

    $nids = $union_select->execute()->fetchAllKeyed();

    return $nids;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    $matches = [];
    // Get the node id from the key.
    if (preg_match('/\d+$/', $key, $matches)) {
      $node_id = $matches[0];
    }
    if (empty($node_id)) {
      $message = "No node id in $key. Nothing to process.";
      return $message;
    }

    // Load the node to which we'll be adding the paragraph
    $node = Node::load($node_id);
    $bundle_type = $node->bundle();
    if ($bundle_type !== "health_care_local_health_service" and $bundle_type !== "vha_facility_nonclinical_service") {
      $message = "No nodes to change in the targeted bundle types.";
      return $message;
    }
    $all_revisions = $this->getNodeAllRevisions($node_id);
    foreach ($all_revisions as $revision) {
      // Get the Service Location paragraph.
      $service_locations = $revision->get('field_service_location')->referencedEntities();
      if (empty($service_locations)) {
        continue;
      }
      foreach ($service_locations as $service_location) {
        // These are the two phone fields in Service Location.
        // They both use the phone_number paragraph type.
        $phone_fields = [
          'field_phone',
          'field_other_phone_numbers',
        ];
        foreach ($phone_fields as $phone_field) {
          $phone_number_entities = $service_location->get($phone_field)->referencedEntities();
          if (empty($phone_number_entities)) {
            continue;
          }
          foreach ($phone_number_entities as $phone_number_entity) {
            $extension = $phone_number_entity->get('field_phone_extension')->value;

            // We only need extensions with non-numerical characters.
            if (!preg_match('/[^0-9]/', $extension)) {
              continue;
            }
            // @todo: get the current extension
            $first_extension = [];
            preg_match('/^\d+/', $extension, $first_extension);
            $second_extension = [];
            preg_match('/\d+$/', $extension, $second_extension);
            $original_label = $phone_number_entity->get('field_phone_label')->value;
            if (!empty($original_label)) {
              $phone_number_entity->get('field_phone_label')->value = "$original_label #1";
            }
            if (!empty($first_extension)) {
              $phone_number_entity->get('field_phone_extension')->value = $first_extension;
              // Set the new phone value(s) on the node.
              $phone_number_entity->set(name: 'field_phone_extension', value: $first_extension);
              $phone_number_entity->save();
            }
            if (!empty($second_extension)) {
              $phone_number = $phone_number_entity->get('field_phone_number')->value;
              $field_service_location_phone = Paragraph::create([
                'type' => 'phone_number',
                'field_phone_number_type' => 'tel',
                'field_phone_number' => $phone_number,
                'field_phone_extension' => $second_extension,
                'field_phone_label' => "$original_label #2",
                'revision_translation_affected' => 1,
              ]);
              $field_service_location_phone->save();
              $service_location->get($phone_field)->appendItem($field_service_location_phone);
              $service_location->save();
            }
        }


          // $node->set(name: 'field_phone', value: array_map(callback: fn($field_service_location_phone) => [
          //   /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
          //   'target_id' => $field_service_location_phone->id(),
          //   'target_revision_id' => $field_service_location_phone->getRevisionId(),
          // ], array: $paragraphs));

          // $this->saveNodeRevision($node, 'cbu04905', FALSE);

        }
      }

      // $service_location_entity_id = $revision->getValues()->field_service_location->value;
    }

    // @todo: get all revisions

    // @todo: get paragaph info of $key
    // @todo: for each revision
      // @todo: separate the values of the extension
      // @todo: get the label info
      // @todo: get the current phone number
      // @todo: get the current extension
      // @todo: replace the current label
      // @todo: replace the current extension
      // @todo: create the new paragraph
        // @todo: assign each of the values
      // @todo: change all revisions

    // $paragraphs = [];
    // foreach ($node->get($this->sourceFieldName) as $field_value) {
    //   try {
    //     $telephoneParagraph = $this->createParagraph($node, $field_value);
    //     $telephoneParagraph->save();
    //     $paragraphs[] = $telephoneParagraph;
    //   }
    //   catch (EntityStorageException $e) {
    //     $message = "Unable to create paragraph. Reason provided: " . $e->getMessage();
    //     $this->batchOpLog->appendError($message);
    //     return $message;
    //   }
    // }
    // // Set the new phone value(s) on the node.
    // $node->set(name: $this->targetFieldName, value: array_map(callback: fn($paragraph) => [
    //   /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    //   'target_id' => $paragraph->id(),
    //   'target_revision_id' => $paragraph->getRevisionId(),
    // ], array: $paragraphs));

    // $message = 'Telephone migration complete for node ' . $node->id();
    // $this->saveNodeRevision($node, $message);
    return "Cool";
  }

  /**
   * Gets the paragraph.
   */

   /**
    * Gets the node from the paragraph.
    */

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
