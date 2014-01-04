var expect = chai.expect;

describe('GET /group/foo/invite/bar', function() {
    it('when it doesn\'t exist, should return a 404', function() {
        var promise = getDataByUrl('/group/foo/invite/bar');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 404),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'Group does not exist.')
        ]);
    });
});

describe('GET /group/{groupId}/invite/foobar', function() {
    it('should return a 403', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var promise = getDataByUrl('/group/' + groupId + '/invite/foobar');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 403),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'Access to this resource is forbidden.')
        ]);
    });
});

describe('GET /group/foo/block/bar', function() {
    it('when it doesn\'t exist, should return a 404', function() {
        var promise = getDataByUrl('/group/foo/block/bar');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 404),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'Group does not exist.')
        ]);
    });
});

describe('GET /group/{groupId}/block/foobar', function() {
    it('should return a 403', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var promise = getDataByUrl('/group/' + groupId + '/block/foobar');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 403),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'Access to this resource is forbidden.')
        ]);
    });
});

describe('GET /group/foo/promote/bar', function() {
    it('when it doesn\'t exist, should return a 404', function() {
        var promise = getDataByUrl('/group/foo/promote/bar');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 404),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'Group does not exist.')
        ]);
    });
});

describe('GET /group/{groupId}/promote/foobar', function() {
    it('should return a 403', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var promise = getDataByUrl('/group/' + groupId + '/promote/foobar');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 403),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'Access to this resource is forbidden.')
        ]);
    });
});