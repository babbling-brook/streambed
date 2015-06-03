//var should = require("should");
describe.only('RhythmModel', function() {

  describe('#find()', function() {
    it('should check find function', function (done) {
      RhythmModel
        .findOne({ where : { rhythm_id : 1 }})
        .then(function(results) {
          results.should.have.keys('rhythm_id', 'user_id', 'name');
          results.should.have.property('rhythm_id', 1);
          results.should.have.property('user_id', 1);
          results.should.have.property('name', 'test rhythm 1');
          done();
        })
        .catch(done);
    });

    it('should check create function', function (done) {
      RhythmModel
        .create({
          user_id : 1,
          name : 'A rhythm created as a test.'
        })
        .exec(function onCreated(err, created) {
            created.rhythm_id.should.be.Number;
          done();
        });
    });
  });

});