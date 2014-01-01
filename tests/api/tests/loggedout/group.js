var expect = chai.expect;
var groups = [];

function saveGroups(json) {
    groups = json;
    return json;
}

function fetchGroupData(forceAjax) {
    if (!forceAjax && groups.length > 0) {
        var deferred = Q.defer();
        setTimeout(deferred.resolve(groups), 0);
        return deferred.promise;
    }
    return Q.when(
        $.ajax({
            type: 'GET',
            url: getFullApiUrl('/group')
        })
    ).then(saveGroups);
};

describe('GET /group', function() {
    it('should return only allowed groups', function() {
        var groupPromise = fetchGroupData();

        return Q.all([
            expect(groupPromise).to.eventually.have.length(5),
            expect(groupPromise).to.eventually.have.deep.property('[0].description', 'First group, open, by Other User Example'),
            expect(groupPromise).to.eventually.have.deep.property('[1].description', 'Second group, open, by Other User Example'),
            expect(groupPromise).to.eventually.have.deep.property('[2].name', 'Example 3'),
            expect(groupPromise).to.not.eventually.have.deep.property('[2].description'),
            expect(groupPromise).to.eventually.have.deep.property('[3].description', 'Sixth group, open, by Other User Example'),
            expect(groupPromise).to.eventually.have.deep.property('[4].name', 'Example 7'),
            expect(groupPromise).to.not.eventually.have.deep.property('[4].description')
        ]);
    });
});