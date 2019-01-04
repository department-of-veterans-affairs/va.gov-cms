<?php

namespace Traits;

use Drupal\node\Entity\Node;
use joshtronic\LoremIpsum;

/**
 * Provides methods to support testing of vacancy nodes.
 *
 * This trait is meant to be used only by test classes.
 */
trait VacancyTrait {

  use GroupTrait;

  // Options for generateRandomDate() function.
  /**
   * @var string
   */
  static private $dateDirectionFuture = 'future';
  /**
   * @var string
   */
  static private $dateDirectionPast = 'past';
  /**
   * @var string
   */
  static private $dateDirectionLastWeek = 'last week';
  /**
   * @var string
   */
  static private $dateDirectionNextWeek = 'next week';
  /**
   * @var string
   */
  static private $dateDirectionFutureMoreThan1Month = 'more than 1 month ahead';

  // Options for generateDate() function.
  /**
   * @var string
   */
  static private $today = 'today';
  /**
   * @var string
   */
  static private $tomorrow = 'tomorrow';
  /**
   * @var string
   */
  static private $yesterday = 'yesterday';

  // Static vars define 2 "types" of Vacancy nodes.
  // Business logic dictates a vacancy is "usajobs" when usajobs ID is present.
  // This class assumes "dear colleague" to be the default value.
  /**
   * Type = usajobs.
   *
   * @var string
   */
  static private $typeUsajobs = 'usajobs';
  /**
   * Type = Dcl, dear colleague.
   *
   * @var string
   */
  static private $typeDcl = 'dear colleague';

  // Static variable to define date format for Vacancy Date Fields.
  /**
   * @var string
   */
  static private $dateFormat = 'Y-m-d';

  /**
   * Helper func to ensure Vacancies are visible after they are created.
   *
   * Search api cron must run for new Vacancies to be seen.
   */
  protected function refreshVacancies() {
    search_api_cron();
    sleep(5);
  }

