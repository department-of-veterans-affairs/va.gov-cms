<?php

namespace Drupal\va_gov_notifications\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for no-active-users notifications.
 */
class NoActiveUsersNotificationSettingsForm extends ConfigFormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'va_gov_notifications_no_active_users_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['va_gov_notifications.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('va_gov_notifications.settings');
    $selected_roles = $config->get('no_active_users_recipient_roles') ?: [];

    $roles = [];
    $role_entities = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    foreach ($role_entities as $role_id => $role_entity) {
      $roles[$role_id] = $role_entity->label();
    }

    $form['recipient_roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Section leadership roles for derived recipients'),
      '#description' => $this->t('Users with these roles and section assignment will receive notifications for their sections with no active users.'),
      '#options' => $roles,
      '#default_value' => $selected_roles,
      '#required' => TRUE,
    ];

    $form['live_report_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Live report path'),
      '#description' => $this->t('Relative path used in the email for the live report link, for example: /admin/people/users_per_section.'),
      '#default_value' => $config->get('no_active_users_live_report_path') ?: '/admin/people/users_per_section',
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $roles = array_values(array_filter($form_state->getValue('recipient_roles')));
    $live_report_path = trim((string) $form_state->getValue('live_report_path'));

    $this->configFactory->getEditable('va_gov_notifications.settings')
      ->set('no_active_users_recipient_roles', $roles)
      ->set('no_active_users_live_report_path', $live_report_path)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
