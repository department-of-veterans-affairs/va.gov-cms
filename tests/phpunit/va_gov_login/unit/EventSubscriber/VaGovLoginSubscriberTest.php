<?php

namespace Drupal\Tests\va_gov_login\unit\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\va_gov_login\EventSubscriber\VaGovLoginSubscriber;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\Container;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test for the va_gov_login event subscriber.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_login\EventSubscriber\VaGovLoginSubscriber
 */
class VaGovLoginSubscriberTest extends VaGovUnitTestBase {

  /**
   * {@inheritDoc}
   */
  public function setUp() : void {
    parent::setUp();
    $container = new Container();
    \Drupal::setContainer($container);
  }

  /**
   * Make sure anonymous user is redirected on 403.
   */
  public function testOn403RedirectsAnonymousUser() {
    // Mock AccountInterface to simulate anonymous user.
    $account = $this->createMock(AccountInterface::class);
    $account->method('isAnonymous')->willReturn(TRUE);

    $subscriber = new VaGovLoginSubscriber($account);

    // Mock ExceptionEvent.
    $kernel = $this->createMock(HttpKernelInterface::class);
    $request = new Request();
    $exception = new \Exception('Forbidden');
    $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

    // Call the method.
    $subscriber->on403($event);

    // Assert that the response is a redirect to <front>.
    $response = $event->getResponse();
    $this->assertInstanceOf(Response::class, $response);
    $this->assertTrue($response->isRedirect());
    $this->assertStringContainsString('/', $response->headers->get('Location'));
  }

  /**
   * Make sure authenticated user is not redirected on 403.
   */
  public function testOn403DoesNotRedirectAuthenticatedUser() {
    // Mock AccountInterface to simulate authenticated user.
    $account = $this->createMock(AccountInterface::class);
    $account->method('isAnonymous')->willReturn(FALSE);

    $subscriber = new VaGovLoginSubscriber($account);

    // Mock ExceptionEvent.
    $kernel = $this->createMock(HttpKernelInterface::class);
    $request = new Request();
    $exception = new \Exception('Forbidden');
    $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

    // Call the method.
    $subscriber->on403($event);

    // Assert that the response is still null (no redirect).
    $this->assertNull($event->getResponse());
  }

}
