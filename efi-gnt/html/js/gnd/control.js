

// This class should be a singleton object.
// Not thread-safe.

const LOAD_ALL = 1;     // Load all of the diagrams in the input list or cluster
const LOAD_NEXT = 2;    // Load the next batch of data
const LOAD_RELOAD = 3;  // Reload what's present on the screen (scale factor/window change)


class GndController {
    constructor(msgRouter, gndDb, gndHttp, gndVars, gndView) {
        this.Http = gndHttp;
        this.Vars = gndVars;
        this.Db = gndDb;
        this.View = gndView;
        this.getUrlFn = function(a,b){};
        this.initUrlFn = function(){};
        this.msgRouter = msgRouter;
        this.query = "";
        this.indexRange = [];
        this.useRange = true;
        this.maxIndex = 0;

        this.reset(false);
    }

    getUrl(start, end) {
        return this.getUrlFn(start, end);
    }
    getMaxIndex() {
        return this.maxIndex;
    }

    // The input to this function is the diagram index (e.g. the view).  This function computes
    // the actual arrow data index from the diagram index start and end.  This is used in
    // the HTTP request to optimize data retrieval.
    computeRange(start, end) {
        if (this.indexRange.length == 0)
            return 0;

        var ranges = [];

        var blockStart = 0;
        for (var bi = 0; bi < this.indexRange.length; bi++) {
            var block = this.indexRange[bi];
            var blockEnd = blockStart + (block[1] - block[0]);
            var sb = -1, eb = -1;
            if (start >= blockStart && start <= blockEnd)
                sb = block[0] + (start - blockStart);
            if (end >= blockStart && end <= blockEnd)
                eb = block[0] + (end - blockStart);
            if (start < blockStart && end > blockEnd) {
                sb = block[0];
                eb = block[1];
            }
            
            if (sb < 0 && eb >= 0)
                sb = block[0];
            if (sb >= 0 && eb < 0)
                eb = block[1];

            if (sb >= 0 && eb >= 0)
                ranges.push([sb, eb]);
            blockStart = blockEnd + 1;
        }

        return ranges;
    }

    serializeRange(ranges) {
        var str = "";
        for (var i = 0; i < ranges.length; i++) {
            if (str)
                str += ",";
            str += ranges[i][0] + "-" + ranges[i][1];
        }
        return str;
    }

    getScaleFactor() {
        return this.scaleFactor;
    }
    getScaleType() {
        return this.scaleType;
    }
    getWindow() {
        return this.window;
    }
    // Sets the window size and resets the scale type to window.  Refreshes the view.
    setWindow(value) {
        this.window = value;
        this.scaleType = SCALE_TYPE_WINDOW;
        this.reload();
    }
    // Updates the scale factor based on the zoom factor.  Refreshes the view.
    setZoom(zoomFactor) {
        this.scaleFactor = this.scaleFactor * zoomFactor;
        this.scaleType = SCALE_TYPE_FACTOR;
        this.reload();
    }

