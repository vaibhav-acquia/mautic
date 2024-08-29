<?php

namespace Drupal\mautic_blocks\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Mautic focus item' Block.
 *
 * This is nearly identical to the Mautic
 * form block, so we can just extend that with a few adjustments.
 *
 * @Block(
 *   id = "mautic_focus",
 *   admin_label = @Translation("Mautic Focus Item"),
 *   category = @Translation("Mautic"),
 * )
 */
class MauticFocusBlock extends MauticFormBlock {

  /**
   * {@inheritdoc}
   */
  protected $endpoint = 'focus';

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Change the block title and description.
    $form['mautic_id']['#title'] = t('Mautic focus item');
    $form['mautic_id']['#description'] = t('Select the Mautic focus item to embed.');

    // Hide the embed option because focus items only have a JS embed option.
    $form['embed'] = [
      '#type' => 'hidden',
      '#value' => 'js',
    ];
    return $form;
  }

}
