const {
  Sequelize,
  sequelize,
} = require('../lib/db');
const Ngraph = require('ngraph.graph');
const Pagerank = require('ngraph.pagerank');
const ChineseWhispers = require('ngraph.cw');
const User = require('./user');
const Project = require('./project');

const Idea = sequelize.define('idea', {
  id: {
    type: Sequelize.INTEGER,
    autoIncrement: true,
    primaryKey: true,
  },
  name: {
    type: Sequelize.STRING,
  },
  description: {
    type: Sequelize.STRING(1024),
  },
  user_id: {
    type: Sequelize.INTEGER,
    references: {
      model: User,
      key: 'id',
    },
  },
  project_id: {
    type: Sequelize.INTEGER,
    references: {
      model: Project,
      key: 'id',
    },
  },
  sid: {
    type: Sequelize.STRING,
  },
});


Idea.byProject = (project_id) => {
  const sql = `SELECT ideas.id, ideas.name, ideas.description,
  sum(if(r1.kind=1 OR r2.kind=1,1,0)) as kind1,
  sum(if(r1.kind=2 OR r2.kind=2,1,0)) as kind2,
  sum(if(r1.kind=3 OR r2.kind=3,1,0)) as kind3,
  sum(if(r1.kind=4 OR r2.kind=4,1,0)) as kind4,
  sum(if(r1.kind=5 OR r2.kind=5,1,0)) as kind5,
  sum(if(r1.kind=6 OR r2.kind=6,1,0)) as kind6

  from ideas
  left join relations as r1 on ideas.id=r1.idea_id1
  left join relations as r2 on ideas.id=r2.idea_id2
  where ideas.project_id=$project_id
  group by ideas.id`;
  return sequelize.query(sql, {
    bind: {
      project_id,
    },
    raw: true,
  }).spread((results, meta) => results);
}

Idea.analysed = (project_id) => {

  const binding = {
    bind: {
      project_id,
    }
  };
  const sql1 = `SELECT r.idea_id1,r.idea_id2, r.kind, count(*) as weight 
  FROM relations r
  inner join ideas i on r.idea_id1=i.id
  WHERE 
  i.project_id=$project_id AND
  r.kind IN(1,2)
  GROUP BY r.idea_id1, r.idea_id2, r.kind`;

  // kind 5 is no relation
  const sql2 = `SELECT r.idea_id1,r.idea_id2, sum(CASE r.kind WHEN 3 THEN 5 WHEN 4 THEN 1 WHEN 5 THEN -5 END) as weight
  FROM relations r
  inner join ideas i on r.idea_id1=i.id
  WHERE 
  i.project_id=$project_id AND
  r.kind IN(3,4,6)
  GROUP BY r.idea_id1, r.idea_id2`;

  const promise1 = sequelize.query(sql1, binding)
    .spread((results) => {
      // calculate effectiveness and applicability
      const effectiveness = Ngraph();
      const applicability = Ngraph();

      results.map(((relation) => {
        switch (relation.kind) {
          case 1:
            effectiveness.addLink(relation.idea_id1, relation.idea_id2);
            break;
          case 2:
            applicability.addLink(relation.idea_id1, relation.idea_id2);
            break;
          default:
        }
      }));

      const effRank = Pagerank(effectiveness);
      const appRank = Pagerank(applicability);
      return {
        effRank,
        appRank,
      };
    });

  const promise2 = sequelize.query(sql2, binding)
    .spread((results) => {
      const cummunities = Ngraph();
      results.map((relation) => {
        // if it is some positive relation
        if (parseInt(relation.weight) > 0) {
          cummunities.addLink(relation.idea_id1, relation.idea_id2);
        } else {
          cummunities.addNode(relation.idea_id1);
          cummunities.addNode(relation.idea_id2);
        }
      });

      const whisper = ChineseWhispers(cummunities);

      const requiredChangeRate = 0; // 0 is complete convergence
      while (whisper.getChangeRate() > requiredChangeRate) {
        whisper.step();
      }
      const mapping = {};
      let index = 0;
      cummunities.forEachNode((node) => {
        index = whisper.getClass(node.id);
        if (!mapping[index]) {
          mapping[index] = [];
        }
        mapping[index].push(node);
      });
      return mapping;
    });
  return Promise.all([promise1, promise2, Idea.findAll()])
    .then((results) => {
      const communities = results[1];
      const ranks = results[0];
      const ideas = results[2];
      const ideaMap = {};

      ideas.map(idea => ideaMap[idea.id] = idea);

      // loop on communities and ass rankings
      Object.keys(communities).map((key) => {
        Object.keys(communities[key]).map((i) => {
          const id = communities[key][i].id;
          communities[key][i] = {
            effectiveness: ranks.effRank[id],
            applicability: ranks.appRank[id],
            idea: ideaMap[id],
          };
          delete ideaMap[id];
        });
      });

      const returnArray = Object.values(communities);
      // add remaining ideas
      Object.keys(ideaMap).map((id) => {
        const key = returnArray.length;
        returnArray[key] = [{
          idea: ideaMap[id],
          effectiveness: ranks.effRank[id],
          applicability: ranks.appRank[id],
        }];
      });

      // add missing ideas
      return returnArray;
    });
};

module.exports = Idea;