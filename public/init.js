requirejs.config({
  baseUrl: 'lib',
  paths: {
    ctrl: '/ctrl',
    helper: '/helpers',
    lang: '/lang'
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

define(['jquery', 'onsenui'], function ($, ons, translate) {

  requirejs(['ctrl/projects'], function (controller) {
    controller();
  });

  document.addEventListener('init', function (event) {
    // Hooks are bound to the page element

    // $(event.target).find("[data-i18n]").map(function () {
    //   const text = $(this).text().replace(/(\r\n|\n|\r)/gm, "").trim();
    // })
  });
});