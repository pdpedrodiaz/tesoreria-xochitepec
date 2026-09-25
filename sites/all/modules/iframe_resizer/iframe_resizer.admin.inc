<?php

/**
 * @file
 * Iframe Resizer administration pages.
 */

/**
 * Returns with the general configuration form.
 */
function iframe_resizer_admin_form($form, &$form_state) {

  // Before anything, let's make sure the iFrame Resizer library is installed
  // correctly.
  $library_path = iframe_resizer_get_library_path();
  if (!$library_path) {
    drupal_set_message(t('You need to download the <a href="@library-download-url">iFrame Resizer JavaScript file</a> and extract the entire contents of the archive into the %path directory on your server.', array(
      '@library-download-url' => IFRAME_RESIZER_LIBRARY_URL,
      '%path' => 'sites/all/libraries',
    )), 'error');
    return;
  }

  $form['iframe_resizer_usage'] = array(
    '#type' => 'fieldset',
    '#title' => t('iFrame Resizer Usage'),
    '#description' => t("At least one of the options below should be chosen. Otherwise, this module won't do anything."),
  );
  $form['iframe_resizer_usage']['iframe_resizer_host'] = array(
    '#type' => 'checkbox',
    '#title' => t('This site will host resizable iFrames.'),
    '#default_value' => variable_get('iframe_resizer_host', FALSE),
    '#description' => t("Enable this option if the iFrames being included in this site should be resizable (Note that the site being iFramed in will need to include the iFrame Resizer library's iframeResizer.contentWindow.js file)."),
  );
  $form['iframe_resizer_usage']['iframe_resizer_hosted'] = array(
    '#type' => 'checkbox',
    '#title' => t('Pages from this site will be hosted within iFrames that have been made resizable by the iFrame Resizer JavaScript library.'),
    '#default_value' => variable_get('iframe_resizer_hosted', FALSE),
    '#description' => t('Enable this option if sites using the iFrame Resizer library will be hosting pages from your site in an iFrame.'),
  );

  // Set up advanced configuration options for sites hosting resizable iFrames.
  $form['iframe_resizer_advanced'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced Options for Hosting Resizable iFrames'),
    '#collapsible' => TRUE,
    '#states' => array(
      'visible' => array(
        'input[name="iframe_resizer_host"]' => array('checked' => TRUE),
      ),
    ),
    '#description' => t('Advanced options to be applied when this site will be hosting resizeable iFrames.'),
  );
  $form['iframe_resizer_advanced']['iframe_resizer_target_type'] = array(
    '#type' => 'radios',
    '#title' => t('Which iFrames should be targeted by the iFrame Resizer library?'),
    '#default_value' => variable_get('iframe_resizer_target_type', 'all_iframes'),
    '#options' => array('all_iframes' => t('All iFrames'), 'specific' => t('Specific iFrames')),
  );
  $form['iframe_resizer_advanced']['iframe_resizer_target_specifiers'] = array(
    '#type' => 'textarea',
    '#title' => t('Specify the iFrames which should be targeted by the iFrame Resizer library by supplying jQuery selectors.'),
    '#default_value' => variable_get('iframe_resizer_target_specifiers', ''),
    '#description' => t('Use one or more jQuery selectors (for example, ".iframe-id" or "div.content > .iframe-class" without the quotation marks) to specify which hosted iFrames should be targeted by the iFrame Resizer library. Enter one selector per line.'),
    '#states' => array(
      'disabled' => array(
        'input[name="iframe_resizer_target_type"]' => array('value' => 'all_iframes'),
      ),
      'enabled' => array(
        'input[name="iframe_resizer_target_type"]' => array('value' => 'specific'),
      ),
      'required' => array(
        'input[name="iframe_resizer_target_type"]' => array('value' => 'specific'),
      ),
    ),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_override_defaults'] = array(
    '#type' => 'checkbox',
    '#title' => t('Override the default behavior of the iFrame Resizer library.'),
    '#default_value' => variable_get('iframe_resizer_override_defaults', FALSE),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options'] = array(
    '#type' => 'fieldset',
    '#title' => t('Configure the options made available by the iFrame Resizer library'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#states' => array(
      'visible' => array(
        'input[name="iframe_resizer_override_defaults"]' => array('checked' => TRUE),
      ),
    ),
  );
  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_log'] = array(
    '#type' => 'checkbox',
    '#title' => t('Turn iFrame Resizer JavaScript console logging on.'),
    '#description' => t('Setting the log option to true will make the scripts in both the host page and the iFrame output everything they do to the JavaScript console so you can see the communication between the two scripts.'),
    '#default_value' => variable_get('iframe_resizer_log', FALSE),
  );
  $height_calc_options = drupal_map_assoc(array(
    'bodyScroll',
    'documentElementOffset',
    'documentElementScroll',
    'max',
    'min',
    'grow',
    'lowestElement',
    'taggedElement',
  ));
  $height_calc_options = array('bodyOffset' => t('Use library default (bodyOffset)')) + $height_calc_options;
  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_height_calculation_method'] = array(
    '#type' => 'select',
    '#title' => t('iFrame Height Calculation Method'),
    '#description' => t('Different circumstances require different methods of calculating the height of the iFramed content.'),
    '#default_value' => variable_get('iframe_resizer_height_calculation_method', 'bodyOffset'),
    '#options' => $height_calc_options,
    '#states' => array(
      'required' => array(
        'input[name="iframe_resizer_override_defaults"]' => array('checked' => TRUE),
      ),
    ),
  );
  $width_calc_options = drupal_map_assoc(array(
    'bodyOffset',
    'bodyScroll',
    'documentElementOffset',
    'documentElementScroll',
    'max',
    'min',
    'rightMostElement',
    'taggedElement',
  ));
  $width_calc_options = array('scroll' => 'Use library default (scroll)') + $width_calc_options;
  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_width_calculation_method'] = array(
    '#type' => 'select',
    '#title' => t('iFrame Width Calculation Method'),
    '#description' => t('Different circumstances require different methods of calculating the width of the iFramed content.'),
    '#default_value' => variable_get('iframe_resizer_width_calculation_method', 'scroll'),
    '#options' => $width_calc_options,
    '#states' => array(
      'required' => array(
        'input[name="iframe_resizer_override_defaults"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_autoresize'] = array(
    '#type' => 'checkbox',
    '#title' => t('Automatically resize the iFrame when its DOM changes.'),
    '#description' => t('Checked by default'),
    '#default_value' => variable_get('iframe_resizer_autoresize', TRUE),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_bodybackground'] = array(
    '#type' => 'textfield',
    '#title' => t('iFrame body background CSS'),
    '#description' => t("Override the body background style of the iFrame. Leave blank to use the iFrame's default background."),
    '#default_value' => variable_get('iframe_resizer_bodybackground', ''),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_bodymargin'] = array(
    '#type' => 'textfield',
    '#title' => t('iFrame body margin CSS'),
    '#description' => t("Override the iFrame's body's margin styles. Leave blank to use the iFrame's default body margin styles."),
    '#default_value' => variable_get('iframe_resizer_bodymargin', ''),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_inpagelinks'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable in page linking inside the iFrame and from the iFrame to the parent page'),
    '#default_value' => variable_get('iframe_resizer_inpagelinks', FALSE),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_interval'] = array(
    '#type' => 'textfield',
    '#title' => t('Page size change check interval'),
    '#description' => t("How often to check (in milliseconds) for page size changes in browsers which don't support mutationObserver. Default is 32. Setting this property to a negative number will force the interval check to run instead of mutationObserver. Set to zero to disable."),
    '#default_value' => variable_get('iframe_resizer_interval', 32),
    '#element_validate' => array('element_validate_integer'),
    '#size' => 5,
    '#states' => array(
      'required' => array(
        'input[name="iframe_resizer_override_defaults"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_maxheight'] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum height of the iFrame'),
    '#description' => t("Leave blank or set to 'Infinity' to set no maximum, the default."),
    '#default_value' => variable_get('iframe_resizer_maxheight', 'Infinity'),
    '#element_validate' => array('iframe_resizer_element_validate_positive_integer_zero_infinity'),
    '#size' => 8,
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_maxwidth'] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum width of the iFrame'),
    '#description' => t("Leave blank or set to 'Infinity' to set no maximum, the default."),
    '#default_value' => variable_get('iframe_resizer_maxwidth', 'Infinity'),
    '#element_validate' => array('iframe_resizer_element_validate_positive_integer_zero_infinity'),
    '#size' => 8,
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_minheight'] = array(
    '#type' => 'textfield',
    '#title' => t('Minimum height of the iFrame'),
    '#description' => t('Leave blank or set to 0 to set no minimum, the default.'),
    '#default_value' => variable_get('iframe_resizer_minheight', 0),
    '#element_validate' => array('iframe_resizer_element_validate_positive_integer_zero'),
    '#size' => 8,
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_minwidth'] = array(
    '#type' => 'textfield',
    '#title' => t('Minimum width of the iFrame'),
    '#description' => t('Leave blank or set to 0 to set no minimum, the default.'),
    '#default_value' => variable_get('iframe_resizer_minwidth', 0),
    '#element_validate' => array('iframe_resizer_element_validate_positive_integer_zero'),
    '#size' => 8,
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_resizefrom'] = array(
    '#type' => 'select',
    '#title' => t('Resize event listener'),
    '#description' => t('Listen for resize events from the parent page, or the iFrame.'),
    '#default_value' => variable_get('iframe_resizer_resizefrom', 'parent'),
    '#options' => array(
      'parent' => t('Use library default (parent)'),
      'child' => t('child'),
    ),
    '#states' => array(
      'required' => array(
        'input[name="iframe_resizer_override_defaults"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_scrolling'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable scroll bars in iFrame'),
    '#default_value' => variable_get('iframe_resizer_scrolling', FALSE),
    '#description' => t('Disabled by default.'),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_sizeheight'] = array(
    '#type' => 'checkbox',
    '#title' => t('Resize iFrame to content height.'),
    '#default_value' => variable_get('iframe_resizer_sizeheight', TRUE),
    '#description' => t('Enabled by default.'),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_sizewidth'] = array(
    '#type' => 'checkbox',
    '#title' => t('Resize iFrame to content width.'),
    '#default_value' => variable_get('iframe_resizer_sizewidth', FALSE),
    '#description' => t('Disabled by default.'),
  );

  $form['iframe_resizer_advanced']['iframe_resizer_options']['iframe_resizer_tolerance'] = array(
    '#type' => 'textfield',
    '#title' => t('Tolerance'),
    '#description' => t('Set the number of pixels the iFrame content size has to change by, before triggering a resize of the iFrame. Default is 0.'),
    '#default_value' => variable_get('iframe_resizer_tolerance', 0),
    '#element_validate' => array('iframe_resizer_element_validate_positive_integer_zero'),
    '#size' => 8,
    '#states' => array(
      'required' => array(
        'input[name="iframe_resizer_override_defaults"]' => array('checked' => TRUE),
      ),
    ),
  );

  // Set up advanced configuration options for sites hosted resizable iFrames.
  $form['iframe_resizer_advanced_hosted_options'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced Options for Hosted Resizable iFrames'),
    '#collapsible' => TRUE,
    '#states' => array(
      'visible' => array(
        'input[name="iframe_resizer_hosted"]' => array('checked' => TRUE),
      ),
    ),
    '#description' => t('Advanced options to be applied when this site will be hosted within a resizeable iFrame.'),
  );

  $form['iframe_resizer_advanced_hosted_options']['iframe_resizer_targetorigin'] = array(
    '#type' => 'textfield',
    '#title' => t('targetOrigin'),
    '#description' => t("Restrict the domain of the parent page to prevent other sites mimicking your parent page. Include protocol ('http://' or 'https://')."),
    '#default_value' => variable_get('iframe_resizer_targetorigin', ''),
  );

  $height_calc_options['bodyOffset'] = 'bodyOffset';
  $height_calc_options = array('' => t("Use parent's height calculation method")) + $height_calc_options;
  $form['iframe_resizer_advanced_hosted_options']['iframe_resizer_hosted_height_calculation_method'] = array(
    '#type' => 'select',
    '#title' => t('iFrame Height Calculation Method'),
    '#description' => t('Different circumstances require different methods of calculating the height of the iFramed content.'),
    '#default_value' => variable_get('iframe_resizer_hosted_height_calculation_method', ''),
    '#options' => $height_calc_options,
  );

  $width_calc_options['scroll'] = 'scroll';
  $width_calc_options = array('' => t("Use parent's width calculation method")) + $width_calc_options;
  $form['iframe_resizer_advanced_hosted_options']['iframe_resizer_hosted_width_calculation_method'] = array(
    '#type' => 'select',
    '#title' => t('iFrame Width Calculation Method'),
    '#description' => t('Different circumstances require different methods of calculating the width of the iFramed content.'),
    '#default_value' => variable_get('iframe_resizer_hosted_width_calculation_method', ''),
    '#options' => $width_calc_options,
  );

  $form['#validate'][] = 'iframe_resizer_admin_form_validate';

  return system_settings_form($form);
}

/**
 * Provides validation for the general configuration form.
 */
function iframe_resizer_admin_form_validate($form, &$form_state) {
  if ($form_state['values']['iframe_resizer_target_type'] == 'specific' && empty($form_state['values']['iframe_resizer_target_specifiers'])) {
    form_set_error('iframe_resizer_target_specifiers', t('You must specify at least one jQuery selector.'));
  }

  if ($form_state['values']['iframe_resizer_override_defaults'] !== 0) {
    $fields_reqd_override = array();
    foreach ($form['iframe_resizer_advanced']['iframe_resizer_options'] as $field_name => $field_value) {
      if (isset($field_value['#states']['required']['input[name="iframe_resizer_override_defaults"]']['checked']) && $field_value['#states']['required']['input[name="iframe_resizer_override_defaults"]']['checked'] === TRUE) {
        $fields_reqd_override[$field_name] = $field_value['#title'];
      }
    }
    foreach ($fields_reqd_override as $field_name => $field_title) {
      if (trim($form_state['values'][$field_name]) === '') {
        form_set_error($field_name, t('@name field is required.', array('@name' => $field_title)));
      }
    }
  }
}

/**
 * Validation handler for elements that must be positive, zero or 'infinity'.
 */
function iframe_resizer_element_validate_positive_integer_zero_infinity($element, &$form_state) {
  $value = $element['#value'];
  if (strcasecmp($value, 'infinity') == 0) {
    return;
  }
  if ($value !== '' && (!is_numeric($value) || intval($value) != $value || $value < 0)) {
    form_error($element, t("%name must be an integer or 'Infinity'.", array('%name' => $element['#title'])));
  }
}

/**
 * Validation handler for elements that must be a positive integer or zero.
 */
function iframe_resizer_element_validate_positive_integer_zero($element, &$form_state) {
  $value = $element['#value'];
  if ($value !== '' && (!is_numeric($value) || intval($value) != $value || $value < 0)) {
    form_error($element, t('%name must be a positive integer or zero.', array('%name' => $element['#title'])));
  }
}
