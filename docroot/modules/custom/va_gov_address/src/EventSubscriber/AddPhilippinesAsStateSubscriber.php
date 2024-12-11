<?php

namespace Drupal\va_gov_address\EventSubscriber;

use CommerceGuys\Addressing\AddressFormat\AdministrativeAreaType;
use Drupal\address\Event\AddressEvents;
use Drupal\address\Event\AddressFormatEvent;
use Drupal\address\Event\SubdivisionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds a Philippines to the US States.
 */
class AddPhilippinesAsStateSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AddressEvents::ADDRESS_FORMAT][] = ['onAddressFormat'];
    $events[AddressEvents::SUBDIVISIONS][] = ['onSubdivisions'];
    return $events;
  }

  /**
   * Alters the address format for the US.
   *
   * @param \Drupal\address\Event\AddressFormatEvent $event
   *   The address format event.
   */
  public function onAddressFormat(AddressFormatEvent $event) {
    $definition = $event->getDefinition();
    if ($definition['country_code'] == 'US') {
      $definition['format'] = $definition['format'] . "\n%administrativeArea";
      $definition['administrative_area_type'] = AdministrativeAreaType::STATE;
      $definition['subdivision_depth'] = 1;
      $event->setDefinition($definition);
    }
  }

  /**
   * Provides the states of the US (plus the Philippines).
   *
   * @param \Drupal\address\Event\SubdivisionsEvent $event
   *   The subdivisions event.
   */
  public function onSubdivisions(SubdivisionsEvent $event) {
    $parents = $event->getParents();
    if ($event->getParents() != ['US']) {
      return;
    }

    $definitions = [
      'country_code' => $parents[0],
      'parents' => $parents,
      'subdivisions' => [
        'AL' => [
          'code' => 'AL',
          'name' => 'Alabama',
          'country_code' => 'US',
          'id' => 'AL',
        ],
        'AK' => [
          'code' => 'AK',
          'name' => 'Alaska',
          'country_code' => 'US',
          'id' => 'AK',
        ],
        'AS' => [
          'code' => 'AS',
          'name' => 'American Samoa',
          'country_code' => 'US',
          'id' => 'AS',
        ],
        'AZ' => [
          'code' => 'AZ',
          'name' => 'Arizona',
          'country_code' => 'US',
          'id' => 'AZ',
        ],
        'AR' => [
          'code' => 'AR',
          'name' => 'Arkansas',
          'country_code' => 'US',
          'id' => 'AR',
        ],
        'AA' => [
          'code' => 'AA',
          'name' => 'Armed Forces (AA)',
          'country_code' => 'US',
          'id' => 'AA',
        ],
        'AE' => [
          'code' => 'AE',
          'name' => 'Armed Forces (AE)',
          'country_code' => 'US',
          'id' => 'AE',
        ],
        'AP' => [
          'code' => 'AP',
          'name' => 'Armed Forces (AP)',
          'country_code' => 'US',
          'id' => 'AP',
        ],
        'CA' => [
          'code' => 'CA',
          'name' => 'California',
          'country_code' => 'US',
          'id' => 'CA',
        ],
        'CO' => [
          'code' => 'CO',
          'name' => 'Colorado',
          'country_code' => 'US',
          'id' => 'CO',
        ],
        'CT' => [
          'code' => 'CT',
          'name' => 'Connecticut',
          'country_code' => 'US',
          'id' => 'CT',
        ],
        'DE' => [
          'code' => 'DE',
          'name' => 'Delaware',
          'country_code' => 'US',
          'id' => 'DE',
        ],
        'DC' => [
          'code' => 'DC',
          'name' => 'District of Columbia',
          'country_code' => 'US',
          'id' => 'DC',
        ],
        'FL' => [
          'code' => 'FL',
          'name' => 'Florida',
          'country_code' => 'US',
          'id' => 'FL',
        ],
        'GA' => [
          'code' => 'GA',
          'name' => 'Georgia',
          'country_code' => 'US',
          'id' => 'GA',
        ],
        'GU' => [
          'code' => 'GU',
          'name' => 'Guam',
          'country_code' => 'US',
          'id' => 'GU',
        ],
        'HI' => [
          'code' => 'HI',
          'name' => 'Hawaii',
          'country_code' => 'US',
          'id' => 'HI',
        ],
        'ID' => [
          'code' => 'ID',
          'name' => 'Idaho',
          'country_code' => 'US',
          'id' => 'ID',
        ],
        'IL' => [
          'code' => 'IL',
          'name' => 'Illinois',
          'country_code' => 'US',
          'id' => 'IL',
        ],
        'IN' => [
          'code' => 'IN',
          'name' => 'Indiana',
          'country_code' => 'US',
          'id' => 'IN',
        ],
        'IA' => [
          'code' => 'IA',
          'name' => 'Iowa',
          'country_code' => 'US',
          'id' => 'IA',
        ],
        'KS' => [
          'code' => 'KS',
          'name' => 'Kansas',
          'country_code' => 'US',
          'id' => 'KS',
        ],
        'KY' => [
          'code' => 'KY',
          'name' => 'Kentucky',
          'country_code' => 'US',
          'id' => 'KY',
        ],
        'LA' => [
          'code' => 'LA',
          'name' => 'Louisiana',
          'country_code' => 'US',
          'id' => 'LA',
        ],
        'ME' => [
          'code' => 'ME',
          'name' => 'Maine',
          'country_code' => 'US',
          'id' => 'ME',
        ],
        'MH' => [
          'code' => 'MH',
          'name' => 'Marshall Islands',
          'country_code' => 'US',
          'id' => 'MH',
        ],
        'MD' => [
          'code' => 'MD',
          'name' => 'Maryland',
          'country_code' => 'US',
          'id' => 'MD',
        ],
        'MA' => [
          'code' => 'MA',
          'name' => 'Massachusetts',
          'country_code' => 'US',
          'id' => 'MA',
        ],
        'MI' => [
          'code' => 'MI',
          'name' => 'Michigan',
          'country_code' => 'US',
          'id' => 'MI',
        ],
        'FM' => [
          'code' => 'FM',
          'name' => 'Micronesia',
          'country_code' => 'US',
          'id' => 'FM',
        ],
        'MN' => [
          'code' => 'MN',
          'name' => 'Minnesota',
          'country_code' => 'US',
          'id' => 'MN',
        ],
        'MS' => [
          'code' => 'MS',
          'name' => 'Mississippi',
          'country_code' => 'US',
          'id' => 'MS',
        ],
        'MO' => [
          'code' => 'MO',
          'name' => 'Missouri',
          'country_code' => 'US',
          'id' => 'MO',
        ],
        'MT' => [
          'code' => 'MT',
          'name' => 'Montana',
          'country_code' => 'US',
          'id' => 'MT',
        ],
        'NE' => [
          'code' => 'NE',
          'name' => 'Nebraska',
          'country_code' => 'US',
          'id' => 'NE',
        ],
        'NV' => [
          'code' => 'NV',
          'name' => 'Nevada',
          'country_code' => 'US',
          'id' => 'NV',
        ],
        'NH' => [
          'code' => 'NH',
          'name' => 'New Hampshire',
          'country_code' => 'US',
          'id' => 'NH',
        ],
        'NJ' => [
          'code' => 'NJ',
          'name' => 'New Jersey',
          'country_code' => 'US',
          'id' => 'NJ',
        ],
        'NM' => [
          'code' => 'NM',
          'name' => 'New Mexico',
          'country_code' => 'US',
          'id' => 'NM',
        ],
        'NY' => [
          'code' => 'NY',
          'name' => 'New York',
          'country_code' => 'US',
          'id' => 'NY',
        ],
        'NC' => [
          'code' => 'NC',
          'name' => 'North Carolina',
          'country_code' => 'US',
          'id' => 'NC',
        ],
        'ND' => [
          'code' => 'ND',
          'name' => 'North Dakota',
          'country_code' => 'US',
          'id' => 'ND',
        ],
        'MP' => [
          'code' => 'MP',
          'name' => 'Northern Mariana Islands',
          'country_code' => 'US',
          'id' => 'MP',
        ],
        'OH' => [
          'code' => 'OH',
          'name' => 'Ohio',
          'country_code' => 'US',
          'id' => 'OH',
        ],
        'OK' => [
          'code' => 'OK',
          'name' => 'Oklahoma',
          'country_code' => 'US',
          'id' => 'OK',
        ],
        'OR' => [
          'code' => 'OR',
          'name' => 'Oregon',
          'country_code' => 'US',
          'id' => 'OR',
        ],
        'PW' => [
          'code' => 'PW',
          'name' => 'Palau',
          'country_code' => 'US',
          'id' => 'PW',
        ],
        'PA' => [
          'code' => 'PA',
          'name' => 'Pennsylvania',
          'country_code' => 'US',
          'id' => 'PA',
        ],
        'PH' => [
          'code' => 'PH',
          'name' => 'Philippines',
          'country_code' => 'US',
          'id' => 'PH',
        ],

        'PR' => [
          'code' => 'PR',
          'name' => 'Puerto Rico',
          'country_code' => 'US',
          'id' => 'PR',
        ],
        'RI' => [
          'code' => 'RI',
          'name' => 'Rhode Island',
          'country_code' => 'US',
          'id' => 'RI',
        ],
        'SC' => [
          'code' => 'SC',
          'name' => 'South Carolina',
          'country_code' => 'US',
          'id' => 'SC',
        ],
        'SD' => [
          'code' => 'SD',
          'name' => 'South Dakota',
          'country_code' => 'US',
          'id' => 'SD',
        ],
        'TN' => [
          'code' => 'TN',
          'name' => 'Tennessee',
          'country_code' => 'US',
          'id' => 'TN',
        ],
        'TX' => [
          'code' => 'TX',
          'name' => 'Texas',
          'country_code' => 'US',
          'id' => 'TX',
        ],
        'UT' => [
          'code' => 'UT',
          'name' => 'Utah',
          'country_code' => 'US',
          'id' => 'UT',
        ],
        'VT' => [
          'code' => 'VT',
          'name' => 'Vermont',
          'country_code' => 'US',
          'id' => 'VT',
        ],
        'VI' => [
          'code' => 'VI',
          'name' => 'Virgin Islands',
          'country_code' => 'US',
          'id' => 'VI',
        ],
        'VA' => [
          'code' => 'VA',
          'name' => 'Virginia',
          'country_code' => 'US',
          'id' => 'VA',
        ],
        'WA' => [
          'code' => 'WA',
          'name' => 'Washington',
          'country_code' => 'US',
          'id' => 'WA',
        ],
        'WV' => [
          'code' => 'WV',
          'name' => 'West Virginia',
          'country_code' => 'US',
          'id' => 'WV',
        ],
        'WI' => [
          'code' => 'WI',
          'name' => 'Wisconsin',
          'country_code' => 'US',
          'id' => 'WI',
        ],
        'WY' => [
          'code' => 'WY',
          'name' => 'Wyoming',
          'country_code' => 'US',
          'id' => 'WY',
        ],
      ],
    ];
    $event->setDefinitions($definitions);
  }

}
