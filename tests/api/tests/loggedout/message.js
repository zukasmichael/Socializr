var expect = chai.expect;

describe('GET /board/{boardId}/message', function() {
    this.timeout(2500);

    it('should return all messages', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var boardId = lastResults['GET:/group/'][0].boards[0].id;
        var promise = getDataByUrl('/board/' + boardId + '/message');

        return Q.all([
            expect(promise).to.eventually.be.an('array').and.have.length(11),
            expect(promise).to.eventually.have.deep.property('[10].contents', 'Example message 11'),
            expect(promise).to.eventually.have.deep.property('[10].group_id', groupId),
            expect(promise).to.eventually.have.deep.property('[10].board_id', boardId)
        ]);
    });
});

describe('GET /board/{boardId}/message?limit=7&offset=2', function() {
    this.timeout(2500);

    it('should return all messages', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var boardId = lastResults['GET:/group/'][0].boards[0].id;
        var promise = getDataByUrl('/board/' + boardId + '/message?limit=7&offset=2');

        return Q.all([
            expect(promise).to.eventually.be.an('array').and.have.length(7),
            expect(promise).to.eventually.have.deep.property('[3].contents', 'Example message 6'),
            expect(promise).to.eventually.have.deep.property('[3].group_id', groupId),
            expect(promise).to.eventually.have.deep.property('[3].board_id', boardId),
            expect(promise).to.eventually.have.deep.property('[3].post_user').and.have.property('user_name', 'Other User Example')
        ]);
    });
});

describe('GET /message/{id}', function() {
    this.timeout(2500);

    it('should return all messages', function() {
        var groupId = lastResults['GET:/group/'][0].id;
        var boardId = lastResults['GET:/group/'][0].boards[0].id;
        var messageId = lastResults['GET:/board/' + boardId + '/message'][6].id;
        var promise = getDataByUrl('/message/' + messageId);

        return Q.all([
            expect(promise).to.eventually.have.property('contents', 'Example message 7')
        ]);
    });

    it('should return a 404 when it does not exists', function() {
        var promise = getDataByUrl('/message/foobar');

        return Q.all([
            expect(promise).to.eventually.have.property('status', 404),
            expect(promise).to.eventually.have.deep.property('responseJSON.Message', 'The requested resource was not found.')
        ]);
    });
});
