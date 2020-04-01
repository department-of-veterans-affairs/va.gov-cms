<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migration_tools\Message;

/**
 * Source to read from sidebar.json.
 *
 * @MigrateSource(
 *   id = "va_benefits_menu_source"
 * )
 */
class VaBenefitsMenu extends VaMenuBase {

  /**
   * Sections.
   *
   * @var mixed
   */
  protected $sections;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    // Path is required.
    if (empty($this->configuration['sections'])) {
      throw new MigrateException('You must declare the "sections" in your source settings.');
    }

    $this->sections = $configuration['sections'];
  }

  /**
   * Return a string representing the source file path.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return 'sidebar.json';
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $hub_dirs = [
      'Records' => '/records',
      'Disability benefits' => '/disability',
      'Education and training' => '/education',
      'Careers and employment' => '/careers-employment',
      'Pension benefits' => '/pension',
      'Housing assistance' => '/housing-assistance',
      'Life insurance' => '/life-insurance',
      'Burials and memorials' => '/burials-memorials',
    ];

    $contents = file_get_contents("modules/custom/va_gov_migrate/data/sidebar.json");
    $json_sidebar = json_decode($contents, TRUE);
    // Get top level menus.
    $menus = [];
    foreach ($json_sidebar as $page) {
      if (!empty($page['sidebarTitle']) && in_array($page['sidebarTitle'], $this->sections)) {
        if (empty($menus[$page['sidebarTitle']])) {

          if (empty($hub_dirs[$page['sidebarTitle']])) {
            Message::make("Hub @title doesn't have a directory in menus", ['@title' => $page['sidebarTitle']], Message::ERROR);
            $hub_dir = strtolower(str_replace(' ', '-', $page['sidebarTitle']));
          }
          else {
            $hub_dir = $hub_dirs[$page['sidebarTitle']];
          }
          if ($hub_dir === "/burials-memorials") {
            $menu_name = 'burials-and-memorials-benefits-hub';
          }
          else {
            $menu_name = strtolower(str_replace('/', '', $hub_dir . '-benefits-hub'));
          }
          $menu_name = substr($menu_name, 0, 27);
          $menus[$page['sidebarTitle']] = [
            'title' => $page['sidebarTitle'],
            'href' => $hub_dir,
            'items' => [],
            'menu' => $menu_name,
          ];
        }
        if (!empty($page['menus'])) {
          $menus[$page['sidebarTitle']]['items'] = $this->mergeMenus($page['menus'], $menus[$page['sidebarTitle']]['items']);
        }
      }
    }

    foreach ($json_sidebar as $page) {
      if (!empty($page['sidebarTitle']) && in_array($page['sidebarTitle'], $this->sections)) {
        if (!empty($page['items'])) {
          $this->findMergeMenus($page, $menus[$page['sidebarTitle']]['items']);
        }
      }
    }

    return new \ArrayIterator($this->process($menus));
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields['href'] = parent::fields();
    $fields['menu'] = 'Menu machine name';

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  protected function sanitizeDomain($href) {
    return str_replace('http://localhost:3001', 'https://www.va.gov', $href);
  }

}
