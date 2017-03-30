var N3 = require('n3');
var http = require('follow-redirects').http;
var stream = require('stream');

class Consumer extends stream.Writable {
    constructor() {
        super();
        this.triples = [];
        this.defaultRequestParams = {
            family: 6,
            port: 3000
        };
        this.streamParser = N3.StreamParser();
    }

    performRequest(argument) {
        let path = '/parking';
        if (argument !== undefined) {
            path += '?' + argument;
        }
        let params = this.defaultRequestParams;
        params.path = path;
        http.request(params, (res) => {
            res.pipe(this.streamParser);
            this.streamParser.pipe(this);
        }).end();
    }

    write(triple) {
        // TODO decide what happens with triple here (is it a prev/next link, is it a recording, ...)
        this.triples.push(triple);
    }

    logTriples() {
        console.log("LOGGING");
        console.log(this.triples);
    }
}

let consumer = new Consumer();
consumer.performRequest();
setTimeout(function() {
    consumer.logTriples();
}, 1000);
