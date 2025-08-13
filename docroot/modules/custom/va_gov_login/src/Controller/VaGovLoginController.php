<?php

namespace Drupal\va_gov_login\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Site\Settings;

/**
 * Controller for handling Microsoft Entra ID.
 *
 * Social authentication redirects and callbacks.
 */
class VaGovLoginController extends ControllerBase {

  /**
   * HTTP client service for making external requests.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Messenger service for displaying messages.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Logger factory service for logging errors.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructs a VaGovLoginController object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   HTTP client service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory service.
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings service.
   */
  public function __construct(
    ClientInterface $http_client,
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger_factory,
    Settings $settings,
  ) {
    $this->httpClient = $http_client;
    $this->messenger = $messenger;
    $this->loggerFactory = $logger_factory;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('messenger'),
      $container->get('logger.factory'),
      $container->get('settings')
    );
  }

  /**
   * Redirects the user to the Microsoft Entra ID login page.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   A trusted redirect response to the Microsoft Entra ID login URL.
   */
  public function redirectToMicrosoft() {
    $client_id = $this->settings->get('microsoft_entra_id_client_id');
    $tenant_id = $this->settings->get('microsoft_entra_id_tenant_id');

    // Validate required Entra ID settings.
    if (empty($client_id) || empty($tenant_id)) {
      $this->loggerFactory->get('va_gov_login')->error('Missing required Microsoft Entra ID configuration: client_id or tenant_id.');
      $this->messenger->addError($this->t('Login is currently unavailable. Please contact CMS helpdesk.'));
      // Redirect to front page or another safe location.
      return $this->redirect('<front>');
    }

    // Use Url::fromRoute() to generate the redirect URI.
    $redirect_uri = Url::fromRoute('va_gov_login.callback', [], ['absolute' => TRUE])->toString();
    $scopes = 'openid profile email User.Read';

    // Construct the URL for the Microsoft login.
    $url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize?" . http_build_query([
      'client_id' => $client_id,
      'response_type' => 'code',
      'redirect_uri' => $redirect_uri,
      'scope' => $scopes,
    ]);

    // Redirect the user to Microsoft login using TrustedRedirectResponse.
    return new TrustedRedirectResponse($url);
  }

  /**
   * Handles the callback from Microsoft Entra ID after user authorization.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to the front page after processing the callback.
   */
  public function handleMicrosoftCallback(Request $request) {
    $code = $request->query->get('code');

    if ($code) {
      // Retrieve settings from environment variables.
      $client_id = $this->settings->get('microsoft_entra_id_client_id');
      $client_secret = $this->settings->get('microsoft_entra_id_client_secret');
      $tenant_id = $this->settings->get('microsoft_entra_id_tenant_id');
      $redirect_uri = Url::fromRoute('va_gov_login.callback', [], ['absolute' => TRUE])->toString();

      try {
        // Exchange the code for an access token.
        $response = $this->httpClient->request('POST', "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token", [
          'form_params' => [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code',
          ],
        ]);
        $data = json_decode($response->getBody()->getContents(), TRUE);
        // Check if access_token exists in response.
        if (isset($data['access_token'])) {
          $profile_response = $this->httpClient->request('GET', 'https://graph.microsoft.com/v1.0/me', [
            'headers' => ['Authorization' => 'Bearer ' . $data['access_token']],
          ]);
          $profile_data = json_decode($profile_response->getBody()->getContents(), TRUE);

          if (isset($profile_data['mail'])) {
            $user_email = $profile_data['mail'];

            $existing_user = user_load_by_name($user_email);

            if ($existing_user) {
              user_login_finalize($existing_user);
              $this->messenger->addStatus($this->t('Logged in successfully.'));
            }
            else {
              $this->messenger->addError($this->t('Login failed. The account does not exist.'));
              return new RedirectResponse(Url::fromRoute('<front>')->toString());
            }
          }
          else {
            throw new \Exception('User email not found.');
          }
        }
        else {
          throw new \Exception('Access token missing.');
        }
      }
      catch (\Exception $e) {
        $this->messenger->addError($this->t('Login failed. Please try again or contact support if the problem persists.'));
        $this->loggerFactory->get('va_gov_login')->error($e->getMessage());
      }
    }
    else {
      $this->messenger->addError($this->t('Authorization code missing.'));
    }

    // Redirect to the front page after handling callback or errors.
    return new RedirectResponse(Url::fromRoute('<front>')->toString());
  }

}
