<?php

namespace Drupal\va_gov_backend\Plugin\Filter;

use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Repairs formatting of email address links.
 *
 * @Filter(
 *   id = "va_gov_backend_email_link_repair",
 *   title = @Translation("Email Link Repair"),
 *   description = @Translation("Repairs formatting of email address links."),
 *   settings = {
 *     "title" = TRUE,
 *   },
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class EmailLinkRepairFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Constructs a Email Link Repair Filter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PathValidatorInterface $path_validator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pathValidator = $path_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    if (strpos($text, '<a href="') === FALSE || strpos($text, '@') === FALSE) {
      return $result;
    }
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//a[contains(@href, "@")]') as $element) {
      $url = $element->getAttribute('href');
      $email = filter_var($url, FILTER_VALIDATE_EMAIL);
      if ($email !== FALSE) {
        $element->setAttribute('href', "mailto:$email");
      }
    }
    $result->setProcessedText(Html::serialize($dom));
    return $result;
  }

}