    // Called every time we load a cluster/put in a new set of inputs in the query box.
    onLoad(query) {

        //var queryEscaped = query.replace(/\n/g, " ").replace(/\r/g, " ");
        var queryEscaped = query.replace(/[^A-Za-z0-9\-, ]/g, " ");
        //TODO: move these methods to a different class?
        var authString = this.Vars.getAuthString();
        var urlPath = this.Vars.getUrlPath();

        var that = this;
        this.getUrlFn = function(start, end) {
            var win = that.getWindow();
            var sf = that.getScaleFactor();
            var scaleType = that.getScaleType();
            var sep = "?"
            var url = urlPath;
            url += sep + authString;
            sep = "&";
            url += sep + "window=" + win;
//            if (scaleType == SCALE_TYPE_FACTOR)
                url += sep + "scale-factor=" + sf;
            if (that.useRange) {
                var ranges = that.computeRange(start, end);
                var rangeStr = that.serializeRange(ranges);
                url += sep + "range=" + rangeStr;
            } else {
                url += sep + "query=" + queryEscaped;
                url += sep + "sidx=" + start;
                url += sep + "eidx=" + end;
            }
            return url;
        };

        this.initUrlFn = function() {
            var win = that.getWindow();
            var sep = "?";
            var url = urlPath;
            url += sep + authString;
            sep = "&";
            url += sep + "window=" + win;
            url += sep + "query=" + queryEscaped;
            url += sep + "stats=1";
            return url;
        };

        var that = this;
        var handleInitRequest = function(jsonData) {
            if (jsonData !== null && typeof jsonData.stats.max_index !== 'undefined') {
                that.Http.initialize(jsonData.stats.max_index, that.getUrlFn);
                that.scaleFactor = jsonData.stats.scale_factor;
                that.View.setLegendScale(jsonData.stats.legend_scale);
                that.maxIndex = jsonData.stats.max_index;
                that.indexRange = jsonData.stats.index_range;
                if (typeof jsonData.totaltime !== "undefined")
                    console.log("Init load duration: " + jsonData.totaltime);
                
                var totalCount = jsonData.stats.max_index + 1; // zero-based index
                var payload = new Payload(); payload.MessageType = "InitDataRetrieved"; payload.Data = { TotalCount: totalCount }
                that.msgRouter.sendMessage(payload);

                that.loadNext(); // Retrieve the first batch of arrows.
            } else {
                console.log("Invalid json data: ");
                console.log(jsonData);
            }
        };

        var payload = new Payload(); payload.MessageType = "DataRetrievalStatus"; payload.Data = { Retrieving: true, Message: "Retrieving initial data...", Initial: true };
        that.msgRouter.sendMessage(payload);
//        var payload = new Payload(); payload.MessageType = "UpdateShowButtonStatus"; payload.Data = { Disabled: true };
//        this.msgRouter.sendMessage(payload);

        this.Http.fetchInit(this.initUrlFn, handleInitRequest);
    }

    search(query) {
        this.reset(true);
        this.query = query;
        this.onLoad(query);
    }

    // PRIVATE
    reload() {
        this.View.clearCanvas();
        //this.onLoad(this.query);
        this.doLoad(LOAD_RELOAD);
    }

    loadAll() {
        this.doLoad(LOAD_ALL);
    }

    loadNext() {
        this.doLoad(LOAD_NEXT);
    }

    // Private
    reset(fullReset) {
        this.scaleFactor = this.Vars.getDefaultScaleFactor();
        this.scaleType = this.Vars.getDefaultScaleType();
        this.window = this.Vars.getDefaultWindow();

        // fullReset = true if this isn't the first load of the app.
        if (fullReset) {
            //TODO: reset filter, scale factor, etc
            this.View.clearCanvas();
        }
    }

    // Private
    doLoad(loadAction) {
        var that = this;

        var pageSize = loadAction == LOAD_ALL ? -1 : this.Vars.getPageSize();

        // Handles one block of data from the server.
        var handleData = function(startIndex, endIndex, jsonData) {
            var drawables = that.Db.update(startIndex, endIndex, jsonData);
            var viewData = that.Db.getViewData(jsonData);
            that.View.addDiagrams(drawables, viewData);
            if (typeof jsonData.totaltime !== "undefined")
                console.log("Processing duration: " + jsonData.totaltime);
        };

        // After all data has been retrieved and drawn, this is called.
        var handleFinish = function() {
            that.View.finishDrawFetched();
            var diagramCount = that.View.getDiagramCount();
            var isEod = that.View.getEndOfData();
            var payload = new Payload(); payload.MessageType = "DataRetrievalStatus"; payload.Data = { Retrieving: false, DiagramCount: diagramCount, EndOfData: isEod };
            that.msgRouter.sendMessage(payload);
        };

//        var payload = new Payload(); payload.MessageType = "UpdateShowButtonStatus"; payload.Data = { Disabled: true };
//        this.msgRouter.sendMessage(payload);

        // This returns immediately, and retrieves asynchronously.
        if (loadAction == LOAD_NEXT || loadAction == LOAD_ALL)
            this.Http.fetchDiagrams(pageSize, handleData, handleFinish);
        else
            this.Http.reloadDiagrams(handleData, handleFinish);
    }
}

