<?php

class Api_StudyController extends Api_AbstractController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        return $this->_resultType(new Object(array('foo' => 'bar')));
    }

    public function submitAction () {
        $this->_enableRenderer($renderer = new Api_Plugin_JsonRenderer());
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
}

