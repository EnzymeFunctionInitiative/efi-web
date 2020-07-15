

class UniRef {
    constructor(uniRefVersion = false, uniRefId = "") {
        this.uniRefVersion = uniRefVersion;
        this.uniRefId = uniRefId;
        console.log("UNIREF INIT: " + uniRefVersion + " " + uniRefId);
    }

    setVersion(version) {
        this.uniRefVersion = version;
    }
    getVersion() {
        return this.uniRefVersion;
    }
    getId() {
        return this.uniRefId;
    }

    getRequestParams() {
        var idType = this.uniRefVersion ? this.uniRefVersion : "uniprot";
        var idTypeParm = idType ? "&id-type=" + idType : "";
        if (this.uniRefId)
            idTypeParm += "&uniref-id=" + this.uniRefId;
        return idTypeParm;
    }
    // When going down to the next level (e.g. UR50->UR90, UR90->UP), use this function
    updateLinkRequestParams(uniRefId, params) {
        var nextIdType = this.uniRefVersion == 50 ?
                            (this.uniRefId ? 90 : 50) :
                        (this.uniRefVersion == 90 ?
                            (this.uniRefId ? "" : 90) :
                            ""
                        );
        params.set("id-type", nextIdType);
        //var nextIdTypeParm = nextIdType ? "&id-type=" + nextIdType : "";
        if (nextIdType)
            params.set("uniref-id", uniRefId);
            //nextIdTypeParm += "&uniref-id=" + uniRefId;
        //return nextIdTypeParm;
    }
    showUniRefExpandButton(data) {
        var ur50sz = data.hasOwnProperty("uniref50_size") ? data.uniref50_size : 0;
        var ur90sz = data.hasOwnProperty("uniref90_size") ? data.uniref90_size : 0;
        var size = 0;
        if (this.uniRefVersion == 50) {
            size = this.uniRefId ? ur90sz : ur50sz;
        } else if (this.uniRefVersion == 90) {
            size = this.uniRefId ? 0 : ur90sz;
        }
        return size > 1;
    }
    showUniRefTitleInfo() {
        if (this.uniRefVersion === false || this.uniRefVersion < 50)
            return false;
        var showTitle = this.uniRefVersion == 50 ?
                            true :
                        (this.uniRefVersion == 90 ?
                            (this.uniRefId ? false : true) :
                            false
                        );
        return showTitle;
    }
    getUniRefSizeFieldInfo(data) {
        var ur50sz = data.hasOwnProperty("uniref50_size") ? data.uniref50_size : 0;
        var ur90sz = data.hasOwnProperty("uniref90_size") ? data.uniref90_size : 0;
        var info = {};
        if (this.uniRefVersion == 50) {
            if (this.uniRefId) {
                info.ValueFieldName = "UniProt";
                info.UniRefType = "UniRef90";
                info.UniRefSize = ur90sz;
            } else {
                info.ValueFieldName = "UniRef90";
                info.UniRefType = "UniRef50";
                info.UniRefSize = ur50sz;
            }
        } else if (this.uniRefVersion == 90) {
            if (this.uniRefId) {
                info.ValueFieldName = "";
                info.UniRefType = "";
                info.UniRefSize = 0;
            } else {
                info.ValueFieldName = "UniProt";
                info.UniRefType = "UniRef90";
                info.UniRefSize = ur90sz;
            }
        } else {
            info.ValueFieldName = "";
            info.UniRefType = "";
            info.UniRefSize = 0;
        }
        return info;
    }
}

