var expect = chai.expect;

function logJson(json) {
    console.log(json);
    return json;
}

function fetchLoginData() {
    return Q.when(jQuery.ajax({
        type: 'GET',
        url: getFullApiUrl('/login')
    })
    ).then(logJson);
};

describe('GET /login', function() {
    it('should return loginPaths for facebook, google and twitter', function() {

        var loginPromise = fetchLoginData();

        return Q.all([
            expect(loginPromise).to.eventually.have.property('loginPaths'),
            expect(loginPromise).to.eventually.have.deep.property('loginPaths.facebook'),
            expect(loginPromise).to.eventually.have.deep.property('loginPaths.google'),
            expect(loginPromise).to.eventually.have.deep.property('loginPaths.twitter')
        ]);
    });
});