

// This code is not thread-safe.

class GndHttp {
    constructor(msgRouter) {
        this.msgRouter = msgRouter;
        this.initialize(-1, function(a,b){});
    }


    ///////////////////////////////////////////// PRIVATE /////////////////////////////////////////////
    makeDiagramHttpRequest(handleData, startIndex, endIndex) {
        var theUrl = this.scriptFn(startIndex, endIndex);

        return this.makeHttpRequest(theUrl, handleData);
    }

    makeHttpRequest(theUrl, handleData) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.open("GET", theUrl, true);
        xmlhttp.onload = function() {
            if (this.readyState == 4 && this.status == 200) {
                var data = null;
                var isError = false;
                try {
                    data = JSON.parse(this.responseText);
                } catch (e) {
                    isError = true;
                }
                handleData(data);
            }
        };
        return xmlhttp;
    }


    ///////////////////////////////////////////// PUBLIC //////////////////////////////////////////////
    initialize(maxIndex, getUrlFn) {
        this.diagramIndex = 0;
        this.maxIndex = maxIndex;
        this.scriptFn = getUrlFn;
    }

    // Call this initially to get the extent of the data.
    fetchInit(initUrlFn, handleInitCb) {
        var theUrl = initUrlFn();
        var xmlhttp = this.makeHttpRequest(theUrl, handleInitCb);
        xmlhttp.send(null);
    }

    // Call this to retrieve diagrams from the server.  It chunks the data to allow for more responsive
    // retrieval and drawing.
    fetchDiagrams(numDiagrams, handleData, onFinishCb) {
        this.doFetch(this.diagramIndex, numDiagrams, handleData, onFinishCb);
    }

    doFetch(startIndex, numDiagrams, handleData, onFinishCb) {
        var endIndex = startIndex + numDiagrams - 1;
        if (numDiagrams < 0 || endIndex > this.maxIndex) // load all
            endIndex = this.maxIndex;

        var chunkSize = 20;
        var chunkIndex = startIndex;

        var that = this;

        function batchRetrieve() {
            var endChunkIndex = chunkIndex + chunkSize - 1;
            if (endChunkIndex >= endIndex)
                endChunkIndex = endIndex;
            if (chunkIndex <= endIndex) {
                var onReceiveCb = function(data) {
                    if (data !== null)
                        handleData(chunkIndex, endChunkIndex, data);
                    else
                        handleData(chunkIndex, endChunkIndex, []);
                    chunkIndex = endChunkIndex + 1;
                    setTimeout(batchRetrieve, 1); // allows the UI to draw
                };
                var xmlhttp = that.makeDiagramHttpRequest(onReceiveCb, chunkIndex, endChunkIndex);
                xmlhttp.send(null);
                
                var pct = Math.trunc(100 * (chunkIndex - startIndex) / (endIndex - startIndex));
                var payload = new Payload(); payload.MessageType = "DataRetrievalStatus"; payload.Data = { Retrieving: true, Message: "Retrieving diagrams...", PercentCompleted: pct};
                that.msgRouter.sendMessage(payload);
            } else {
                that.diagramIndex = endChunkIndex + 1;
                onFinishCb();
            }
        };

        var pct = 50;
        var payload = new Payload(); payload.MessageType = "DataRetrievalStatus"; payload.Data = { Retrieving: true, Message: "Retrieving diagrams...", PercentCompleted: 0};
        this.msgRouter.sendMessage(payload);
        batchRetrieve();
    }

    reloadDiagrams(handleData, onFinishCb) {
        this.doFetch(0, this.diagramIndex, handleData, onFinishCb);
    }
}


