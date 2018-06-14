requirejs.config({
  baseUrl: 'lib',
  paths: {
    ctrl: '/ctrl',
    helper: '/helpers',
  },
  shim: {
    // "backbone": {
    //     "deps": ["underscore", "jquery", "handlebars"],
    //     "exports": "Backbone"  //attaches "Backbone" to the window object
    // },
    "onsenui": {
      "exports": "ons"
    }
  }
});



// workaround for loading onsenui
window.setImmediate = window.setTimeout;

define(['jquery', 'onsenui'], function ($, ons) {
  requirejs(['ctrl/projects']);
});