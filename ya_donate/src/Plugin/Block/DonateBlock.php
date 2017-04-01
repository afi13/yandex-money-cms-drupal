<?php

namespace Drupal\ya_donate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\yamoney\YamoneyService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Yandex Money Donate' block.
 *
 * @Block(
 *  id = "ya_donate_block",
 *  admin_label = @Translation("Yandex money donate block"),
 * )
 */
class DonateBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Yandex money service.
   *
   * @var \Drupal\yamoney\YamoneyService
   */
  protected $yamoney;

  /**
   * Constructs a \Drupal\ush_search\Plugin\Block\SearchBlockBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $entity_form_builder
   *   The form builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $entity_form_builder, YamoneyService $yamoney) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $entity_form_builder;
    $this->yamoney = $yamoney;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('form_builder'),
      $container->get('yamoney.payment_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();
    $options += [
      'donate_text' => $this->t('Donate'),
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $options = [
      'donate_text' => $this->configuration['donate_text'],
      'receiver' => $this->yamoney->getReceiver()
    ];

    if (!empty($this->configuration['amount'])) {
      $options['amount'] = $this->configuration['amount'];
    }

    $form = $this->formBuilder->getForm('Drupal\ya_donate\Plugin\Form\DonateForm', $options);
    $build['content'] = $form;

    $build['#cache'] = [
      'max-age' => 0,
    ];


    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['donate_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Donate text'),
      '#default_value' => $this->configuration['donate_text'],
      '#require' => TRUE,
    ];

    $form['amount'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Donate amount'),
      '#description' => t('Leave blank if the amount entered by the user.'),
      '#default_value' => $this->configuration['amount'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['donate_text'] = $form_state->getValue('donate_text');
    $this->configuration['amount'] = $form_state->getValue('amount');
    parent::blockSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if (!empty($form_state->getValue('amount')) && !is_numeric($form_state->getValue('amount'))) {
      $form_state->setErrorByName('amount', $this->t('This field is not numeric!!!'));
    }

    parent::blockValidate($form, $form_state);
  }

}
