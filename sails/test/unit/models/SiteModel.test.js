//var should = require("should");
describe.only('SiteModel', function() {

  var created_site;
//console.log(fixtures);
  describe('#create()', function() {
    it('Should check create function', function (done) {
    console.log(fix2);
      SiteModel
        .create({
          domain : 'test.com.localhost'
        })
        .exec(function onCreated(err, created) {
          created.site_id.should.be.Number;
          created_site = created;
          done();
        });
    });
  });

  describe('#find()', function() {
    it('Should check find function', function (done) {
      SiteModel
        .findOne({ where : { site_id : created_site.site_id }})
        .then(function(results) {
          results.should.have.keys('site_id', 'domain');
          results.should.have.property('site_id', created_site.site_id);
          results.should.have.property('domain', 'test.com.localhost');
          done();
        })
        .catch(done);
    });
  });

  describe('#delete()', function() {
    it('Should check delete function', function (done) {
      SiteModel
        .destroy({ where : { site_id : created_site.site_id }})
        .then(function(results) {
          console.log(results);
          results[0].should.have.keys('site_id', 'domain');
          results[0].should.have.property('site_id', created_site.site_id);
          results[0].should.have.property('domain', 'test.com.localhost');
          done();
        })
        .catch(done);
    });
  });

});