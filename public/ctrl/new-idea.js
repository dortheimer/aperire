define(['jquery', 'onsenui', 'mustache', 'helper/api'], function ($, ons, Mustache, api) {
  return function (project) {
    $('[component="button/save-idea"]').click(function () {
      api.post('project/' + project.id + '/idea', {
          'name': $('#title-name').val(),
          'description': $('#description-input').val(),
          'project_id': project.id
        })
        .then(function (idea) {
          idea_collection.add({
            idea: idea
          });
          document.querySelector('#myNavigator').popPage();
        });
    })
  }

});