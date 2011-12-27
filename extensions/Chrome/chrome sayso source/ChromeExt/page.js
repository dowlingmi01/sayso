chrome.extension.sendRequest({hostname: document.location.hostname}, function(response) {
//  console.log(response);

    if (response.plantTags) {
        var d = document;
        if (d.body) {

            var planted = d.getElementById("kynetx_runtime_planted");
            if (planted == null) {
                var s = d.createElement('script');
                s.text = response.full_runtime;
                d.body.appendChild(s);

                s = d.createElement('script');
                s.id = "kynetx_runtime_planted";
                d.body.appendChild(s);
            }

            var s = d.createElement('script');
            s.text = "KOBJ.add_config_and_run(" + JSON.stringify(response.config) + ");";
            d.body.appendChild(s);

        }
    }
});
