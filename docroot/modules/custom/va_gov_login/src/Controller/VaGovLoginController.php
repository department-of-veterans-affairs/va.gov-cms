<?php

namespace Drupal\va_gov_login\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
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
   * The key-value store for OAuth state.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $keyValueStore;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Disables page caching for sensitive requests.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $pageCacheKillSwitch;

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
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key-value factory service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $page_cache_kill_switch
   *   The page cache kill switch.
   */
  public function __construct(
    ClientInterface $http_client,
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger_factory,
    Settings $settings,
    KeyValueFactoryInterface $key_value_factory,
    AccountProxyInterface $current_user,
    Connection $database,
    KillSwitch $page_cache_kill_switch,
  ) {
    $this->httpClient = $http_client;
    $this->messenger = $messenger;
    $this->loggerFactory = $logger_factory;
    $this->settings = $settings;
    $this->keyValueStore = $key_value_factory->get('va_gov_oauth_state');
    $this->currentUser = $current_user;
    $this->database = $database;
    $this->pageCacheKillSwitch = $page_cache_kill_switch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('messenger'),
      $container->get('logger.factory'),
      $container->get('settings'),
      $container->get('keyvalue.database'),
      $container->get('current_user'),
      $container->get('database'),
      $container->get('page_cache_kill_switch'),
    );
  }

  /**
   * Redirects the user to the Microsoft Entra ID login page.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   A trusted redirect response to the Microsoft Entra ID login URL.
   */
  public function redirectToMicrosoft() {
    try {
      $logger = $this->loggerFactory->get('va_gov_login');

      // Ensure this request is never cached at any layer.
      $this->pageCacheKillSwitch->trigger();

      // Log immediately to verify method execution.
      $logger->info('REDIRECT: METHOD CALLED - redirectToMicrosoft() executing');

      $session_id = session_id();

      $logger->info('REDIRECT: OAuth flow initiated - User ID: @uid, Session ID: @sid, Session status: @status', [
        '@uid' => $this->currentUser->id(),
        '@sid' => $session_id ?: 'NO SESSION',
        '@status' => session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'NONE',
      ]);

      $client_id = $this->settings->get('microsoft_entra_id_client_id');
      $tenant_id = $this->settings->get('microsoft_entra_id_tenant_id');

      // Validate required Entra ID settings.
      if (empty($client_id) || empty($tenant_id)) {
        $logger->error('REDIRECT: Missing required Microsoft Entra ID configuration: client_id or tenant_id.');
        $this->messenger->addError($this->t('Login is currently unavailable. Please contact CMS helpdesk.'));
        // Redirect to front page or another safe location.
        return $this->redirect('<front>');
      }

      $logger->info('REDIRECT: Settings validated - client_id: @client_id, tenant_id: @tenant_id', [
        '@client_id' => substr($client_id, 0, 8) . '...',
        '@tenant_id' => substr($tenant_id, 0, 8) . '...',
      ]);

      // Generate CSRF state token (64 char hex) for OAuth flow protection.
      // This prevents attackers from initiating unauthorized authentication.
      $state = bin2hex(random_bytes(32));

      // Generate nonce (64 char hex) for ID token replay protection.
      // Microsoft will include this in the ID token for validation.
      $nonce = bin2hex(random_bytes(32));

      // Store state and nonce in KeyValue store with timestamp for expiration.
      // Using direct database storage for immediate persistence.
      $this->keyValueStore->set($state, [
        'nonce' => $nonce,
        'timestamp' => time(),
      ]);

      // Verify KeyValue write by reading back immediately.
      $verify_data = $this->keyValueStore->get($state);
      $logger->info('REDIRECT: Generated state: @state, nonce: @nonce | Stored and verified: @verify | Match: @match', [
        '@state' => $state,
        '@nonce' => $nonce,
        '@verify' => $verify_data ? 'YES' : 'NULL',
        '@match' => ($verify_data && $verify_data['nonce'] === $nonce) ? 'YES' : 'NO',
      ]);

      // Use Url::fromRoute() to generate the redirect URI.
      $redirect_uri = Url::fromRoute('va_gov_login.callback', [], ['absolute' => TRUE])->toString();
      $scopes = 'openid profile email User.Read';

      $logger->info('REDIRECT: Redirect URI: @uri', ['@uri' => $redirect_uri]);

      // Construct the URL for the Microsoft login.
      $url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize?" . http_build_query([
        'client_id' => $client_id,
        'response_type' => 'code',
        'redirect_uri' => $redirect_uri,
        'scope' => $scopes,
        'state' => $state,
        'nonce' => $nonce,
      ]);

      $logger->info('REDIRECT: Redirecting to Microsoft Entra ID');

      // Use TrustedRedirectResponse to allow external Microsoft domain.
      $response = new TrustedRedirectResponse($url);

      // Ensure this response is never cached.
      // OAuth flows require fresh state on every request.
      $response->setMaxAge(0);
      $response->setSharedMaxAge(0);
      $response->setPrivate();
      $response->headers->addCacheControlDirective('no-cache', TRUE);
      $response->headers->addCacheControlDirective('no-store', TRUE);
      $response->headers->addCacheControlDirective('must-revalidate', TRUE);
      $response->headers->addCacheControlDirective('max-age', 0);

      $cacheable_metadata = new CacheableMetadata();
      $cacheable_metadata->setCacheMaxAge(0);
      $cacheable_metadata->applyTo($response);

      return $response;
    }
    catch (\Exception $e) {
      // Catch any exception and log it before failing.
      $logger = $this->loggerFactory->get('va_gov_login');
      $logger->error('REDIRECT: FATAL EXCEPTION - @message | Trace: @trace', [
        '@message' => $e->getMessage(),
        '@trace' => $e->getTraceAsString(),
      ]);
      $this->messenger->addError($this->t('Login initialization failed. Please try again or contact support.'));
      return new RedirectResponse(Url::fromRoute('user.login')->toString());
    }
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
    $logger = $this->loggerFactory->get('va_gov_login');
    $session_id = session_id();
    $cookies = $request->cookies->all();

    $logger->info('CALLBACK: OAuth callback initiated - User ID: @uid, Session ID: @sid, Session status: @status, Has cookies: @cookies', [
      '@uid' => $this->currentUser->id(),
      '@sid' => $session_id ?: 'NO SESSION',
      '@status' => session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'NONE',
      '@cookies' => !empty($cookies) ? implode(', ', array_keys($cookies)) : 'NONE',
    ]);

    $code = $request->query->get('code');
    $state = $request->query->get('state');
    $error = $request->query->get('error');
    $error_description = $request->query->get('error_description');

    if ($error) {
      $logger->error('CALLBACK: Microsoft returned error: @error - @desc', [
        '@error' => $error,
        '@desc' => $error_description ?? 'No description',
      ]);
      $this->messenger->addError($this->t('Authentication failed: @error', ['@error' => $error_description ?? $error]));
      return new RedirectResponse(Url::fromRoute('user.login')->toString());
    }

    $logger->info('CALLBACK: Received code: @code (length: @len), state: @state', [
      '@code' => substr($code, 0, 10) . '...',
      '@len' => strlen($code ?? ''),
      '@state' => $state ?? 'NULL',
    ]);

    // SECURITY: Validate state parameter to prevent CSRF attacks.
    // Use state token from URL as key to retrieve stored OAuth data.
    $stored_data = $this->keyValueStore->get($state);

    // Check database for KeyValue entries to verify persistence.
    $db_check = $this->database->query(
      "SELECT COUNT(*) FROM {key_value} WHERE collection = 'va_gov_oauth_state'"
    )->fetchField();

    $logger->info('CALLBACK: State from URL: @state | Stored data found: @found | State DB entries: @count | Timestamp: @ts', [
      '@state' => $state ?? 'NULL',
      '@found' => $stored_data ? 'YES' : 'NO',
      '@count' => $db_check,
      '@ts' => $stored_data['timestamp'] ?? 'N/A',
    ]);

    // Validate state exists and hasn't expired (10 minute window).
    $state_valid = !empty($stored_data) &&
                   !empty($stored_data['timestamp']) &&
                   ($stored_data['timestamp'] > time() - 600);

    if (!$state_valid) {
      $age = !empty($stored_data['timestamp']) ? (time() - $stored_data['timestamp']) : 'N/A';
      $logger->warning('CALLBACK: CSRF/expired state detected - State: @state, Found: @found, Age: @age seconds', [
        '@state' => $state ?? 'NULL',
        '@found' => $stored_data ? 'YES (expired)' : 'NO',
        '@age' => $age,
      ]);
      $this->messenger->addError($this->t('Invalid or expired state parameter. Please try logging in again.'));
      if ($stored_data) {
        $this->keyValueStore->delete($state);
      }
      $response = new RedirectResponse(Url::fromRoute('user.login')->toString());
      $response->setMaxAge(0);
      $response->headers->addCacheControlDirective('no-cache', TRUE);
      return $response;
    }

    $logger->info('CALLBACK: State validation PASSED - Age: @age seconds', [
      '@age' => time() - $stored_data['timestamp'],
    ]);

    // Store nonce for later validation.
    $stored_nonce = $stored_data['nonce'];

    // Delete state immediately after validation (one-time use).
    $this->keyValueStore->delete($state);

    if ($code) {
      $logger->info('CALLBACK: Authorization code present, exchanging for tokens');

      // Retrieve settings from environment variables.
      $client_id = $this->settings->get('microsoft_entra_id_client_id');
      $client_secret = $this->settings->get('microsoft_entra_id_client_secret');
      $tenant_id = $this->settings->get('microsoft_entra_id_tenant_id');
      $redirect_uri = Url::fromRoute('va_gov_login.callback', [], ['absolute' => TRUE])->toString();

      try {
        $logger->info('CALLBACK: Requesting tokens from Microsoft');

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

        $logger->info('CALLBACK: Token response received - has_access_token: @access, has_id_token: @id', [
          '@access' => isset($data['access_token']) ? 'YES' : 'NO',
          '@id' => isset($data['id_token']) ? 'YES' : 'NO',
        ]);

        // Check if access_token and id_token exist in response.
        if (isset($data['access_token']) && isset($data['id_token'])) {
          $logger->info('CALLBACK: Validating ID token');
          // Parse and validate JWT ID token structure.
          // JWT format: base64(header).base64(payload).base64(signature)
          $id_token_parts = explode('.', $data['id_token']);
          if (count($id_token_parts) !== 3) {
            throw new \Exception('Invalid ID token format.');
          }

          // Decode the JWT payload (middle section).
          // Uses URL-safe base64 decoding (-_ instead of +/).
          $id_token_payload = json_decode(base64_decode(strtr($id_token_parts[1], '-_', '+/')), TRUE);

          if (!$id_token_payload) {
            throw new \Exception('Failed to decode ID token payload.');
          }

          // Validate critical ID token claims for security.
          // SECURITY: These validations prevent token forgery and misuse.
          // Verify audience (aud) claim matches our client_id.
          // Prevents tokens issued for other apps from being accepted.
          if (empty($id_token_payload['aud']) || $id_token_payload['aud'] !== $client_id) {
            throw new \Exception('ID token audience mismatch.');
          }

          // Verify issuer (iss) claim is from Microsoft.
          // Prevents tokens from malicious issuers.
          // Expected issuer for an organization account:
          // microsoft.com/{tenant_id}/v2.0 .
          if (empty($id_token_payload['iss'])) {
            throw new \Exception('ID token issuer missing.');
          }
          // For organization accounts, validate exact tenant match.
          $expected_issuer = "https://login.microsoftonline.com/$tenant_id/v2.0";
          if ($id_token_payload['iss'] !== $expected_issuer) {
            throw new \Exception('ID token issuer mismatch.');
          }

          // Verify token expiration (exp) claim.
          // Prevents use of old/expired tokens.
          if (empty($id_token_payload['exp']) || $id_token_payload['exp'] < time()) {
            throw new \Exception('ID token has expired.');
          }

          // Verify nonce to prevent token replay attacks.
          // Nonce must match what we sent in authorization request.
          if (!empty($stored_nonce)) {
            if (empty($id_token_payload['nonce']) || $id_token_payload['nonce'] !== $stored_nonce) {
              $logger->error('CALLBACK: Nonce mismatch - Expected: @expected, Received: @received', [
                '@expected' => $stored_nonce,
                '@received' => $id_token_payload['nonce'] ?? 'NULL',
              ]);
              throw new \Exception('ID token nonce mismatch.');
            }
            $logger->info('CALLBACK: Nonce validation PASSED');
          }

          $logger->info('CALLBACK: ID token validated, fetching user profile from Microsoft Graph');

          $profile_response = $this->httpClient->request('GET', 'https://graph.microsoft.com/v1.0/me', [
            'headers' => ['Authorization' => 'Bearer ' . $data['access_token']],
          ]);
          $profile_data = json_decode($profile_response->getBody()->getContents(), TRUE);

          if (isset($profile_data['mail'])) {
            $user_email = $profile_data['mail'];
            $logger->info('CALLBACK: Retrieved user email: @email', ['@email' => $user_email]);

            $existing_user = user_load_by_name($user_email);
            $logger->info('CALLBACK: User lookup result: @found', [
              '@found' => $existing_user ? 'FOUND (uid: ' . $existing_user->id() . ')' : 'NOT FOUND',
            ]);

            if ($existing_user) {

              // Block user 1 from logging in via Entra ID.
              if ($existing_user->id() == 1) {
                $logger->warning('CALLBACK: Blocked user 1 login attempt for email: @email, IP: @ip', [
                  '@email' => $user_email,
                  '@ip' => $request->getClientIp(),
                ]);
                $this->messenger->addError($this->t('The root administrator account cannot log in via Entra ID.'));
                $response = new RedirectResponse(Url::fromRoute('user.login')->toString());
                $response->setMaxAge(0);
                $response->headers->addCacheControlDirective('no-cache', TRUE);
                return $response;
              }

              $logger->info('CALLBACK: Logging in user: @email (uid: @uid)', [
                '@email' => $user_email,
                '@uid' => $existing_user->id(),
              ]);
              user_login_finalize($existing_user);
              $logger->info('CALLBACK: Login successful for @email', ['@email' => $user_email]);
              $this->messenger->addStatus($this->t('Logged in successfully.'));
            }
            else {
              $logger->error('CALLBACK: User account does not exist for email: @email', ['@email' => $user_email]);
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
        $logger->error('CALLBACK: Exception during OAuth flow: @message | Trace: @trace', [
          '@message' => $e->getMessage(),
          '@trace' => $e->getTraceAsString(),
        ]);
        $this->messenger->addError($this->t('Login failed. Please try again or contact support if the problem persists.'));
      }
    }
    else {
      $logger->error('CALLBACK: Authorization code missing from request');
      $this->messenger->addError($this->t('Authorization code missing.'));
    }

    // Redirect to the front page after handling callback or errors.
    return new RedirectResponse(Url::fromRoute('<front>')->toString());
  }

}
