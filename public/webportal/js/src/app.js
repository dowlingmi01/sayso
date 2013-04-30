(function() {
    'use strict';

    var shared = {},
        baseUrl,
        client,
        version;

    baseUrl = 'say.so';
    client = '';
    version = '1.0';

    shared.baseUrl = baseUrl;
    shared.client = client;
    shared.version = version;

    return shared;
})();