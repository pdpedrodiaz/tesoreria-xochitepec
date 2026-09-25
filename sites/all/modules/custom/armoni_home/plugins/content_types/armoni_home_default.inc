<?php

/**
 * @file
 * Provides a panels pane that shows the default home page
 * This is a simple pane that just renders a tpl template that
 * you can override in your own theme
 */

$plugin = array(
  'title' => t('Default Home Panel'),
  'description' => t('A panel displays the default Armonizacion Contable home page.'),
  'single' => TRUE,
  'category' => array(t('Armonizacion'), -9),
  'edit form' => 'armoni_home_default_edit_form',
  'render callback' => 'armoni_home_default_render',
);


/**
 * Render callback for the panel.
 */
function armoni_home_default_render($subtype, $conf, $args, $context = NULL) {
  global $user;
  if (!empty($conf)) {
    // $conf is empty during popup previews, so don't load css
    global $base_url;
    $base = parse_url($base_url);
    drupal_add_css(drupal_get_path('module', 'armoni_home') . '/armoni_home_default.css');
    drupal_add_css($base['scheme'] . '://fonts.googleapis.com/css?family=Roboto:300,400,100,700,500', array('type' => 'external'));
    drupal_add_css($base['scheme'] . '://fonts.googleapis.com/css?family=Roboto+Condensed:400,700', array('type' => 'external'));
    drupal_add_css($base['scheme'] . '://fonts.googleapis.com/css?family=Roboto+Slab:400,100,700', array('type' => 'external'));
  }
  $block = new stdClass();
  $block->title = '';
  $vars = array();
  if (!$user->uid) {
    $login_block = block_load('user', 'login');
    $vars['login'] = _block_get_renderable_array(_block_render_blocks(array($login_block)));
  }
  else {
    $user_record = user_load($user->uid);
    
  // Real name was loaded/generated via hook_user_load(), so re-use it.
    if (isset($account->realname)) {
      if (drupal_strlen($account->realname)) {
      // Only if the real name is a non-empty string is $name actually altered.
        $name = $account->realname;
       } 
     }
	$vars['username'] = $name;
  }
  $block->content = theme('armoni_home_default', $vars);
  return $block;
}


/**
 * Edit form for the panel.
 */
function armoni_home_default_edit_form($form, &$form_state) {
  return $form;
}
