<?php

/**
 * @file
 * Content type plugin to expose entity edit form and maybe clone.
 * Code taken from entity module rendered entity pane.
 */

$plugin = array(
  'title' => t('HTTP response header'),
  'single' => TRUE,
  'content_types' => array('panels_responseheader_pane'),
  'defaults' => array(
    'name' => '',
    'value' => '',
    'append' => FALSE,
    ), // This seems not to prepopulate $conf.
  'edit form' => 'panels_responseheader_pane_edit_form',
  'render callback' => 'panels_responseheader_pane_render',
  'admin title' => 'panels_responseheader_pane_admin_title',
  'category' => array(t('HTTP Headers'), -9),
);

function panels_responseheader_pane_render($subtype, $conf, $args, $context) {
  drupal_add_http_header($conf['name'], $conf['value'] ? $conf['value'] : FALSE, $conf['append']);
  $block = new stdClass();
  if (!empty($content)) {
    $block->content = $content;
  }
  return $block;
}

function panels_responseheader_pane_edit_form($form, &$form_state) {
  $conf = $form_state['conf'];
  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#size' => 50,
    '#description' => t('The name of the HTTP header.'),
    '#default_value' => !empty($conf['name']) ? $conf['name'] : '',
    '#required' => TRUE,
  );
  $form['value'] = array(
    '#type' => 'textfield',
    '#title' => t('Value'),
    '#size' => 50,
    '#description' => t('The value of the HTTP header. Leave empty to unset.'),
    '#default_value' => !empty($conf['value']) ? $conf['value'] : '',
  );
  $form['append'] = array(
    '#type' => 'checkbox',
    '#title' => t('Append'),
    '#description' => t('If checked, the value is appended.'),
    '#default_value' => !empty($conf['append']) ? $conf['append'] : FALSE,
  );

  $form['override_title']['#access'] = FALSE;
  $form['override_title_text']['#access'] = FALSE;
  $form['override_title_heading']['#access'] = FALSE;
  $form['override_title_markup']['#access'] = FALSE;
  return $form;
}

function panels_responseheader_pane_edit_form_submit($form, &$form_state) {
  $form_state['conf']['name'] = $form_state['values']['name'];
  $form_state['conf']['value'] = $form_state['values']['value'];
  $form_state['conf']['append'] = $form_state['values']['append'];
}

/**
 * Returns the administrative title for a type.
 */
function panels_responseheader_pane_admin_title($subtype, $conf, $context) {
  $t_args = array(
    '%name' => $conf['name'],
    '%value' => $conf['value'],
  );
  $title = $conf['append'] ?
    t('Append to response header %name: %value', $t_args) :
    t('Set response header %name: %value', $t_args);
  return $title;
}
