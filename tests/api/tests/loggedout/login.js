var expect = chai.expect;

describe('GET /login', function() {
    it('should return loginPaths for facebook, google and twitter', function() {
        var loginPromise = getDataByUrl('/login');

        return Q.all([
            expect(loginPromise).to.eventually.have.property('loginPaths'),
            expect(loginPromise).to.eventually.have.deep.property('loginPaths.facebook'),
            expect(loginPromise).to.eventually.have.deep.property('loginPaths.google'),
            expect(loginPromise).to.eventually.have.deep.property('loginPaths.twitter')
        ]);
    });
});