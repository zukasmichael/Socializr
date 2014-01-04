var expect = chai.expect;

describe('GET /group/{groupId}/board', function() {
    this.timeout(2500);

    it('should return group boards', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var promise = getDataByUrl('/group/' + groupId + '/board');

        return Q.all([
            expect(promise).to.eventually.have.length(3),
            expect(promise).to.eventually.have.deep.property('[0].title', 'Example board 1'),
            expect(promise).to.eventually.have.deep.property('[0].messages').with.length(11),
            expect(promise).to.eventually.have.deep.property('[1].title', 'Example board 2'),
            expect(promise).to.eventually.have.deep.property('[1].messages').with.length(3),
            expect(promise).to.eventually.have.deep.property('[2].title', 'Example board 3'),
            expect(promise).to.eventually.have.deep.property('[2].messages').with.length(2)
        ]);
    });

    it('should return a 403 when not allowed', function() {
        var groupId = lastResults['GET:/group/'][2].id;
        var promise = getDataByUrl('/group/' + groupId + '/board');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 403),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'Access to this resource is forbidden.')
        ]);
    });
});

describe('GET /group/{groupId}/board?limit=2&offset=1', function() {
    this.timeout(2500);

    it('should return only a limited set of two boards', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var promise = getDataByUrl('/group/' + groupId + '/board?limit=2&offset=1');

        return Q.all([
            expect(promise).to.eventually.have.length(2),
            expect(promise).to.eventually.have.deep.property('[0].title', 'Example board 2'),
            expect(promise).to.eventually.have.deep.property('[0].messages').with.length(3),
            expect(promise).to.eventually.have.deep.property('[1].title', 'Example board 3'),
            expect(promise).to.eventually.have.deep.property('[1].messages').with.length(2)
        ]);
    });
});

describe('GET /group/{groupId}/board?limit=88&offset=3', function() {
    this.timeout(2500);

    it('should return no boards', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var promise = getDataByUrl('/group/' + groupId + '/board?limit=88&offset=3');

        return expect(promise).to.eventually.be.an('array').and.have.length(0);
    });
});

describe('POST /group/{groupId}/board', function() {
    this.timeout(2500);

    it('should return a 403', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var promise = getDataByUrl('/group/' + groupId + '/board', 'POST', {title:"foo"});

        return Q.all([
            expect(promise).to.eventually.have.property('status', 403),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'Access to this resource is forbidden.')
        ]);
    });
});

describe('GET /board/{id}', function() {
    this.timeout(2500);

    it('when it exists, should return a complete board', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var boardId = lastResults['GET:/group/'][0].boards[0].id;
        var promise = getDataByUrl('/board/' + boardId);

        return Q.all([
            expect(promise).to.eventually.be.an('object'),
            expect(promise).to.eventually.have.property('group_id', groupId),
            expect(promise).to.eventually.have.property('title', 'Example board 1'),
            expect(promise).to.eventually.have.property('visibility', 1),
            expect(promise).to.eventually.have.property('messages').and.be.an('array').and.have.length(11)
        ]);
    });

    it('should return a 404 when it does not exists', function() {
        var promise = getDataByUrl('/board/foobar');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 404),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'The requested resource was not found.')
        ]);
    });
});