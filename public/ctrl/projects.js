define(['jquery', 'onsenui', 'helper/api'], function ($, ons, api) {
  api.get('project')
    .then(function (projects) {
      projects.map(function (project) {
        var el = $('<ons-list-item tappable modifier="chevron">' +
        '<div class="left"><ons-icon icon="md-face" class="list-item__icon"></ons-icon></div>' +
        '<div class="center"><span class="list-item__title">'+project.name+'</span></div>' +
        '</ons-list-item>');
        el.on('click',function() {
          document.querySelector('#myNavigator')
          .pushPage('templates/project.html',
            {
              animation: 'slide',
              data: {
                element: project
              }
            }
          ).then(function(){
            requirejs(['ctrl/project'], function(controller){
              controller(project);
            });
          })
        })
        $("#projectList").append(el);
      })

    })
});