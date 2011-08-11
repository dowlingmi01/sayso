
(function () {
    var kynetxAppId = 'a239x15',
        studyData = null;
    $K.ajax({
        dataType : 'jsonp',
        url : 'http://local.sayso.com/admin/data',
        success : function (response) {
            //console.log(response);
            studyData = response.data;
            app = KOBJ.get_application(kynetxAppId); // <-- appRID is passed to JS from 'global' section in ruleset
            app.raise_event('study_data', { 'study_data' : JSON.stringify(response.data) });
        }
    });
})();
