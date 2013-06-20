var http = require('http'),
    qs = require('querystring');

var url = '127.0.0.1',
    port = 1337;

var server = http.createServer(function (req,res) {
    var body = "";
    req.on('data', function (chunk) {
        body += chunk;
    }).on('end', function () {
        var payload = JSON.parse(qs.parse(body).payload);
        var name = payload.repository.name;
        var docRoot = '/var/www/';

        console.log(payload.ref);
        if(payload.ref == 'refs/heads/master') {
            console.log("Check for "+docRoot+payload.repository.name);
            console.log("If No: sudo -u http git clone "+payload.repository.url+".git "+docRoot+payload.repository.name);
            console.log("If Yes: cd "+docRoot+payload.repository.name+" && sudo -u http git pull "+payload.repository.url+".git master");

    }

        res.writeHead(200);
        res.end();
    });   
}).listen(port, url);
console.log('Server running at http://'+url+':'+port+'/');
