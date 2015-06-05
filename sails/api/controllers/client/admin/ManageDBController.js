/**
 * client/admin/ManageDBController
 *
 * @description :: Server-side logic for managing the database
 * @help        :: See http://sailsjs.org/#!/documentation/concepts/Controllers
 */

module.exports = {


  index : function (req, res) {
    res.view('client/admin/managedb/index');
  },

  deleteSiteRows : function (req, res) {

    return res.jsonWithCsrf({
        error : false
    });
    
  }

};

