var site_fixture = require('../../fixtures/models/SiteModel.fixture.js');

describe.only('SiteModel', function() {

  var created_site;

  describe('#create()', function() {
    it('Should check create function', function (done) {
      SiteModel
        .create({
          domain : site_fixture.row_1.domain
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
          results.should.have.property('domain', site_fixture.row_1.domain);
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
          results[0].should.have.property('domain', site_fixture.row_1.domain);
          done();
        })
        .catch(done);
    });
  });

});