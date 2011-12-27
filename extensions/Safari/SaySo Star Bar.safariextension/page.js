if (true) { // (window.top === window) {


    function handleMessage(msgEvent) {
        var messageName = msgEvent.name;
        var messageData = msgEvent.message;


        if (messageData.plantTags) {
            var d = document;
            if (d.body) {

                var planted = d.getElementById("kynetx_runtime_planted");
                if (planted == null) {
                    var s = d.createElement('script');
                    s.text = messageData.full_runtime;
                    d.body.appendChild(s);

                    s = d.createElement('script');
                    s.id = "kynetx_runtime_planted";
                    d.body.appendChild(s);
                }

                var s = d.createElement('script');
                s.text = "KOBJ.add_config_and_run(" + JSON.stringify(messageData.config) + ");";
                d.body.appendChild(s);

                safari.self.removeEventListener("message", handleMessage, false);
            }
        }

    }

    safari.self.addEventListener("message", handleMessage, false);

    safari.self.tab.dispatchMessage("loadruntime", {hostname : window.location.hostname});

}
