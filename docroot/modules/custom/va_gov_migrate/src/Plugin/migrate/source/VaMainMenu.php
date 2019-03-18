<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\source;

/**
 * Source to read from sidebar.json.
 *
 * @MigrateSource(
 *   id = "va_main_menu_source"
 * )
 */
class VaMainMenu extends VaMenuBase {

  /**
   * Return a string representing the source file path.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return 'megamenu.json';
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $contents = file_get_contents("modules/custom/va_gov_migrate/data/megamenu.json");
    $json_main = json_decode($contents, TRUE);
    // Get top level menus.
    $menus = [];
    foreach ($json_main as $section) {
      $main_section = [
        'title' => $section['title'],
        'href' => empty($section['href']) ? 'route:<nolink>' : $section['href'],
      ];
      if (!empty($section['menuSections'])) {
        foreach ($section['menuSections'] as $menu_section) {
          if (!empty($menu_section['title'])) {
            $main_section['items'][] = $this->makeSection($menu_section);
          }
        }
      }
      $menus[] = $main_section;
    }

    return new \ArrayIterator($this->process($menus));
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['id']['type'] = 'string';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  protected function sanitizeDomain($href) {
    return str_replace('https://www.va.gov', '', $href);
  }

  /**
   * Generate a menu tree from a menu section.
   *
   * @param array $section
   *   The menu section from json.
   *
   * @return array
   *   The menu tree.
   */
  protected function makeSection(array $section) {
    $menu_section['title'] = $section['title'];
    $menu_section['href'] = 'route:<nolink>';
    $menu_section['items'] = [];
    foreach ($section['links'] as $name => $link) {
      if (!empty($link['title'])) {
        $column = [
          'title' => $link['title'],
          'href' => empty($link['href']) ? '' : $link['href'],
          'items' => [],
        ];
        foreach ($link['links'] as $item) {
          $column['items'][] = [
            'title' => $item['text'],
            'href' => $item['href'],
          ];
        }
        $menu_section['items'][] = $column;
      }
      elseif (!empty($link['text'])) {
        $menu_section['items'][] = [
          'title' => 'View all in ' . $link['text'],
          'href' => $link['href'],
        ];
      }
    }
    return $menu_section;
  }

}
