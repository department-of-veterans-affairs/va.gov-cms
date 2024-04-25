<?php

namespace Drupal\va_gov_content_release\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_build_trigger\Traits\RunsDuringBusinessHours;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a VA.gov Content Release form.
 */
class ContentReleaseStatusForm extends FormBase {
  use RunsDuringBusinessHours;

  const OWNER = 'department-of-veterans-affairs';
  const REPO = 'next-build';
  const WORKFLOW_ID = 'content-release.yml';

  /**
   * The http client service.
   *
   * @var \GuzzleHttp\Client
   */
  private ClientInterface $httpClient;

  /**
   * The Drupal settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  private Settings $settings;

  /**
   * The Content Release Status Form constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings service.
   * @param \GuzzleHttp\ClientInterface $client
   *   The http client service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   */
  public function __construct(Settings $settings, ClientInterface $client, TimeInterface $time, DateFormatterInterface $dateFormatter) {
    $this->settings = $settings;
    $this->httpClient = $client;
    $this->time = $time;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('settings'),
      $container->get('http_client'),
      $container->get('datetime.time'),
      $container->get('date.formatter'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'va_gov_content_release_next_simple';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state
  ): array {

    // Make a call to GitHub using http_client service to check workflow runs.
    $response = $this->httpClient->request('GET',
      'https://api.github.com/repos/'. self::OWNER .'/'. self::REPO .'/actions/workflows/'. self::WORKFLOW_ID .'/runs');
    $data = json_decode($response->getBody()->getContents());

    // Get the latest run.
    $latest_run = $data->workflow_runs[0];
    // Get last run.
    $last_run = $data->workflow_runs[1];
    // Get start of the run.
    $start = $latest_run->run_started_at;
    // Calculate the duration of the run so far.
    $duration = (time() - strtotime($start));

    $form['content_release_status_block'] = [
      '#theme' => 'status_report_grouped',
      '#grouped_requirements' => [
        [
          'title' => $this->t('Latest Content Release Run'),
          'type' => 'content-release-status',
          'items' => [
            'last_run' => [
              'title' => $this->t('Previous Build Status'),
              'value' => $last_run->status,
            ],
            'status' => [
              'title' => $this->t('Current Build Status'),
              'value' => $latest_run->status,
            ],
            'run_start' => [
              'title' => $this->t('Last Run Start Time'),
              'value' => date('Y-m-d H:i:s', strtotime($start)),
            ],
            'duration' => [
              'title' => $this->t('Duration'),
              'value' => gmdate('H:i:s', $duration),
            ],
            'during_business_hours' => [
              'title' => $this->t('During Business Hours'),
              'value' => $this->isCurrentlyDuringBusinessHours() ? 'Yes' : 'No',
            ],
          ],
        ],
      ],
    ];

    $form['request_release'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Request Content Release'),
    ];

    $message = <<<HTML
<ul>
    <li>Refresh the page to update the Content Release Status information.</li>
    <li>Any content you set to Published will go live once the release is finished.</li>
    <li>You cannot release content manually during business hours so the release button is disabled during those times.</li>
</ul>
<hr>
HTML;

    // Add markup for a message before the form fields.
    $form['request_release']['message'] = [
      '#markup' => $message,
    ];

    $form['request_release']['acknowledgement'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I understand that all VA content set to Published will go live once the release is finished.'),
      '#required' => TRUE,
      '#disabled' => $this->isCurrentlyDuringBusinessHours(),
    ];

    $form['request_release']['actions'] = [
      '#type' => 'actions',
      '#disabled' => $this->isCurrentlyDuringBusinessHours(),
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Release Content Request'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state
  ): void {

    try {
      // Trigger the content release.
      $this->httpClient->request('POST',
        'https://api.github.com/repos/'. self::OWNER .'/'. self::REPO .'/actions/workflows/'. self::WORKFLOW_ID .'/dispatches',
        [
          'headers' => [
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => 'token ' . $this->settings->get('va_gov_content_release.github_token'),
          ],
          'json' => [
            'ref' => 'main',
            // Can add inputs to the workflow, if needed.
            // 'inputs' => [
            //   'release' => 'true',
            // ],
          ],
        ]);
    } catch (Exception $exception) {
      $this->messenger()->addError($this->t('There was an error triggering the content release.'));
      $this->logger->error('Error triggering content release: @error', ['@error' => $exception->getMessage()]);
    }
  }

}
