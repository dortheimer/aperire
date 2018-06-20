const {
  Sequelize,
  sequelize,
} = require('../lib/db');
const User = require('./user');
const Idea = require('./idea');

const Relation = sequelize.define('relation', {
  id: {
    type: Sequelize.INTEGER,
    autoIncrement: true,
    primaryKey: true,
  },
  user_id: {
    type: Sequelize.INTEGER,
    references: {
      model: User,
      key: 'id',
    },
  },
  idea_id1: {
    type: Sequelize.INTEGER,
    references: {
      model: Idea,
      key: 'id',
    },
  },
  idea_id2: {
    type: Sequelize.INTEGER,
    references: {
      model: Idea,
      key: 'id',
    },
  },
  kind: {
    type: Sequelize.INTEGER,
  },
  sid: {
    type: Sequelize.STRING,
  },

});
module.exports = Relation;