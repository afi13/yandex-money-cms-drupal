<?php

namespace Drupal\yamoney\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides form for yamoney module settings.
 */
class YamapsSettingsForm extends ConfigFormBase {

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
      '#options' => yamoney_get_payment_methods(),
      '#default_value' => $config->get('yamoney_payment_method'),
    ];

    $form['yamoney_all']['yamoney_default_payment_method'] = [
      '#type' => 'radios',
      '#title' => t('Default payment method'),
      '#options' => yamoney_get_payment_methods(),
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
      '#default_value' => $success['value'] ? $success['value'] : '',
      '#format' => $success['format'] ? $success['format'] : '',
    ];

    $fail = $config->get('yamoney_fail_text');
    $form['yamoney_texts']['yamoney_fail_text'] = [
      '#type' => 'text_format',
      '#title' => t('Text for fail page'),
      '#default_value' => $fail['value'] ? $fail['value'] : '',
      '#format' => $fail['format'] ? $fail['format'] : '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    parent::submitForm($form, $form_state);
  }
}
