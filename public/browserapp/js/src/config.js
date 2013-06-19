window.sayso = { module: {}, webportal: true };
if (location.host.indexOf("saysollc.com") !== -1 && location.host != "app.saysollc.com") { // testing server
    sayso.base_domain = location.host;
} else {
	sayso.base_domain = "app.saysollc.com";
}
