define(['jquery', 'onsenui', 'mustache', 'helper/api'], function ($, ons, Mustache, api) {

  var idea_collection = [];

  // the direction of the ideas is critical for the graphs to calculate page rank.
  const questions = [{
      id: '1',
      headline: 'Do you agree or disagree with the following statement?',
      question: ['"{{idea2}}"<strong style="display:block">is more effective then</strong> "{{idea1}}"']
    }, {
      id: '2',
      headline: 'Do you agree or disagree with the following statement?',
      question: ['"{{idea2}}" <strong style="display:block">is more applicable than</strong> "{{idea1}}"'],
    }, {
      id: '3',
      headline: 'Do you agree or disagree with the following statement?',
      question: [
        '"{{idea2}}"<strong style="display:block">is a precondition for</strong> "{{idea1}}',
      ]
    }, {
      id: '4',
      headline: 'Do you agree or disagree with the following statement?',
      question: [
        '"{{idea2}}"<strong style="display:block">facilitates</strong> "{{idea1}}',
      ]
    }, {
      id: '5',
      headline: 'Do you agree or disagree with the following statement?',
      question: [
        '"{{idea2}}"<strong style="display:block">contradicts</strong> "{{idea1}}',
      ]
    }
    // , Synergy is a bi directional relation which makes it hard to calculate.
    // It's better to have facilitate on both directions
    // {
    //   id: '6',
    //   headline: 'Do you agree or disagree with the following statement?',
    //   question: [
    //     '"{{idea1}}" and "{{idea2}} <strong style="display:block">are synergistic</strong>',
    //   ]
    // }
  ];

  const shuffle = function (array) {
    var currentIndex = array.length,
      temporaryValue, randomIndex;

    // While there remain elements to shuffle...
    while (0 !== currentIndex) {

      // Pick a remaining element...
      randomIndex = Math.floor(Math.random() * currentIndex);
      currentIndex -= 1;

      // And swap it with the current element.
      temporaryValue = array[currentIndex];
      array[currentIndex] = array[randomIndex];
      array[randomIndex] = temporaryValue;
    }

    return array;
  }

  const randomQuestion = function () {
    const ideas = shuffle(idea_collection);
    const data = {
      idea1: ideas[0].name,
      idea2: ideas[1].name,
    }

    const template = shuffle(questions)[0];

    const response = {
      headline: Mustache.render(template.headline, data),
      question: [],
      ideas: ideas,
      id: template.id,
    }
    template.question.map((answerTemplate) => {
      response.question.push(Mustache.render(answerTemplate, data));
    });
    return response;
  }

  const format_percent = function (float) {
    if (!float) float = 0;
    return float.toLocaleString("en", {
      style: "percent"
    })
  }

  const render_question = function () {
    const data = randomQuestion();
    $("#question")
      .html("")
      .append('<div style="margin:10px">' + data.headline + '</div>')

    $("#answerList").html("");
    data.question.map(function (answer, index) {
      var el = '<div style="margin:20px; text-align:center">' + answer + '</div>' +
        '<ons-row style="margin:20px;">' +
        '<ons-col><ons-fab id="btn-agree"><ons-icon icon="thumbs-up"></ons-icon> </ons-fab> Agree</ons-col>' +
        '<ons-col><ons-fab id="btn-disagree"><ons-icon icon="thumbs-down"></ons-icon></ons-fab> Disagree</ons-col>' +
        '</ons-row>';
      $("#answerList").append(el);
      $("#btn-agree").click(function () {
        select_question(data, true)
      });
      $("#btn-disagree").click(function () {
        // put new question without saving
        render_question()
      });
    })
  }

  const select_question = function (data, selected) {

    api.post('project/' + data.ideas[0].project_id + '/relation/', {
        'idea_id1': data.ideas[0].id,
        'idea_id2': data.ideas[1].id,
        'kind': data.ideas[1].id
      })
      .then(function (success) {
        render_question();
      })

  }


  // initalize
  return function (project) {
    $('#project-name').text(project.name);
    $('#project-description').html(project.description);

    $("#startAnswering").on('click', function (e) {
      document.getElementById('projectTabber').setActiveTab(2);
    })

    $("#new-idea-btn").click(function () {
      document.getElementById('myNavigator').pushPage('templates/idea-new.html');
      requirejs(['ctrl/new-idea']);
    })

    api.get('project/' + project.id + '/idea')
      .then(function (clusters) {
        //loop on clusters
        Object.keys(clusters).map(function (clusterId) {
          $("#ideaList").append($('<ons-list-header>Cluster ' + clusterId + '</ons-list-header>'));
          clusters[clusterId].map(idea => {
            // this is the cache for answering
            let obj = idea.idea;
            idea_collection.push(obj);

            if (idea.description) {
              var el = $('<ons-list-item expandable>' + obj.name +
                '<div class="expandable-content">' + (obj.description ? obj.description : 'No description') + '</div>' +
                '</ons-list-item>');
            } else {
              var el = $('<ons-list-item>' + obj.name + ' ' + format_percent(idea.applicability) + ' ' + format_percent(idea.effectiveness) +
                '</ons-list-item>');
            }
            $("#ideaList").append(el);
          })
        })

        render_question();
      })


  }

});