const Sequelize = require('sequelize');
const config = require('config');
const log = require('./log');


const sequelize = new Sequelize(config.get('db.name'), config.get('db.user'), config.get('db.pass'), {
  host: config.get('db.host'),
  dialect: 'mysql',
  operatorsAliases: false,
  define: {
    charset: 'utf8',
    collate: 'utf8_general_ci'
  },

  pool: {
    max: 5,
    min: 0,
    acquire: 30000,
    idle: 10000,
  },
});

sequelize.authenticate()
  .then(() => log.info('Connection has been established successfully.'))
  .catch(err => log.error('Unable to connect to the database:', err));

module.exports = {
  sequelize,
  Sequelize,
};