  /**
   * Create a vacancy node.
   *
   * @param string $type
   *   2 possible types: self::$typeUsajobs OR self::$typeDcl. Default: typeDcl.
   * @param object $node_extended
   *   Override any field(s) on $node that are generated randomly in this func.
   *
   * @return object
   *   The created vacancy node standard class object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createVacancy($type = NULL, $node_extended = NULL) {
    $org = NULL;
    $lipsum = new LoremIpsum();

    $body_text = $lipsum->paragraphs(3, 'p');
    $number_of_words_in_title = rand(2, 9);
    $random_title = $lipsum->words($number_of_words_in_title);

    // If the USAJobs ID is set, this indicates a type: USAJOBS vacancy.
    // Else this a type: DCL vacancy.
    $random_usajobs_id = NULL;
    if ($type == self::$typeUsajobs) {
      // Generate random 9 digit number for USAJOBS ID.
      $random_usajobs_id = rand(100000000, 999999999);
    }
    else {
      $type = self::$typeDcl;
    }
    $vacancy_type = $this->createVacancyTerm('vacancy_type', $lipsum);
    $random_pos_start_date = $this->generateRandomDate(self::$dateDirectionPast);
    $random_pos_end_date = $this->generateRandomDate(self::$dateDirectionFuture);
    if (empty($node_extended) || !property_exists($node_extended, "field_vacancy_org")) {
      $number_of_words_in_label = rand(2, 5);
      $rand_group_label = $lipsum->words($number_of_words_in_label);
      $group = $this->createGroup('organization', $rand_group_label);
      $org = $group->label();
    }

    $node = (object) [
      'title' => $random_title,
      'type' => 'vacancy',
      'field_vacancy_usajobs_id' => [
        'value' => $random_usajobs_id,
      ],
      'field_vacancy_job_desc' => [
        'value' => $body_text,
      ],
      'field_vacancy_type' => [
        'value' => $vacancy_type,
      ],
      'field_vacancy_org' => [
        'value' => $org,
      ],
      'field_vacancy_pub_start_date' => [
        'value' => $random_pos_start_date,
      ],
      'field_vacancy_pos_start_date' => [
        'value' => $random_pos_start_date,
      ],
      'field_vacancy_pos_end_date' => [
        'value' => $random_pos_end_date,
      ],
      'uid' => 1,
      'status' => 1,
    ];
    $vacancy_node = (object) array_merge((array) $node, (array) $node_extended);
    $node_returned = $this->createNode($vacancy_node);
    if (!empty($vacancy_node->status)) {
      // Set mod state 'published' unless $node_extended overwrites 'status'.
      $entity = Node::load($node_returned->nid);
      $entity->set('moderation_state', 'published');
      $entity->save();
    }

    return $node_returned;
  }

  /**
   * Generates 1 or several vacancy nodes.
   *
   * @param int $number
   *   The number of vacancies to generate.
   * @param string $type
   *   2 possible types: self::$typeUsajobs OR self::$typeDcl. Default: typeDcl.
   * @param object $node_extended
   *   Override any field(s) on $node that are generated randomly by default.
   *
   * @return array
   *   Array of created vacancy node standard class objects.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function generateVacancies($number, $type = NULL, $node_extended = NULL) {
    $nodes = [];
    for ($i = 0; $i <= $number; $i++) {
      try {
        $nodes[] = $this->createVacancy($type, $node_extended);
      }
      catch (Exception $e) {
        print "Error generating vacancy: " . $e->getMessage();
      }

    }
    return $nodes;
  }

  /**
   * Helper func to generate random dates.
   *
   * @param string $direction
   *   The direction of time range (past or future) within which to generate.
   *
   * @return string|null
   *   Formatted, random date string.
   */
  public function generateRandomDate($direction) {
    $now = time();
    $one_day = 86400;
    $one_week = 604800;
    $one_month = 2592000;
    $three_months = 7776000;
    $yesterday = $now - $one_day;
    $tomorrow = $now + $one_day;
    $next_week = $now + $one_week;
    $next_month = $now + $one_month;
    $last_week = $now - $one_week;
    $three_months_ago = $now - $three_months;
    $three_months_from_now = $now + $three_months;
    $random_date = NULL;

    switch ($direction) {
      case self::$dateDirectionFuture:
        $random_future_time = rand($tomorrow, $next_month);
        $random_date = date(self::$dateFormat, $random_future_time);
        break;

      case self::$dateDirectionFutureMoreThan1Month:
        $random_future_time = rand($next_month + $one_day, $three_months_from_now);
        $random_date = date(self::$dateFormat, $random_future_time);
        break;

      case self::$dateDirectionPast:
        $random_past_time = rand($three_months_ago, $yesterday);
        $random_date = date(self::$dateFormat, $random_past_time);
        break;

      case self::$dateDirectionLastWeek:
        $random_past_time = rand($last_week, $yesterday);
        $random_date = date(self::$dateFormat, $random_past_time);
        break;

      case self::$dateDirectionNextWeek:
        $random_future_time = rand($tomorrow, $next_week);
        $random_date = date(self::$dateFormat, $random_future_time);
        break;
    }
    return $random_date;
  }

  /**
   * Helper func to generate specific date.
   *
   * @return string|null
   *   Formatted date string.
   */
  public function getDate($option) {
    $now = time();
    $one_day = 86400;
    $yesterday = $now - $one_day;
    $tomorrow = $now + $one_day;

    switch ($option) {
      case self::$today:
        $date = date(self::$dateFormat, $now);
        break;

      case self::$yesterday:
        $date = date(self::$dateFormat, $yesterday);
        break;

      case self::$tomorrow:
        $date = date(self::$dateFormat, $tomorrow);
        break;
    }

    return $date;

  }

  /**
   * Create a term to be joined to a vacancy node via entity reference.
   *
   * @param string $type
   *   2 possible types: self::$typeUsajobs OR self::$typeDcl. Default: typeDcl.
   * @param \joshtronic\LoremIpsum $lipsum
   *   Pass in to avoid new class instance forcing term beginning with "lorem".
   *
   * @return string
   *   The random created taxonomy term name.
   */
  protected function createVacancyTerm($type, LoremIpsum $lipsum = NULL) {
    if (empty($lipsum)) {
      $lipsum = new LoremIpsum();
    }

    $number_of_words_in_name = rand(1, 3);
    $random_name = $lipsum->words($number_of_words_in_name);
    $term = (object) [
      'name' => $random_name,
      'vocabulary_machine_name' => $type,
      'bundle' => $type,
    ];
    $this->createTerm($term);

    return $random_name;
  }

}
