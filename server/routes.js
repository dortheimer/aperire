const router = require('express').Router();
const dispatcher = require('./lib/dispatcher');
const Project = require('./models/project');
const User = require('./models/user');
const Idea = require('./models/idea');
const Relation = require('./models/relation');

router.post('/sign/up', (req, res, next) =>
  dispatcher(() =>
    User.create(req.body))(req, res, next));

router.post('/sign/in', (req, res, next) =>
  dispatcher(() =>
    User.login(req.body))(req, res, next));

router.get('/project', (req, res, next) =>
  dispatcher(() =>
    Project.findAll())(req, res, next));

router.post('/project', (req, res, next) =>
  dispatcher(() =>
    Project.create(req.body))(req, res, next));

router.patch('/project', (req, res, next) =>
  dispatcher(() =>
    Project.update(req.body))(req, res, next));

router.get('/project/:project_id/idea', (req, res, next) =>
  dispatcher(() =>
    Idea.findAll({
      where: {
        project_id: req.params.project_id
      }
    }))(req, res, next));

router.post('/project/:project_id/idea', (req, res, next) =>
  dispatcher(() =>
    Idea.create(req.body))(req, res, next));

router.patch('/project/:project_id/idea/:idea_id', (req, res, next) =>
  dispatcher(() =>
    Idea.update(req.body))(req, res, next));

router.post('/project/:project_id/relation', (req, res, next) =>
  dispatcher(() =>
    Relation.create(req.body))(req, res, next));

module.exports = router;