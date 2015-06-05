/**
 * JSON response as text preceeded by a csrf token.
 *
 * Usage:
 * return res.jsonWithCsrf(json_data);
 * return res.ok(json_data, status_code);
 *
 * @param  {Object} json_data
 * @param  {String|Object} options
 *          - pass string to render specified view
 */

module.exports = function jsonWithCsrf (json_data, status_code) {

    // Get access to `req`, `res`, & `sails`
    var req = this.req;
    var res = this.res;

    return res.send('&&&BABBLINGBROOK&&&' + JSON.stringify(json_data), status_code || res.statusCode || 200);
};
