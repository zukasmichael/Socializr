var expect = chai.expect;

loginSecondUser = function() {
    jQuery.ajax({
        url: getFullApiUrl('/reset/loginseconduser'),
        type: 'GET',
        dataType: 'json',
        async: false,
        xhrFields: {
            withCredentials: true
        },
        done: function( msg ) {
            //Do nothing special really
            console.log(msg);
        },
        fail: function( jqXHR, textStatus ) {
            throw new Error("Request failed: " + textStatus);
        }
    });
};

loginAdminUser = function() {
    jQuery.ajax({
        url: getFullApiUrl('/reset/loginsuperadmin'),
        type: 'GET',
        dataType: 'json',
        async: false,
        xhrFields: {
            withCredentials: true
        },
        done: function( msg ) {
            //Do nothing special really
            console.log(msg);
        },
        fail: function( jqXHR, textStatus ) {
            throw new Error("Request failed: " + textStatus);
        }
    });
};

describe('GET /user/current', function() {
    it('should return current user info', function() {
        var promise = getDataByUrl('/user/current');

        return Q.all([
            expect(promise).to.eventually.be.an('object'),
            expect(promise).to.eventually.have.property('email', 'sebastiaanderooij@gmail.com')
        ]);
    });

    it('logging in for second user, should return current user info', function() {
        loginSecondUser();
        var promise = getDataByUrl('/user/current', null, null, true);

        return Q.all([
            expect(promise).to.eventually.be.an('object'),
            expect(promise).to.eventually.have.property('email', 'srooijde@twitter.com')
        ]);
    });

    it('logging in for ADMIN user, should return current user info', function() {
        loginAdminUser();
        var promise = getDataByUrl('/user/current', null, null, true);

        return Q.all([
            expect(promise).to.eventually.be.an('object'),
            expect(promise).to.eventually.have.property('email', 'sebastiaanderooij@gmail.com')
        ]);
    });
});