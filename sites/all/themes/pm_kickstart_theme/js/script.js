(function ($) {
  function setCookie(cname, cvalue, exdays) {
      var d = new Date();
      d.setTime(d.getTime() + (exdays*24*60*60*1000));
      var expires = "expires="+d.toUTCString();
      document.cookie = cname + "=" + cvalue + "; " + expires;
  }

  function getCookie(cname) {
      var name = cname + "=";
      var ca = document.cookie.split(';');
      for(var i=0; i<ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0)==' ') c = c.substring(1);
          if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
      }
      return "";
  }

  function initToggleCookie() {
      var wrapper = $('#wrapper');
      var status = getCookie("pmKickStartSideBarToggle");
      if (status != "") {
        wrapper.addClass('toggled');
        setCookie("pmKickStartSideBarToggle", 'toggled', 365);
      }
      else {
        wrapper.removeClass('toggled');
      }
      setTimeout(wrapperCouldBeAnimated, 1000);
      function wrapperCouldBeAnimated() {
         wrapper.addClass('css-animate');
      }
  }

  jQuery(document).ready(function($) {
    initToggleCookie();
  });

  Drupal.behaviors.pmDash = {
    attach: function (context, settings) {
      $('#sidebar-toggle-button', context).click(function (e) {
        e.preventDefault();
        var wrapper = $('#wrapper');
        // $('#wrapper').toggleClass('toggled');
        if (wrapper.hasClass('toggled')) {
          wrapper.removeClass('toggled');
          setCookie("pmKickStartSideBarToggle", '', 365);
        }
        else {
          wrapper.addClass('toggled');
          setCookie("pmKickStartSideBarToggle", 'toggled', 365);
        }

       });
    }
  };
})(jQuery);
