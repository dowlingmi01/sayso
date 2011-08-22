<?php

class Admin_DataController extends Api_AbstractController
{

    protected $_jsonSample = '{"data" : {"type":"ADgregator","tagdomain":{"38191":{"label":"asdf","tag":"asdf","domain":{"40638":{"name":"asdf.com","id":40638},"86801":{"name":"xczv.com","id":86801}},"id":38191}},"domainAvail":{"31399":{"domain":"foo.com","tag":"my_div","id":31399},"77843":{"domain":"bar.com","tag":"my_other_div","id":77843}},"creative":{"57073":{"name":"Test","creativeUrl":"http://whereamiapp.com/image.php?key=06c83b46a4db9fe40270faefb7e58726&id=16&size=380&max","targetUrl":"http://www.google.com/","id":57073}},"metrics":{"clicktrack":"Yes","searchengines":{"Bing":"No","Google":"No","Yahoo!":"Yes"},"social":{"facebookLike":"No","tweet":"Yes"}},"surveyinfo":{"type":"Standard Survey","tag":"","deliverIf":{"15993":{"domain":"Facebook.com","timeframe":"1 hour","id":15993}}},"basic":{"name":"My Study","id":"1234","size":"1000","minimum":"","begindate":"08/16/2011","enddate":"08/26/2011","issurvey":"yes"},"cells":{"44149":{"description":"My Cell","type":"Test","size":"100","deliverIf":"visited-similar-site","adtag":{"38191":{"label":"asdf","tag":"asdf","domain":{"40638":{"name":"asdf.com","id":40638},"86801":{"name":"xczv.com","id":86801}},"id":38191}},"quota":{"27286":{"gender":"Female","age":"18-24","ethnicity":"White","percent":100,"id":27286}},"qualifier":{"browse":{"75213":{"include":"Include","site":"asdf","timeframe":"1 week","id":75213}},"search":{"47349":{"include":"Include","term":"foo","timeframe":"1 week","which":{"bing":"No","google":"Yes","yahoo":"Yes"},"id":47349}}},"id":44149},"64961":{"description":"My Second Cell","type":"Test","size":"700","adtag":{},"quota":{"77422":{"gender":"Male","age":"18-24","ethnicity":"Asian","percent":50,"id":77422}},"qualifier":{"browse":{"27522":{"include":"Include","site":"foo.com","timeframe":"1 week","id":27522}},"search":{"62931":{"include":"Include","term":"qwer","timeframe":"1 week","which":{"bing":"No","google":"No","yahoo":"Yes"},"id":62931}}},"id":64961}}}}';
    
    public function init()
    {
        $this->_enableRenderer($renderer = new Api_Plugin_JsonPRenderer());
    }

    public function indexAction()
    {
        
        $object = new Object(array('test' => 'this is my test!', 'foo' => new Object(array('bar' => 'baz', 'bop' => new Object(array('a' => 1, 'b' => 2, 'c' => 3))))));
        return $this->_resultType($object);
    }

    public function submitStudyAction () {
        $this->_validateRequiredParameters(array('data'));
        $data = json_decode($this->data);
        $type = $data->type;
        switch ($type) {
            case 'ADgregator' :
                $tags = new Study_Collection_Tag();
                foreach ($data->tagdomain as $tagDomainData) {
                    $tag = new Study_Tag();
                    $tag->name = $tagDomainData->label;
                    $tag->content = $tagDomainData->tag;
                    $tags->addItem($tag);
                    if (!empty($tagDomainData->domain)) {
                        foreach ($tagDomainData->domain as $domainData) {
                            $domain = new Study_Domain();
                            $domain->domain = $domainData->name;
                            $tag->addDomain($domain);
                        }
                    }
                }
                break;
            case 'ADjuster' :
                break;
        }
        $tags->save();
        return $this->_resultType($tags);
       
        
    }

    public function sampleAction () {
        if ($this->_request->getParam(Api_Constant::JSONP_CALLBACK)) {
            header('Content-type: text/javascript');
            $jsonpCallback = $this->_request->getParam(Api_Constant::JSONP_CALLBACK);
            exit($jsonpCallback . '(' . $this->_jsonSample . ');');
        } else {
            header('Content-type: application/json');
            exit($this->_jsonSample);
        }
    }
}

