define(['jquery', 'onsenui', 'mustache', 'helper/api'], function ($, ons, Mustache, api) {

  // the direction of the ideas is critical for the graphs to calculate page rank.
  const questions = [{
      id: '1',
      kind: '1',
      headline: 'Which idea is more effective for {{project.name}}?',
      question: [
        '{{idea1}}',
        '{{idea2}}'
      ]
    }, {
      id: '2',
      kind: '2',
      headline: 'Which idea is more applicable for {{project.name}}?',
      question: [
        '{{idea1}}',
        '{{idea2}}'
      ]
    },

    {
      id: '3',
      kind: 'relation',
      headline: 'What is the relation between {{idea1}} and {{idea2}}',
      question: [
        '{{idea1}} is a precondition for {{idea2}}',
        '{{idea1}} facilitates {{idea2}}',
        '{{idea1}} is not related with {{idea2}}',
        '{{idea1}} contradicts {{idea2}}',
      ]
    }
  ];

  // set a global object
  if (window.idea_collection) {
    const idea_collection = window.idea_collection;
  } else {
    window.idea_collection = {
      project: false,
      cache: [],
      add: function (idea) {
        this.cache.push(idea.idea);
      },
      setProject: function (project) {
        this.project = project;
      }
    };
  }

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
    const ideas = shuffle(idea_collection.cache);

    const data = {
      idea1: ideas[0].name,
      idea2: ideas[1].name,
      project: idea_collection.project
    }

    const template = shuffle(questions)[0];
    const response = {
      headline: Mustache.render(template.headline, data),
      question: [],
      ideas: [ideas[0], ideas[1]],
      kind: template.kind,
      id: template.id
    }
    template.question.map((answerTemplate) => {
      response.question.push(Mustache.render(answerTemplate, data));
    });
    return response;
  }

  const render_question = function () {
    const data = randomQuestion();


    $("#question")
      .html("")
      .append('<div style="margin:10px">' + data.headline + '</div>')
    $("#answerList").html("");
    var el = $('<ons-list>');
    switch (data.kind) {
      case '1':
      case '2':
        data.question.map(function (answer, index) {
          const answerEl = $('<ons-list-item tappable data-idea="' + index + '" >' +
            '<label class="left"><ons-checkbox input-id="check-' + index + '"></ons-checkbox></label>' +
            '<label for="check-' + index + '" class="center">' + answer + '</label>' +
            '</ons-list-item >')
          el.append(answerEl);
          answerEl.click(function (btn) {
            if (btn.target.tagName != 'INPUT') return;
            const event = Object.assign({}, data);
            const ideaIndex = $(btn.currentTarget).data('idea');
            const conIdea = ideaIndex ? 0 : 1;
            event.ideas = [data.ideas[ideaIndex], data.ideas[conIdea]];
            select_question(event, true)
          });
        });
        break;

      case 'relation':
        data.question.map(function (answer, index) {
          el1 = $('<ons-list-item tappable  data-relation="' + index + '" >' +
            '<label class="left"><ons-checkbox input-id="check-' + index + '"></ons-checkbox></label>' +
            '<label for="check-' + index + '" data-idea="' + index + '" class="center">' + answer + '</label>' +
            '</ons-list-item >')
          el1.click(function (btn) {
            if (btn.target.tagName != 'INPUT') return;
            const event = Object.assign({}, data);
            const relationIndex = $(btn.currentTarget).data('relation');
            event.kind = relationIndex + 3;
            select_question(event, true)
          });
          el.append(el1);
        });
        break;
    }
    $("#answerList").append(el);

  }

  const select_question = function (click_data, selected) {

    api.post('project/' + click_data.ideas[0].project_id + '/relation/', {
        'idea_id1': click_data.ideas[0].id,
        'idea_id2': click_data.ideas[1].id,
        'kind': click_data.kind
      })
      .then(function (success) {
        render_question();
      })
  }


  // initalize
  return function (project) {
    idea_collection.setProject(project);
    $('#project-name').text(project.name);

    api.get('project/' + project.id + '/idea')
      .then(function (clusters) {
        //loop on clusters
        Object.keys(clusters).map(function (clusterId) {
          clusters[clusterId].map(idea => {
            idea_collection.add(idea);
          })
        })

        render_question();
      })


  }

});