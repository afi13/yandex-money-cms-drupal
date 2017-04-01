<?php

namespace Drupal\yamoney\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\yamoney\Entity\YAMoneyTransaction;
use Drupal\yamoney\YamoneyService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class YamoneyOrderController extends ControllerBase {

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
   * Yamoney service.
   *
   * @var \Drupal\yamoney\YamoneyService
   */
  protected $yamoney;

  /**
   * Constructs a new YamoneyOrderController.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(ModuleHandlerInterface $module_handler, YamoneyService $yamoney) {
    $this->moduleHandler = $module_handler;
    $this->yamoney = $yamoney;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('yamoney.payment_service')
    );
  }

  public function checkOrderPage(Request $request) {
    $access = $request->get('action');

    if (!isset($access) || $access !== 'paymentAviso') {
      $this->checkSendResult('paymentAvisoResponse', YAMONEY_CHECK_RESULT_CODE_ERROR_REQUEST, $this->t('Invalid action. Expected action: paymentAviso.'));
    }

    if (!isset($_POST['md5']) || $_POST['md5'] !== $this->yamoney->md5($_POST)) {
      $this->checkSendResult('paymentAvisoResponse', YAMONEY_CHECK_RESULT_CODE_ERROR_MD5);
    }

    if (!isset($_POST['transaction_id'])) {
      $this->checkSendResult('paymentAvisoResponse', YAMONEY_CHECK_RESULT_CODE_ERROR_CUSTOM, $this->t('Invalid transaction_id provided.'));
    }

    $transaction = yamoney_transaction_load($_POST['transaction_id']);
    if (!$transaction) {
      $this->checkSendResult('paymentAvisoResponse', YAMONEY_CHECK_RESULT_CODE_ERROR_CUSTOM, $this->t('Invalid transaction_id provided.'));
    }

    if ($transaction->status !== YAMoneyTransaction::STATUS_PROCESSED && $transaction->status !== YAMoneyTransaction::STATUS_PAYED) {
      $this->checkSendResult('paymentAvisoResponse', YAMONEY_CHECK_RESULT_CODE_ERROR_CUSTOM,
        'Invalid transaction state: ' . $transaction->status . '. Expected: ' . YAMoneyTransaction::STATUS_PROCESSED . '.');
    }

    $payment = array(
      'success' => TRUE,
      'transaction' => $transaction,
      'request' => $_POST,
    );
    $this->moduleHandler->alter('yamoney_process_payment', $payment);

    if (!$payment['success']) {
      $error = isset($payment['error']) ? $payment['error'] : 'Can not process transaction.';
      $this->checkSendResult('paymentAvisoResponse', YAMONEY_CHECK_RESULT_CODE_ERROR_CUSTOM, $error);
    }

    if ($transaction->status === YAMoneyTransaction::STATUS_PAYED || yamoney_update_transaction_status($transaction->ymid, YAMoneyTransaction::STATUS_PAYED)) {
      $this->checkSendResult('paymentAvisoResponse');
    }
    else {
      $this->checkSendResult('paymentAvisoResponse', YAMONEY_CHECK_RESULT_CODE_ERROR_CUSTOM, 'Can not save transaction.');
    }
  }

  /**
   * @param string $type
   * @param int $code
   * @param string $message
   */
  public function checkSendResult($type, $code = 0, $message = '') {
    $attributes = array(
      'performedDatetime' => $_POST['requestDatetime'],
      'code' => $code,
      'invoiceId' => $_POST['invoiceId'],
      'shopId' => $_POST['shopId'],
    );

    if ($message) {
      $attributes['message'] = $message;
    }

    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<' . $type . ' ' . drupal_attributes($attributes) . ' />';

    drupal_exit();
  }

}
