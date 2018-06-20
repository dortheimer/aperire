const {
  Sequelize,
  sequelize,
} = require('../lib/db');
const User = require('./user');

const Project = sequelize.define('project', {
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
  sid: {
    type: Sequelize.STRING,
  },
});

module.exports = Project;