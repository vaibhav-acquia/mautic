<?php

namespace Drupal\mautic_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mautic_api\MauticApiService;

/**
 * Provides a 'Mautic Form' Block.
 *
 * @Block(
 *   id = "mautic_form",
 *   admin_label = @Translation("Mautic Form"),
 *   category = @Translation("Mautic"),
 * )
 */
class MauticFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\mautic_api\MauticApiService definition.
   *
   * @var \Drupal\mautic_api\MauticApiService
   */
  protected $mauticApiService;

  /**
   * Mautic API endpoint variable.
   *
   * Variable to store the Mautic API endpoint that we are using
   * to manage what items we are listing.
   * Examples: forms, focus, segments, files.
   *
   * @var string
   */
  protected $endpoint = 'forms';

  /**
   * MauticFormBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\mautic_api\MauticApiService $mautic_api_service
   *   The Mautic API service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MauticApiService $mautic_api_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mauticApiService = $mautic_api_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mautic_api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['mautic_id'] = [
      '#type' => 'select',
      '#title' => t('Mautic form block'),
      '#description' => t('Select the Mautic form to embed.'),
      '#default_value' => isset($config['mautic_id']) ? $config['mautic_id'] : '1',
      '#options' => $this->buildSelectList(),
      '#required' => TRUE,
    ];
    $form['embed'] = [
      '#type' => 'select',
      '#title' => t('Embed method'),
      '#description' => t('Choose whether to embed this with JS or as an iframe.'),
      '#default_value' => isset($config['embed']) ? $config['embed'] : 'js',
      '#options' => [
        'js' => 'Javascript',
        'iframe' => 'Iframe',
      ],
      '#required' => TRUE,
    ];
    $form['iframe'] = [
      '#type' => 'details',
      '#title' => t('Iframe settings'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="settings[embed]"]' => ['value' => 'iframe'],
        ],
      ],
    ];
    $form['iframe']['iframe_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#default_value' => isset($config['iframe_width']) ? $config['iframe_width'] : '300',
      '#description' => t('Iframe width in pixels with no units (e.g. "<code>300</code>").'),
      '#min' => 1,
      '#states' => [
        'required' => [
          ':input[name="settings[embed]"]' => ['value' => 'iframe'],
        ],
      ],
    ];
    $form['iframe']['iframe_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height'),
      '#default_value' => isset($config['iframe_width']) ? $config['iframe_height'] : '300',
      '#description' => t('Iframe height in pixels with no units (e.g. "<code>300</code>").'),
      '#min' => 1,
      '#states' => [
        'required' => [
          ':input[name="settings[embed]"]' => ['value' => 'iframe'],
        ],
      ],
    ];
    $form['iframe']['iframe_border'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show border'),
      '#default_value' => isset($config['iframe_border']) ? $config['iframe_border'] : 'FALSE',
      '#description' => t('Show a border around the iframe.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['mautic_id'] = $values['mautic_id'];
    $this->configuration['embed'] = $values['embed'];
    $this->configuration['iframe_width'] = $values['iframe']['iframe_width'];
    $this->configuration['iframe_height'] = $values['iframe']['iframe_height'];
    $this->configuration['iframe_border'] = $values['iframe']['iframe_border'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildSelectList() {
    $type = $this->endpoint;
    $api_list = $this->mauticApiService->getList($type);
    $items = $api_list[$type];
    $list = [];
    foreach ($items as $item) {
      $list[$item['id']] = $item['name'];
    }
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function getMauticInstance() {
    $mautic_settings = \Drupal::config('mautic_api.settings');
    $base_url = parse_url($mautic_settings->get('base_url'));
    $mautic_instance = $base_url['host'];
    return $mautic_instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    return [
      '#theme' => 'mautic_' . $this->endpoint,
      '#mautic_instance' => $this->getMauticInstance(),
      '#mautic_id' => $config['mautic_id'],
      '#embed' => $config['embed'],
      '#iframe_width' => $config['iframe_width'],
      '#iframe_height' => $config['iframe_height'],
      '#iframe_border' => $config['iframe_border'],
      '#attached' => [
        'library' => [
          'mautic_blocks/blocks',
        ],
      ],
    ];
  }

}
