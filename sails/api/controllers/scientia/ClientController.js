/**
 * scientia/clientController
 *
 * @description :: Server-side logic for managing scientia/clients
 * @help        :: See http://sailsjs.org/#!/documentation/concepts/Controllers
 */

module.exports = {

  /**
   * `scientia/clientController.siteData()`
   */
  siteData: function (req, res) {
    return res.json({
      todo: 'siteData() is not implemented yet! ' + req.param('username')
    });
  }
};

