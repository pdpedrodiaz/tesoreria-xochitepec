/**
 * @file
 * Resizes the page's iframes according to the module's configuration.
 */

(function ($, settings) {

  'use strict';

  // Set up the iFrame Resizer library's options.
  var options = {};
  if (settings.iframe_resizer_override_defaults === '1') {

    // Do some pre-processing on configuration values.
    if (settings.iframe_resizer_options.bodyBackground === '') {
      settings.iframe_resizer_options.bodyBackground = null;
    }
    if (settings.iframe_resizer_options.bodyMargin === '') {
      settings.iframe_resizer_options.bodyMargin = null;
    }
    if (settings.iframe_resizer_options.interval === '') {
      settings.iframe_resizer_options.interval = 32;
    }
    if (settings.iframe_resizer_options.maxHeight === '' || settings.iframe_resizer_options.maxHeight.toUpperCase() === 'infinity'.toUpperCase()) {
      settings.iframe_resizer_options.maxHeight = "Infinity";
    }
    else {
      settings.iframe_resizer_options.maxHeight = parseInt(settings.iframe_resizer_options.maxHeight);
    }
    if (settings.iframe_resizer_options.maxWidth === '' || settings.iframe_resizer_options.maxWidth.toUpperCase() === 'infinity'.toUpperCase()) {
      settings.iframe_resizer_options.maxWidth = "Infinity";
    }
    else {
      settings.iframe_resizer_options.maxWidth = parseInt(settings.iframe_resizer_options.maxWidth);
    }
    if (settings.iframe_resizer_options.minHeight === '') {
      settings.iframe_resizer_options.minHeight = 0;
    }
    if (settings.iframe_resizer_options.minWidth === '') {
      settings.iframe_resizer_options.minWidth = 0;
    }
    if (settings.iframe_resizer_options.tolerance === '') {
      settings.iframe_resizer_options.tolerance = 0;
    }

    // Create the options object for the iFrame Resizer library.
    options = {
      log: parseInt(settings.iframe_resizer_options.log) === 1,
      heightCalculationMethod: settings.iframe_resizer_options.heightCalculationMethod,
      widthCalculationMethod: settings.iframe_resizer_options.widthCalculationMethod,
      autoResize: parseInt(settings.iframe_resizer_options.autoResize) === 1,
      bodyBackground: settings.iframe_resizer_options.bodyBackground,
      bodyMargin: settings.iframe_resizer_options.bodyMargin,
      inPageLinks: parseInt(settings.iframe_resizer_options.inPageLinks) === 1,
      interval: parseInt(settings.iframe_resizer_options.interval),
      maxHeight: settings.iframe_resizer_options.maxHeight,
      maxWidth: settings.iframe_resizer_options.maxWidth,
      minHeight: parseInt(settings.iframe_resizer_options.minHeight),
      minWidth: parseInt(settings.iframe_resizer_options.minWidth),
      resizeFrom: settings.iframe_resizer_options.resizeFrom,
      scrolling: parseInt(settings.iframe_resizer_options.scrolling) === 1,
      sizeHeight: parseInt(settings.iframe_resizer_options.sizeHeight) === 1,
      sizeWidth: parseInt(settings.iframe_resizer_options.sizeWidth) === 1,
      tolerance: parseInt(settings.iframe_resizer_options.tolerance)
    };
  }

  Drupal.behaviors.iframe_resizer = {
    attach: function (context, settings) {
      // Resize the specified iFrames.
      $(settings.iframe_resizer_target_specifiers, context).iFrameResize(options);
    }
  };
}(jQuery, Drupal.settings));
