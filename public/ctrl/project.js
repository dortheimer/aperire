define(['jquery', 'onsenui', 'mustache', 'helper/api'], function ($, ons, Mustache, api) {

  // set a global object
  if (!window.idea_collection) {
    window.idea_collection = {
      project: false,
      cache: [],
      add: function (idea) {
        this.cache.push(idea.idea);
        render_idea(idea);
      },
      setProject: function (project) {
        this.project = project;
      }
    };
  }

  const render_idea = function (idea) {
    var el = $(' <ons-list-item>' +
      '<div class="left">' +
      format_percent(idea.applicability) +
      '</div>' +
      '<div class="center">' +
      '<span class="list-item__title">' + idea.idea.name + '</span><span class="list-item__subtitle">' +
      (idea.idea.description ? idea.idea.description : '') + '</span>' +
      '</div>' +
      '</ons-list-item>');
    $("#ideaList").append(el);
  }

  const format_percent = function (float) {
    if (!float) return '';
    return float.toLocaleString("en", {
      style: "percent"
    })
  }

  // initalize
  return function (project) {
    idea_collection.setProject(project);
    $('#project-name').text(project.name);
    $('#project-description').html(project.description);

    $("#start-answering-bth").on('click', function (e) {
      // document.getElementById('projectTabber').setActiveTab(2);
      document.getElementById('myNavigator').pushPage('templates/answers.html').then(function () {
        requirejs(['ctrl/answer'], function (controller) {
          controller(project);
        });
      })
    })

    $("#new-idea-btn").click(function () {
      document.getElementById('myNavigator').pushPage('templates/idea-new.html');
      requirejs(['ctrl/new-idea'], function (controller) {
        controller(project);
      });
    })

    api.get('project/' + project.id + '/idea')
      .then(function (clusters) {
        //loop on clusters
        Object.keys(clusters).map(function (clusterId) {
          $("#ideaList").append($('<ons-list-header>Cluster ' + clusterId + '</ons-list-header>'));
          clusters[clusterId].map(idea => {
            // this is the cache for answering
            idea_collection.add(idea);
          })
        })
      })
  }

});