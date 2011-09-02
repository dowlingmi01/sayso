<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_StudyController extends Api_GlobalController
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
        $this->_validateRequiredParameters(array('data', 'user_id'));
        $this->_authenticateUser(false, true); // true = admin user
        $data = json_decode($this->data);
        $type = $data->type;
        
        switch ($type) {
            case 'ADgregator' :
                $tagsDomainsData = $data->tagdomain;
                break;
            case 'ADjuster' :
                $tagsDomainsData = $data->domainAvail;
                break;
            default :
                // ?
        }
        
        //Record::beginTransaction();
        
        // study
        
        $study = new Study();
        $study->user_id = $this->user_id;
        $study->name = $data->basic->name;
        $study->size = $data->basic->size;
        $study->size_minimum = $data->basic->minimum;
        $study->begin_date = convertLameDateFormat($data->basic->begindate);
        $study->end_date = convertLameDateFormat($data->basic->enddate);
        $study->click_track = $data->metrics->clicktrack === 'Yes' ? 1 : 0;
        $study->save();
        
        // search engines
        
        foreach ($data->metrics->searchengineIds as $searchEngineId) {
            $map = new Study_SearchEnginesMap();
            $map->study_id = $study->getId();
            $map->search_engines_id = $searchEngineId;
            $map->save();
        }
        
        // social activity types
        
        foreach ($data->metrics->socialIds as $socialId) {
            $map = new Study_SocialActivityTypeMap();
            $map->study_id = $study->getId();
            $map->social_activity_type_id = $socialId;
            $map->save();
        }
        
        // survey
        
        if ($data->surveyinfo && $data->surveyinfo->url) { // for now only support surveys with urls
            $survey = new Study_Survey();
            $survey->url = $data->surveyinfo->url;
            $survey->setStudy($study);
            foreach ($data->surveyinfo->deliverIf as $deliverIf) {
                $criterion = new Study_SurveyCriterion();
                $criterion->site = $deliverIf->domain;
                $criterion->timeframe_id = $deliverIf->timeframeId;
                $survey->addCriterion($criterion);
            }
            $survey->save();
        }
        
        // tags / domains
        
        $tags = new Study_Collection_Tag();
        $tagsByClientIds = array(); // Tag objects by client side guids 
        foreach ($tagsDomainsData as $tagClientId => $tagDomainData) {
            $tag = new Study_Tag();
            $tag->name = $tagDomainData->label;
            $tag->tag = $tagDomainData->tag;
            $tag->user_id = $this->user_id;
            $tags->addItem($tag);
            // this is used below for ADjuster to grab the correct mapped tag
            $tagsByClientIds[$tagClientId] = $tag;
            if (!empty($tagDomainData->domain)) {
                // add domains for this Tag
                foreach ($tagDomainData->domain as $domainData) {
                    $domain = new Study_Domain();
                    $domain->domain = $domainData->name;
                    $domain->user_id = $this->user_id;
                    $tag->addDomain($domain);
                }
            }
        }
        $tags->save();
        
        // creatives
        
        if ($type === 'ADjuster') {
            $creatives = new Study_Collection_Creative();
            // don't resave the tags, just the mappings see Study_Creative 
            Study_Creative::$saveTagsOnSave = false; 
            foreach ($data->creative as $creativeData) {
                $creative = new Study_Creative();
                $creative->user_id = $this->user_id;
                $creative->mime_type_id = $creativeData->contentType;
                $creative->name = $creativeData->name;
                $creative->url = $creativeData->creativeUrl;
                $creative->target_url = $creativeData->targetUrl;
                // associate Study for this Creative
                $creative->setStudy($study);
                $creatives->addItem($creative);
                // add Tags mapped to this Creative
                if (!empty($creativeData->domainAvail)) {
                    foreach ($creativeData->domainAvail as $domainAvailClientId) {
                        $creative->addTag($tagsByClientIds[$domainAvailClientId]);
                    }
                }
                
            } 
            $creatives->save();
        }
        
        //Record::commitTransaction();
        
        return $this->_resultType($tags);
       
        
    }
}

function convertLameDateFormat ($date) {
    $parts = explode('/', $date);
    return $parts[2] . '-' . $parts[0] . '-' . $parts[1];
}
