/**
 * scientia/rhythmController
 *
 * @description :: Server-side logic for managing scientia/rhythms
 * @help        :: See http://sailsjs.org/#!/documentation/concepts/Controllers
 */

module.exports = {

  /**
   * `scientia/rhythmController.get()`
   */
  get: function (req, res) {

     RhythmModel
      .findOne({ where: { name : req.param('name') }})
      .then(function (a) {
        console.log(a.rhythm_id);

        return res.json({
          rhythm_id : a.rhythm_id,
          todo: 'get() is not implemented yet!'
        });
      });
  }
};

