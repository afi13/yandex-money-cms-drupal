<?php

namespace Drupal\ya_ubercart\Plugin\Ubercart\PaymentMethod;

use Drupal\Core\Form\FormStateInterface;
use Drupal\uc_order\OrderInterface;
use Drupal\uc_payment\OffsitePaymentMethodPluginInterface;
use Drupal\uc_payment\PaymentMethodPluginBase;
use Drupal\yamoney\YamoneyService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the Yandex money payment method.
 *
 * @UbercartPaymentMethod(
 *   id = "yamoney",
 *   name = @Translation("Yandex Money"),
 * )
 */
class YamoneyPaymentMethod extends PaymentMethodPluginBase implements OffsitePaymentMethodPluginInterface {

  /**
   * Yamoney service.
   *
   * @var \Drupal\yamoney\YamoneyService
   */
  protected $yamoney;

  /**
   * Constructs a new YamoneySettingsForm.
   *
   * @param \Drupal\yamoney\YamoneyService $yamoney
   */
  public function __construct(YamoneyService $yamoney) {
    $this->yamoney = $yamoney;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamoney.payment_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayLabel($label) {
    $logo_path = drupal_get_path('module', 'yamoney') . '/images/logo.png';
    $build['logo'] = [
      '#theme' => 'image',
      '#uri' => $logo_path,
      '#alt' => $this->t('Yandex.Kassa'),
      '#attributes' => [
        'class' => [
          'uc-credit-cctype', 'uc-credit-cctype-yamoney'
        ]
      ],
    ];
    $build['label'] = [
      '#prefix' => ' ',
      '#plain_text' => $this->t('Yandex.Kassa.'),
      '#suffix' => '<br /> ',
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'server' => YamoneyService::TEST_ESHOP_URL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $form['shopId'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Shop ID'),
      '#description' => $this->t('Идентификатор магазина, выдается при подключении к Яндекс.Кассе.'),
      '#default_value' => $this->configuration['shopId'],
      '#size' => 2,
    ];

    $form['scid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Shop SCID'),
      '#description' => $this->t('Номер витрины магазина, выдается при подключении к Яндекс.Кассе.'),
      '#default_value' => $this->configuration['scid'],
      '#size' => 2,
    ];

    $form['server'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Yandex.Kassa server'),
      '#description' => $this->t('Action URL.'),
      '#options' => [
        YamoneyService::TEST_ESHOP_URL => ('Sandbox'),
        YamoneyService::PRODUCTION_ESHOP_URL => ('Live'),
      ],
      '#default_value' => $this->configuration['server'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['wps_email'] = trim($form_state->getValue('wps_email'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildRedirectForm(array $form, FormStateInterface $form_state, OrderInterface $order) {
    $form = [];

    $form['#action'] = $this->configuration['server'];

    $data = [
      'shopId' => $this->configuration['shopId'],
      'scid' => $this->configuration['scid'],
      'sum' => $order->getTotal(),
      'customerNumber' => $order->getOwnerId(),
      'orderNumber' => $order->id(),
      'cps_email' => $order->getEmail(),
      'custEmail' => $order->getEmail(),
      'paymentType' => '',
    ];

    foreach ($data as $name => $value) {
      $form[$name] = ['#type' => 'hidden', '#value' => $value];
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit order'),
    ];

    return $form;
  }

}
