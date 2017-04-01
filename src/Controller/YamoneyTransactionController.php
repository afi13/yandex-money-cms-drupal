<?php

namespace Drupal\yamoney\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\yamoney\YamoneyEvent;
use Drupal\yamoney\YamoneyEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for the transactions pages.
 */
class YamoneyTransactionController extends ControllerBase {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Yamoney event.
   *
   * @var \Drupal\yamoney\YamoneyEvent
   */
  protected $event;

  /**
   * Constructs a new YamoneyTransactionController.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(EventDispatcherInterface $event_dispatcher, ModuleHandlerInterface $module_handler) {
    $this->eventDispatcher = $event_dispatcher;
    $this->moduleHandler = $module_handler;
    $this->event = new YamoneyEvent();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_dispatcher'),
      $container->get('module_handler')
    );
  }

  /**
   * @return array
   */
  public function completePage() {
    $this->eventDispatcher->dispatch(YamoneyEvents::SUCCESS, $this->event);

    $config = $this->config('yamoney.settings');
    $text = $config->get('yamoney_success_text');
    $page = [
      '#markup' => $text,
    ];

    $this->moduleHandler->alter('yamoney_complete_page', $page);

    return $page;
  }

  /**
   * @return array
   */
  public function failPage() {
    $this->eventDispatcher->dispatch(YamoneyEvents::FAIL, $this->event);
    $config = $this->config('yamoney.settings');
    $text = $config->get('yamoney_fail_text');
    $page = [
      '#markup' => $text,
    ];
    $this->moduleHandler->alter('yamoney_fail_page', $page);

    return $page;
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  public function tempPage(Request $request) {
    $action = $request->get('action');
    $route = ($action == 'PaymentSuccess') ? 'transaction_complete' : 'transaction_fail';
    $this->redirect('yamoney.' . $route);
  }

}
