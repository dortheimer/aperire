const {
  Sequelize,
  sequelize,
} = require('../lib/db');
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

});
module.exports = Idea;
