

function setupArchiveUi() {
    initArchiveButton(getExtraData);
}


function getExtraData(element) {
    var trElem = element.parent().parent();

    var elemList = [trElem];

    var elementHideFn = function() {
        elemList.map(x => x.hide());
    };

    var otherIds = [];

    return [otherIds, elementHideFn];
}

