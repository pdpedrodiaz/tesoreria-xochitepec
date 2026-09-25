<?php

/**
 * @file
 * template.php
 */

/**
 * Implements hook_preprocess_views_view_table().
 */
function pm_kickstart_theme_preprocess_views_view_table(&$vars) {
  $vars['classes_array'][] = 'table table-stripped table-bordered table-hover';
}


function pm_kickstart_theme_preprocess_ctools_dropdown(&$vars) {
  $vars['dropdown_menu'] = array();
  $vars['default_link']  = array();

  $flag_first_item = TRUE;
  foreach ($vars['links'] as $key => $value) {
    $options = array();
    $href = $value['href'];
    if (isset($value['query'])) {
      $options = array(
        'query' => $value['query'],
      );
    }
    $url = !empty($href) ? check_plain(url($href, $options)) : '';

    if ($flag_first_item) {
      $vars['default_link'] = $value;
      $vars['default_link']['url'] = $url;
      $vars['default_link']['class'] = !empty($value['attributes']['class']) ? implode(' ', $value['attributes']['class']) : '';
    }
    else {
      $vars['dropdown_menu'][$key] = $value;
      $vars['dropdown_menu'][$key]['url'] = $url;
      $vars['dropdown_menu'][$key]['class'] = !empty($value['attributes']['class']) ? implode(' ', $value['attributes']['class']) : '';
    }
    $flag_first_item = FALSE;
  }
}

function pm_kickstart_theme_preprocess_links__ctools_dropbutton(&$vars) {
  $vars['dropdown_menu'] = array();
  $vars['default_link']  = array();

  $flag_first_item = TRUE;
  foreach ($vars['links'] as $key => $value) {
    $options = array();
    $href = $value['href'];
    if (isset($value['query'])) {
      $options = array(
        'query' => $value['query'],
      );
    }
    $url = !empty($href) ? check_plain(url($href, $options)) : '';

    if ($flag_first_item) {
      $vars['default_link'] = $value;
      $vars['default_link']['url'] = $url;
      $vars['default_link']['class'] = !empty($value['attributes']['class']) ? implode(' ', $value['attributes']['class']) : '';
    }
    else {
      $vars['dropdown_menu'][$key] = $value;
      $vars['dropdown_menu'][$key]['url'] = $url;
      $vars['dropdown_menu'][$key]['class'] = !empty($value['attributes']['class']) ? implode(' ', $value['attributes']['class']) : '';
    }
    $flag_first_item = FALSE;
  }
}

function pm_kickstart_theme_preprocess_pm_dashboard_link(&$variables) {
  switch ($variables['icon']) {
    case 'pmconfiguration':
      $variables['fa_icon'] = 'fa-gear';
      break;
    case 'pmexpenses':
      $variables['fa_icon'] = 'fa-money';
      break;
    case 'pmnotes':
      $variables['fa_icon'] = 'fa-file-o';
      break;
    case 'pmtimetrackings':
      $variables['fa_icon'] = 'fa-clock-o';
      break;
    case 'pmtickets':
      $variables['fa_icon'] = 'fa-ticket';
      break;
    case 'pmtasks':
      $variables['fa_icon'] = 'fa-tasks';
      break;
    case 'pmprojects':
      $variables['fa_icon'] = 'fa-flask';
      break;
    case 'pmteams':
      $variables['fa_icon'] = 'fa-users';
      break;
    case 'pmorganizations':
      $variables['fa_icon'] = 'fa-institution';
      break;
    default:
      $variables['fa_icon'] = 'fa-wrench';
      break;
  }
  $variables['href'] = check_plain(url($variables['path']));
  $variables['href_add'] = FALSE;
  if ($variables['add_type']) {
    $variables['href_add'] = check_plain(url('node/add/' . $variables['add_type']));
  }
}
/**
 * Theme function implementation for bootstrap_search_form_wrapper.
 */
function pm_kickstart_theme_bootstrap_search_form_wrapper($variables) {
  $parent = reset($variables['element']['#parents']);
  $prefix = TRUE;
  $button_type = 'button';
  switch ($parent) {
    case 'name':
      $fa_icon = 'fa-user';
      break;
    case 'pass':
      $fa_icon = 'fa-key';
      break;
    case 'search_block_form':
      $fa_icon = 'fa-search';
      $button_type = 'submit';
      $prefix = FALSE;
      break;
    default:
      $fa_icon = 'fa-keyboard-o';
      break;
  }

  $button  = '<span class="input-group-btn">';
  $button .= '<button type="' . $button_type . '" class="btn btn-default">';
  $button .= '<i class="fa '. $fa_icon .' fa-fw"></i>';
  $button .= '</button>';
  $button .= '</span>';

  $output  = '<div class="input-group">';

  if ($prefix) {
    $output .= $button . $variables['element']['#children'];
  }
  else {
    $output .= $variables['element']['#children'] . $button;
  }

  $output .= '</div>';

  return $output;
}


/**
 * Implements hook_form_alter().
 */
function pm_kickstart_theme_form_alter(array &$form, array &$form_state = array(), $form_id = NULL) {
  if ($form_id) {
    switch ($form_id) {
      case 'user_login_form':
      case 'user_login_block':
        $form['name']['#theme_wrappers'] = array('bootstrap_search_form_wrapper');
        $form['pass']['#theme_wrappers'] = array('bootstrap_search_form_wrapper');
        break;

      case 'search_block_formdddd':
        $form['#attributes']['class'][] = 'form-search';

        $form['search_block_form']['#title'] = '';
        $form['search_block_form']['#attributes']['placeholder'] = t('Search');

        // Hide the default button from display and implement a theme wrapper
        // to add a submit button containing a search icon directly after the
        // input element.
        $form['actions']['submit']['#attributes']['class'][] = 'element-invisible';
        $form['search_block_form']['#theme_wrappers'] = array('bootstrap_search_form_wrapper');

        // Apply a clearfix so the results don't overflow onto the form.
        $form['#attributes']['class'][] = 'content-search';
        break;
    }

  }
}
