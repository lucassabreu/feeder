var script = document.createElement("script");

script.src = "/js/jquery.min.js";
document.body.appendChild(script);

script.onload = function() {
    console.log("jQuery loaded...");
};

var container = document.createElement("div");
document.body.appendChild(container);

var RestfulAPI = function(baseUrl) {
    this.baseUrl = baseUrl;
};

RestfulAPI.prototype = {
    baseUrl: null,
    lastRequest: null,
    get: function(uri, params) {
        if (params !== undefined)
            return this.send("GET", uri + "?" + this.parseParams(params).substring(1));
        else
            return this.send("GET", uri);
    },
    post: function(uri, params) {
        return this.send("POST", uri, params);
    },
    delete: function(uri, params) {
        return this.send("DELETE", uri, params);
    },
    put: function(uri, params) {
        return this.send("PUT", uri, params);
    },
    options: function(uri, params) {
        return this.send("OPTIONS", uri ? uri : "", params);
    },
    parseParams: function(params) {
        var strParams = "";
        for (var key in params) {
            strParams += "&" + key + "=" + encodeURIComponent(params[key]);
        }
        return strParams;
    },
    send: function(method, uri, params) {
        xmlHttp = new XMLHttpRequest();
        xmlHttp.open(method, this.baseUrl + uri, false);

        if (method == "POST")
            xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xmlHttp.send(this.parseParams(params));

        this.lastRequest = xmlHttp;

        if (xmlHttp.getResponseHeader("Content-type").indexOf("application/json") != -1)
            return jQuery.parseJSON(xmlHttp.responseText);

        if (xmlHttp.getResponseHeader("Content-type").indexOf("text/xml") != -1)
            return xmlHttp.responseXML;

        return xmlHttp.responseText;
    },
};

if (!reloadHelp) {
    function reloadHelp() {
        var script = document.createElement("script");
        script.src = "/js/help.js";
        script.onload = function() {
            console.log("Reload complete...");
        };
        document.body.appendChild(script);
    }
}
