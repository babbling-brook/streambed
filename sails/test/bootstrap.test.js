/*
 * Lifts Sails before testing comenses.
 */

var Sails = require('sails');
var sails;

before(function(done) {
  this.timeout(5000);

  Sails.lift({
    // configuration for testing purposes
    environment: 'test',
  }, function(err, server) {
    sails = server;
    if (err) {
      return done(err);
    }

    done(err, sails);
  });
});

after(function(done) {
  Sails.lower(done);
});