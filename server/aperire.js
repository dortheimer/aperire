const createError = require('http-errors');
const express = require('express');
const path = require('path');
const cookieParser = require('cookie-parser');
const logger = require('express-pino-logger')();
const log = require('./lib/log');

const app = express();

// app.use(logger);
app.use(express.json());
app.use(express.urlencoded({
  extended: false
}));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, '../public')));

app.use('/', require('./routes'));

// error handler
app.use((err, req, res, next) => {

  if (!err) {
    err = createError(404);
  }
  // set locals, only providing error in development
  res.locals.message = err.message;
  res.locals.error = req.app.get('env') === 'development' ? err : {};

  log.error(err);
  // render the error page
  res.status(err.status || 500);
  res.json(err.message);
});

module.exports = app;