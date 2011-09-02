<?php

class Admin_DataController extends Api_AbstractController
{

    protected $_jsonSample = '{"type":"ADjuster","tagdomain":{"38191":{"label":"foobar","tag":"asdf","domain":{"40638":{"name":"asdf.com","id":40638},"86801":{"name":"xczv.com","id":86801}},"id":38191}},"domainAvail":{"85256":{"label":"test","tag":"test","domain":{"99958":{"name":"test.com","id":99958},"22209":{"name":"asdf","id":22209}},"id":85256},"24805":{"label":"another avail","tag":"avaiil avail!","domain":{"19527":{"name":"asdf","id":19527},"45874":{"name":"qwer","id":45874}},"id":24805}},"creative":{"17664":{"name":"test","creativeUrl":"http://whereamiapp.com/image.php?key=959a6853f043ec34f0167250bfb5293c&id=187&size=200&max","contentType":"2","targetUrl":"http://www.google.com/","domainAvail":[85256],"id":17664},"22277":{"name":"big creative","creativeUrl":"http://whereamiapp.com/image.php?key=06c83b46a4db9fe40270faefb7e58726&id=16&size=380&max","contentType":"2","targetUrl":"http://www.google.com/","domainAvail":[85256,24805],"id":22277}},"metrics":{"clicktrack":"Yes","searchengines":{"Bing":"No","Google":"No","Yahoo!":"Yes"},"social":{"facebookLike":"No","tweet":"Yes"}},"surveyinfo":{"type":"Standard Survey","tag":"","deliverIf":{"15993":{"domain":"Facebook.com","timeframe":"1 hour","id":15993}}},"basic":{"name":"My Study","id":"1234","size":"1000","minimum":"","begindate":"08/16/2011","enddate":"08/26/2011","issurvey":"yes"},"cells":{"44149":{"description":"My Cell","type":"Test","size":"100","deliverIf":"visited-similar-site","adtag":{"38191":{"label":"asdf","tag":"asdf","domain":{"40638":{"name":"asdf.com","id":40638},"86801":{"name":"xczv.com","id":86801}},"id":38191}},"quota":{"27286":{"gender":"Female","age":"18-24","ethnicity":"White","percent":100,"id":27286}},"qualifier":{"browse":{"75213":{"include":"Include","site":"asdf","timeframe":"1 week","id":75213}},"search":{"47349":{"include":"Include","term":"foo","timeframe":"1 week","which":{"bing":"No","google":"Yes","yahoo":"Yes"},"id":47349}}},"id":44149},"64961":{"description":"My Second Cell","type":"Test","size":"700","adtag":{},"quota":{"77422":{"gender":"Male","age":"18-24","ethnicity":"Asian","percent":50,"id":77422}},"qualifier":{"browse":{"27522":{"include":"Include","site":"foo.com","timeframe":"1 week","id":27522}},"search":{"62931":{"include":"Include","term":"qwer","timeframe":"1 week","which":{"bing":"No","google":"No","yahoo":"Yes"},"id":62931}}},"id":64961}}}';
    protected $_jsonSample2 = '{"type":"ADjuster","tagdomain":{},"domainAvail":{"85256":{"label":"test","tag":"test","domain":{"99958":{"name":"test.com","id":99958},"22209":{"name":"asdf","id":22209}},"id":85256},"24805":{"label":"another avail","tag":"avaiil avail!","domain":{"19527":{"name":"asdf","id":19527},"45874":{"name":"qwer","id":45874}},"id":24805}},"creative":{"17664":{"name":"test","creativeUrl":"http://whereamiapp.com/image.php?key=959a6853f043ec34f0167250bfb5293c&id=187&size=200&max","contentType":"2","targetUrl":"http://www.google.com/","domainAvail":[85256],"id":17664},"22277":{"name":"big creative","creativeUrl":"http://whereamiapp.com/image.php?key=06c83b46a4db9fe40270faefb7e58726&id=16&size=380&max","contentType":"2","targetUrl":"http://www.google.com/","domainAvail":[85256,24805],"id":22277}},"basic":{"name":"Test","id":"123","size":"10000","minimum":"100","begindate":"08/31/2011","enddate":"09/22/2011","issurvey":"no"},"metrics":{"clicktrack":"Yes","searchengines":{"Google":"Yes","Yahoo!":"Yes"},"searchengineIds":["2","3"],"social":{"Facebook Like":"Yes","Tweet":"Yes"},"socialIds":["1","2"]},"surveyinfo":{"type":"Custom Survey","url":"http://mysurvey.com/mysurvey1234","deliverIf":{"33155":{"domain":"Facebook.com","timeframe":"1 Day","timeframeId":"2","id":33155},"56947":{"domain":"asdf.com","timeframe":"1 Week","timeframeId":"3","id":56947}}},"quota":{"82395":{"gender":"Male","genderId":"1","age":"18-24","ageId":"2","ethnicity":"White","ethnicityId":"1","percent":"50%","percentId":"6","id":82395},"86155":{"gender":"Female","genderId":"2","age":"18-24","ageId":"2","ethnicity":"African American","ethnicityId":"2","percent":"25%","percentId":"3","id":86155},"62781":{"gender":"Male","genderId":"1","age":"25-34","ageId":"3","ethnicity":"African American","ethnicityId":"2","percent":"25%","percentId":"3","id":62781}},"cells":{"86075":{"description":"My Cell","type":"Control","size":"100","deliverIf":"visited-similar-site","adtag":[85256,24805],"qualifier":{"browse":{"74098":{"include":"Include","site":"foo.com","timeframe":"1 Week","timeframeId":"3","id":74098},"9655":{"include":"Exclude","site":"bar.com","timeframe":"1 Hour","timeframeId":"1","id":9655}},"search":{"27355":{"include":"Include","term":"foo","timeframe":"1 Month","timeframeId":"4","which":{"bing":"No","google":"Yes","yahoo":"Yes"},"whichIds":["2","3"],"id":27355}}},"id":86075},"92523":{"description":"Another","type":"Test","size":"300","adtag":[85256],"qualifier":{"browse":{"64293":{"include":"Exclude","site":"foo.com","timeframe":"1 Day","timeframeId":"2","id":64293}},"search":{"46499":{"include":"Include","term":"asdf","timeframe":"Timeframe","timeframeId":"","which":{"bing":"Yes","google":"No","yahoo":"No"},"whichIds":["1"],"id":46499}}},"id":92523}}}';
    
    public function init()
    {
        
    }

    public function indexAction()
    {
        
        $object = new Object(array('test' => 'this is my test!', 'foo' => new Object(array('bar' => 'baz', 'bop' => new Object(array('a' => 1, 'b' => 2, 'c' => 3))))));
        return $this->_resultType($object);
    }

    public function sampleAction () {
        if ($this->_request->getParam(Api_Constant::JSONP_CALLBACK)) {
            header('Content-type: text/javascript');
            $jsonpCallback = $this->_request->getParam(Api_Constant::JSONP_CALLBACK);
            exit($jsonpCallback . '(' . $this->_jsonSample2 . ');');
        } else {
            header('Content-type: application/json');
            exit($this->_jsonSample2);
        }
    }
}

