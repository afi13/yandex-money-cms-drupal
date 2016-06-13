<?php

/**
 * @file
 * Contains \Drupal\yamoney\Controller\YamoneyTransactionController.
 */

namespace Drupal\yamoney\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class YamoneyTransactionController extends ControllerBase {

  public function completePage() {
    \Drupal::moduleHandler()->invokeAll('yamoney_complete');
    $config = $this->config('yamoney.settings.yamoney_fail_text');
  }

  public function failPage() {
    \Drupal::moduleHandler()->invokeAll('yamoney_fail');
    $config = $this->config('yamoney.settings');
    $text = $config->get('yamoney_fail_text');
    $page = [
      '#markup' => $text,
    ];

    return $page;
  }

  public function tempPage(Request $request) {
    $action = $request->get('action');
    if ($action == 'PaymentSuccess') {
      $route_name = 'yamoney.transaction_complete';
    }
    else {
      $route_name = 'yamoney.transaction_fail';
    }
    $this->redirect($route_name);
  }
}
