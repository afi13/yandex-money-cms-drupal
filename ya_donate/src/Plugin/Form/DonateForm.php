<?php

namespace Drupal\ya_donate\Plugin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamoney\YamoneyService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DonateForm extends FormBase {

  /**
   * Constructs a new DonateForm.
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
  public function getFormId() {
    return 'ya_donate_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $options = []) {
    $form = [];

    $form['#action'] = $this->yamoney->getQuickpayUrl();

    $form['text'] = [
      '#markup' => $options['donate_text'],
    ];

    if (isset($options['amount'])) {
      $form['sum'] = [
        '#type' => 'hidden',
        '#value' => $options['amount'],
      ];
    }
    else {
      $form['sum'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Donate amount'),
        '#description' => $this->t('Enter the amount of the donation'),
        '#size' => 20,
      ];
    }

    $form['receiver'] = [
      '#type' => 'hidden',
      '#value' => $options['receiver'],
    ];

    $form['quickpay-form'] = [
      '#type' => 'hidden',
      '#value' => 'shop',
    ];

    $form['targets'] = [
      '#type' => 'hidden',
      '#value' => $this->t('Donate payment'),
    ];

    $form['payment-type'] = [
      '#type' => 'hidden',
      '#value' => 'PC',
    ];

    $form['comment'] = [
      '#type' => 'hidden',
      '#value' => $options['donate_text'],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Donate',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
