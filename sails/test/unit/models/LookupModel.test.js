var lookup_fixture = require('../../fixtures/models/LookupModel.fixture.js');

describe.only('LookupModel', function() {

  var created_site;

  describe('#create()', function() {
    it('Should check create function', function (done) {
      LookupModel
        .create({
          column_name : lookup_fixture.row_1.column_name,
          value : lookup_fixture.row_1.value,
          description: lookup_fixture.row_1.description,
          sort_order: lookup_fixture.row_1.sort_order
        })
        .exec(function onCreated(err, created) {
          created.lookup_id.should.be.Number;
          created_lookup = created;
          done();
        });
    });
  });

  describe('#find()', function() {
    it('Should check find function', function (done) {
      LookupModel
        .findOne({ where : { site_id : created_site.site_id }})
        .then(function(results) {
          results.should.have.keys('lookup_id', 'column_name', 'description', 'sort_order');
          results.should.have.property('column_name', created_site.column_name);
          results.should.have.property('description', created_site.description);
          results.should.have.property('sort_order', created_site.sort_order);
          done();
        })
        .catch(done);
    });
  });

  describe('#delete()', function() {
    it('Should check delete function', function (done) {
      LookupModel
        .destroy({ where : { site_id : created_site.site_id }})
        .then(function(results) {
          console.log(results);
          results.should.have.keys('lookup_id', 'column_name', 'description', 'sort_order');
          results[0].should.have.property('column_name', lookup_fixture.row_1.column_name);
          results[0].should.have.property('description', lookup_fixture.row_1.description);
          results[0].should.have.property('sort_order', lookup_fixture.row_1.sort_order);
          done();
        })
        .catch(done);
    });
  });

});