const bluebird = require('bluebird');
const success = require('../views/success');
const {
  bind,
} = require('lodash');


module.exports = (fn, ctx) => (req, res, next) => bluebird
  .try(() => (ctx ? bind(fn, ctx)(req) : fn(req)))
  .then(response => success.set(res, response))
  .catch((err) => {
    req.error = err;
    next(err);
  });