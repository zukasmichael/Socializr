var expect = chai.expect;

describe('GET /group/', function() {
    this.timeout(2500);

    it('should return only and all allowed groups', function() {
        var promise = getDataByUrl('/group/');

        return Q.all([
            expect(promise).to.eventually.have.length(5),
            expect(promise).to.eventually.have.deep.property('[0].description', 'First group, open, by Other User Example'),
            expect(promise).to.eventually.have.deep.property('[1].description', 'Second group, open, by Other User Example'),
            expect(promise).to.eventually.have.deep.property('[2].name', 'Example 3'),
            expect(promise).to.not.eventually.have.deep.property('[2].description'),
            expect(promise).to.eventually.have.deep.property('[3].description', 'Sixth group, open, by Other User Example'),
            expect(promise).to.eventually.have.deep.property('[4].name', 'Example 7'),
            expect(promise).to.not.eventually.have.deep.property('[4].description')
        ]);
    });

    it('should return only and all open boards', function() {
        var promise = getDataByUrl('/group/');

        return Q.all([
            expect(promise).to.eventually.have.deep.property('[0].visibility', 1),
            expect(promise).to.eventually.have.deep.property('[0].boards.').that.is.an('array').with.length(3),
            expect(promise).to.eventually.have.deep.property('[0].boards.[0].title', 'Example board 1'),
            expect(promise).to.eventually.have.deep.property('[0].boards.[1].title', 'Example board 2'),
            expect(promise).to.eventually.have.deep.property('[0].boards.[2].title', 'Example board 3'),

            expect(promise).to.eventually.have.deep.property('[1].visibility', 1),
            expect(promise).to.eventually.have.deep.property('[1].boards').that.is.an('array').with.length(2),
            expect(promise).to.eventually.have.deep.property('[1].boards.[0].title', 'Example board 4'),
            expect(promise).to.eventually.have.deep.property('[1].boards.[1].title', 'Example board 5'),

            expect(promise).to.eventually.have.deep.property('[2].visibility', 2),
            expect(promise).to.eventually.have.deep.property('[2].boards').that.deep.equals([]),

            expect(promise).to.eventually.have.deep.property('[3].visibility', 1),
            expect(promise).to.eventually.have.deep.property('[3].boards').that.deep.equals([]),

            expect(promise).to.eventually.have.deep.property('[4].visibility', 2),
            expect(promise).to.eventually.have.deep.property('[4].boards').that.deep.equals([])
        ]);
    });

    it('should not return board messages', function() {
        var promise = getDataByUrl('/group/');

        return Q.all([
            expect(promise).to.not.eventually.have.deep.property('[0].boards.messages'),
            expect(promise).to.not.eventually.have.deep.property('[1].boards.messages')
        ]);
    });
});

describe('GET /group/?limit=3&offset=3', function() {
    this.timeout(2500);

    it('should return only a limited set of two groups', function() {
        var promise = getDataByUrl('/group/?limit=3&offset=3');

        return Q.all([
            expect(promise).to.eventually.have.length(2),
            expect(promise).to.eventually.have.deep.property('[0].description', 'Sixth group, open, by Other User Example'),
            expect(promise).to.eventually.have.deep.property('[1].name', 'Example 7'),
            expect(promise).to.not.eventually.have.deep.property('[1].description')
        ]);
    });
});

describe('GET /group/?limit=2&offset=4', function() {
    this.timeout(2500);

    it('should return only a limited set of one group', function() {
        var promise = getDataByUrl('/group/?limit=2&offset=4');

        return Q.all([
            expect(promise).to.eventually.have.length(1),
            expect(promise).to.eventually.have.deep.property('[0].name', 'Example 7'),
            expect(promise).to.not.eventually.have.deep.property('[0].description')
        ]);
    });
});

describe('GET /group/?limit=100&offset=5', function() {
    this.timeout(2500);

    it('should return no groups at all', function() {
        var promise = getDataByUrl('/group/?limit=100&offset=5');

        return expect(promise).to.eventually.be.an('array').and.have.length(0);
    });
});

describe('GET /group/{id}', function() {
    this.timeout(2500);

    it('when it exists, should return a complete group', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var promise = getDataByUrl('/group/' + groupId);

        return Q.all([
            expect(promise).to.eventually.be.an('object'),
            expect(promise).to.eventually.have.property('name', 'Example 1'),
            expect(promise).to.eventually.have.property('description', 'First group, open, by Other User Example'),
            expect(promise).to.eventually.have.property('visibility', 1)
        ]);
    });

    it('when it doesn\'t exist, should return a 404', function(done) {
        var promise = getDataByUrl('/group/foobar');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 404),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'The requested resource was not found.')
        ]);
    });
});

describe('POST /group/', function() {
    this.timeout(2500);

    it('should return a 403', function() {
        var promise = getDataByUrl('/group/', 'POST', {title:"foo"});

        return Q.all([
            expect(promise).to.eventually.have.property('status', 403),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'Access to this resource is forbidden.')
        ]);
    });
});