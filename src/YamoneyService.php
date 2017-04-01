<?php

namespace Drupal\yamoney;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\yamoney\Entity\YAMoneyTransaction;
use Symfony\Component\DependencyInjection\ContainerInterface;

class YamoneyService {
  use StringTranslationTrait;

  const TEST_ESHOP_URL = 'https://demomoney.yandex.ru/eshop.xml';
  const PRODUCTION_ESHOP_URL = 'https://money.yandex.ru/eshop.xml';
  const TEST_QUICKPAY_URL = 'https://demomoney.yandex.ru/quickpay/confirm.xml';
  const PRODUCTION_QUICKPAY_URL = 'https://money.yandex.ru/quickpay/confirm.xml';

  protected $config;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new YamoneyService.
   */
  public function __construct() {
    $this->config = $this->config('yamoney.settings');
    $this->moduleHandler = \Drupal::moduleHandler();
  }

  /**
   * @return string
   */
  public function getOrderSubmissionUrl() {
    $shop = $this->config->get('yamoney_shop');

    if ($shop) {
      // shop
      $url = ($this->isTest()) ? self::TEST_ESHOP_URL : self::PRODUCTION_ESHOP_URL;
      return $url;
    }
    else {
      // quick
      return $this->getQuickpayUrl();
    }
  }

  /**
   * @return string
   */
  public function getQuickpayUrl() {
    return ($this->isTest()) ? self::TEST_QUICKPAY_URL : self::PRODUCTION_QUICKPAY_URL;
  }

  /**
   * @return array|mixed|null
   */
  public function getReceiver() {
    return $this->config->get('yamoney_receiver');
  }

  /**
   * @return bool
   */
  public function isTest() {
    return $this->config->get('yamoney_mode') === 'test';
  }

  /**
   * @return array
   */
  public function getEnabledPaymentMethods() {
    $payments = [];

    $all_payments = $this->config->get('yamoney_payment_method');
    if (empty($all_payments)) {
      $all_payments = array_keys($this->getPaymentMethods());
    }

    foreach ($all_payments as $key => $label) {
      if (isset($enabled_payments[$key]) && $enabled_payments[$key] === $key) {
        $payments[$key] = $label;
      }
    }

    return $payments;
  }

  /**
   * @param YAMoneyTransaction $transaction
   * @return array
   */
  public function getOrderSubmissionParams(YAMoneyTransaction $transaction) {
    if ($this->config->get('yamoney_shop')) {
      // shop
      $params = $this->getShopParams($transaction);
    }
    else {
      $params = $this->getQuickParams($transaction);
    }

    $this->moduleHandler->alter('yamoney_order_submission_params', $params);

    return $params;
  }

  public function getShopParams(YAMoneyTransaction $transaction) {
    $params = [];

    // Идентификатор Контрагента
    $params['shopId'] = $this->config->get('yamoney_shop_id');
    // Номер витрины Контрагента
    $params['scid'] = $this->config->get('yamoney_scid');
    // Сумма заказа
    $params['sum'] = $transaction->amount;
    // Идентификатор плательщика. Номер оплачиваемого мобильного телефона, договора и т. п., специфично для Контрагента.
    $params['customerNumber'] = $transaction->uid;
    // Уникальный для данного shopId номер заказа в ИС Контрагента.
    $params['orderNumber'] = $transaction->order_id;
    // URL, на который должен быть осуществлен редирект в случае успеха перевода (urlencoded значение).
    $params['shopSuccessURL'] = Url::fromRoute('yamoney.transaction_complete')
      ->setAbsolute()
      ->toString();
    // URL, на который должен быть осуществлен редирект в случае ошибки (urlencoded значение).
    $params['shopFailURL'] = Url::fromRoute('yamoney.transaction_fail')
      ->setAbsolute()
      ->toString();
    // Детали способа совершения платежа.
    $params['paymentType'] = $this->config->get('yamoney_default_payment_method');
    // Provide CMS name
    $params['cms_name'] = 'drupal';
    // Internally used field
    $params['order_id'] = $transaction->order_id;
    // Internally used field
    $params['transaction_id'] = $transaction->ymid;

    $this->moduleHandler->alter('yamoney_shop_params', $params);

    return $params;
  }

  /**
   * @param YAMoneyTransaction $transaction
   * @return array
   */
  public function getQuickParams(YAMoneyTransaction $transaction) {
    $params = [];

    $params['receiver'] = $this->config->get('yamoney_receiver');
    $params['formcomment'] = $this->config->get('yamoney_formcomment');
    $params['short-dest'] = $this->t('Payments for order No') . $transaction->order_id;
    $params['writable-targets'] = FALSE;
    $params['comment-needed'] = FALSE;
    $params['label'] = $transaction->order_id;
    $params['targets'] = $this->t('Payments for order No') . $transaction->order_id;
    $params['sum'] = $transaction->amount;
    $params['quickpay-form'] = 'shop';
    $params['paymentType'] = $this->config->get('yamoney_default_payment_method');
    $params['cms_name'] = 'drupal';

    $this->moduleHandler->alter('yamoney_quick_params', $params);

    return $params;
  }

  /**
   * @param array $params
   * @return string
   */
  function md5($params = []) {
    $secret = $this->config->get('yamoney_secret', '');
    if (!empty($params)) {
      $output = '';
      $output .= $params['action'];
      $output .= ';' . $params['orderSumAmount'];
      $output .= ';' . $params['orderSumCurrencyPaycash'];
      $output .= ';' . $params['orderSumBankPaycash'];
      $output .= ';' . $params['shopId'];
      $output .= ';' . $params['invoiceId'];
      $output .= ';' . $params['customerNumber'];
      $md5 = md5($output . ';' . $secret);
      return  Unicode::strtoupper($md5);
    }
    else {
      return '';
    }
  }

  /**
   * @return array
   */
  public function getPaymentMethods() {
    return [
      'PC' => $this->t('Payment from a Yandex.Money e-wallet'),
      'AC' => $this->t('Payment by any bank card'),
      'GP' => $this->t('Payment in cash via retailers and payment kiosks'),
      'MC' => $this->t('Payment from a mobile phone balance'),
      'WM' => $this->t('Payment from a WebMoney e-wallet'),
      'AB' => $this->t('Payment via Alfa-Click'),
      'SB' => $this->t('Payment via Sberbank: payment by text messages or Sberbank Online'),
      'MA' => $this->t('Payment via MasterPass'),
      'PB' => $this->t('Payment via Promsvyazbank'),
      'QW' => $this->t('Payment via QIWI Wallet'),
      'QP' => $this->t('Trust payment (Qppi.ru)'),
    ];
  }

  /**
   * Retrieves a configuration object.
   *
   * This is the main entry point to the configuration API. Calling
   * @code $this->config('book.admin') @endcode will return a configuration
   * object in which the book module can store its administrative settings.
   *
   * @param string $name
   *   The name of the configuration object to retrieve. The name corresponds to
   *   a configuration file. For @code \Drupal::config('book.admin') @endcode,
   *   the config object returned will contain the contents of book.admin
   *   configuration file.
   *
   * @return \Drupal\Core\Config\Config
   *   A configuration object.
   */
  protected function config($name) {
    if (!$this->configFactory) {
      $this->configFactory = \Drupal::getContainer()->get('config.factory');
    }
    return $this->configFactory->get($name);
  }

}
