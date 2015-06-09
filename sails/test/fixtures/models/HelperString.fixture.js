module.exports = { longString: function (length) {
    var long_string = '';
    for (var i=0; i < length; i++) {
        long_string += 'a';
    }
    return long_string;
}};
