var expect = chai.expect;
var users = [];

describe('GET /user/', function() {
    it('should return basic user info', function() {
        var promise = getDataByUrl('/user/');

        return Q.all([
            expect(promise).to.eventually.be.an('array').and.have.length(1),
            expect(promise).to.eventually.have.deep.property('[0].email', 'otheruser@example.com'),
            expect(promise).to.eventually.have.deep.property('[0].user_name', 'Other User Example')
        ]);
    });
});