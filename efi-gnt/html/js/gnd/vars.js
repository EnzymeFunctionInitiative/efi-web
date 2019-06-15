

const SCALE_TYPE_WINDOW = 90;
const SCALE_TYPE_FACTOR = 91;
const DEFAULT_SCALE_FACTOR = 5;
const DEFAULT_WINDOW = 10;
const DEFAULT_PAGE_SIZE = 200;


class GndVars {
    constructor() {
        this.authString = "";
        this.urlPath = "";
        this.pageSize = DEFAULT_PAGE_SIZE;
    }


    getAuthString() {
        return this.authString;
    }
    setAuthString(value) {
        this.authString = value;
    }


    getUrlPath() {
        return this.urlPath;
    }
    setUrlPath(value) {
        this.urlPath = value;
    }


    getPageSize() {
        return this.pageSize;
    }
    setPageSize(value) {
        this.pageSize = value;
    }


    getDefaultScaleType() {
        return SCALE_TYPE_WINDOW;
    }
    getDefaultScaleFactor() {
        return DEFAULT_SCALE_FACTOR;
    }
    getDefaultWindow() {
        return DEFAULT_WINDOW;
    }
}

