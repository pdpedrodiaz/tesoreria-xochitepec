/**
 * @file
 * Apply advanced options when this site will be hosted within a resizeable iFrame.
 */

(function ($) {

  'use strict';

  var hosted_options = {};
  if (Drupal.settings.iframe_resizer_targetorigin) {
    hosted_options.targetOrigin = Drupal.settings.iframe_resizer_targetorigin;
  }

  if (Drupal.settings.iframe_resizer_hosted_width_calculation_method) {
    hosted_options.widthCalculationMethod = Drupal.settings.iframe_resizer_hosted_width_calculation_method;
  }

  if (Drupal.settings.iframe_resizer_hosted_height_calculation_method) {
    hosted_options.heightCalculationMethod = Drupal.settings.iframe_resizer_hosted_height_calculation_method;
  }

  if (!$.isEmptyObject(hosted_options)) {
    window.iFrameResizer = hosted_options;
  }

}(jQuery));
