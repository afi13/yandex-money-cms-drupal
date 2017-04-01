<?php

namespace Drupal\yamoney\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamoney\YamoneyService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides form for yamoney module settings.
 */
class YamoneySettingsForm extends ConfigFormBase {

  /**
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
   * @inheritdoc
   */
  public function getFormId() {
    return 'yamoney_settings';
  }

  /**
   * @inheritdoc
   */
  protected function getEditableConfigNames() {
    return ['yamoney.settings'];
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('yamoney.settings');

    $form = [];

    // General settings
    $form['yamoney_all'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Base options'),
      '#description' => $this->t('Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу <a href="https://money.yandex.ru/doc.xml?id=527132">https://money.yandex.ru/doc.xml?id=527132</a> (далее – «Лицензионный договор»). Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.')
    ];

    $form['yamoney_all']['yamoney_ip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed IPs for callbacks'),
      '#default_value' => $config->get('yamoney_ip'),
      '#description' => $this->t('The list of IP addresses which has access to payment callbacks. One per line.<br/>0.0.0.0 means allow from all.')
    ];

    $form['yamoney_all']['yamoney_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Payment mode'),
      '#options' => [
        'test' => $this->t('Test mode'),
        'live' => $this->t('Production mode'),
      ],
      '#default_value' => $config->get('yamoney_mode'),
    ];

    $form['yamoney_all']['yamoney_payment_method'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enabled payment methods'),
      '#options' => $this->yamoney->getPaymentMethods(),
      '#default_value' => $config->get('yamoney_payment_method'),
    ];

    $form['yamoney_all']['yamoney_default_payment_method'] = [
      '#type' => 'radios',
      '#title' => t('Default payment method'),
      '#options' => $this->yamoney->getPaymentMethods(),
      '#default_value' => $config->get('yamoney_default_payment_method'),
    ];

    // Shop settings
    $shop_states = [
      // Hide the settings when the Yandex shop checkbox is disabled.
      'invisible' => [
        ':input[name="yamoney_shop"]' => ['checked' => FALSE],
      ],
    ];

    $form['yamoney_shop_setting'] = [
      '#type' => 'fieldset',
      '#title' => t('Yandex shop settings'),
    ];

    $form['yamoney_shop_setting']['yamoney_shop'] = [
      '#type' => 'checkbox',
      '#title' => t('Select if you have a shop in Yandex.Money'),
      '#default_value' => $config->get('yamoney_shop'),
    ];

    $form['yamoney_shop_setting']['yamoney_shop_id'] = [
      '#type' => 'textfield',
      '#title' => t('Shop ID'),
      '#description' => t('You shop ID. If you have any shops in you Yandex account.'),
      '#default_value' => $config->get('yamoney_shop_id'),
      '#size' => 2,
      '#states' => $shop_states,
    ];

    $form['yamoney_shop_setting']['yamoney_scid'] = [
      '#type' => 'textfield',
      '#title' => t('Shop SCID'),
      '#default_value' => $config->get('yamoney_scid'),
      '#size' => 5,
      '#states' => $shop_states,
    ];

    $form['yamoney_shop_setting']['yamoney_secret'] = [
      '#type' => 'textfield',
      '#title' => t('Shop security key'),
      '#description' => t('You shop password security key. Not you payment password.'),
      '#default_value' => $config->get('yamoney_secret'),
      '#size' => 17,
      '#states' => $shop_states,
    ];

    // Purse settings
    $form['yamoney_purse_setting'] = [
      '#type' => 'fieldset',
      '#title' => t('Yandex purse settings'),
    ];

    $form['yamoney_purse_setting']['yamoney_receiver'] = [
      '#type' => 'textfield',
      '#title' => t('Purse number'),
      '#description' => t('Your Yandex.Money purse number.'),
      '#default_value' => $config->get('yamoney_receiver'),
      '#size' => 14,
    ];

    $form['yamoney_purse_setting']['yamoney_formcomment'] = [
      '#type' => 'textfield',
      '#title' => t('Pay comment'),
      '#description' => t('Your Yandex.Money pay comment.'),
      '#default_value' => $config->get('yamoney_formcomment'),
    ];

    $form['yamoney_texts'] = [
      '#type' => 'fieldset',
      '#title' => t('Text for success and fail payment pages'),
    ];

    $success = $config->get('yamoney_formcomment');
    $form['yamoney_texts']['yamoney_success_text'] = [
      '#type' => 'text_format',
      '#title' => t('Text for success page'),
      '#default_value' => $success,
      '#format' => $success['format'] ? $success['format'] : 'restricted_html',
    ];

    $fail = $config->get('yamoney_fail_text');
    $form['yamoney_texts']['yamoney_fail_text'] = [
      '#type' => 'text_format',
      '#title' => t('Text for fail page'),
      '#default_value' =>$fail,
      '#format' => $fail['format'] ? $fail['format'] : 'restricted_html',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $payment_method = [];
    foreach ($values['yamoney_payment_method'] as $method) {
      if ($method) {
        $payment_method[] = $method;
      }
    }

    $this->config('yamoney.settings')
      ->set('yamoney_ip', $values['yamoney_ip'])
      ->set('yamoney_payment_method', $payment_method)
      ->set('yamoney_default_payment_method', $values['yamoney_default_payment_method'])
      ->set('yamoney_shop', $values['yamoney_shop'])
      ->set('yamoney_shop_id', $values['yamoney_shop_id'])
      ->set('yamoney_scid', $values['yamoney_scid'])
      ->set('yamoney_secret', $values['yamoney_secret'])
      ->set('yamoney_receiver', $values['yamoney_receiver'])
      ->set('yamoney_formcomment', $values['yamoney_formcomment'])
      ->set('yamoney_success_text', $values['yamoney_success_text']['value'])
      ->set('yamoney_fail_text', $values['yamoney_fail_text']['value'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
