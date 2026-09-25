$.postMessage(
  'hello world desde tesoreria-mpio-xochitepec',
  'http://tesoreria-xochi.byethost4.com',
  parent
);

$(function(){
  // Get the parent page URL as it was passed in, for browsers that don't support
  // window.postMessage (this URL could be hard-coded).
  var parent_url = decodeURIComponent( document.location.hash.replace( /^#/, '' ) ),
    link;
  
  // The first param is serialized using $.param (if not a string) and passed to the
  // parent window. If window.postMessage exists, the param is passed using that,
  // otherwise it is passed in the location hash (that's why parent_url is required).
  // The second param is the targetOrigin.
  function setHeight() {
    $.postMessage({ if_height: $('body').outerHeight( true ) }, parent_url, parent );
  };
  
  // Bind all this good stuff to a link, for maximum clickage.
  link = $( '<a href="#">Show / hide content<\/a>' )
    .appendTo( '#nav' )
    .click(function(){
      $('#toggle').toggle();
      setHeight();
      return false;
    });
  
  // Now that the DOM has been set up (and the height should be set) invoke setHeight.
  setHeight();
  
  // And for good measure, let's listen for a toggle_content message from the parent.
  $.receiveMessage(function(e){
    if ( e.data === 'toggle_content' ) {
      link.triggerHandler( 'click' );
    }
  }, 'http://tesoreria-xochi.byethost4.com/' );
});