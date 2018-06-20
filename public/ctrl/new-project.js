define(['jquery', 'onsenui', 'mustache', 'helper/api'], function ($, ons, Mustache, api) {
  return function (project) {
    $('[component="button/save-project"]').click(function () {
      api.post('project/', {
          'name': $('#title-name').val(),
          'description': $('#description-input').val()
        })
        .then(function (idea) {
          document.querySelector('#myNavigator').popPage();
        });
    })
  }

});