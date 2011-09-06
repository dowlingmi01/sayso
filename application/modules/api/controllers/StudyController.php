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

    public function validateAction () {
        $this->_validateRequiredParameters(array('user_id', 'study_id'));
        // @todo check if study date is still valid
        return $this->_resultType(true);
    }
    
    public function getAction () {
        $this->_validateRequiredParameters(array('user_id'));
        
        // check if current study exists for this user
        $activeCellId = Study_CellAssignment::getActiveCellIdByUser($this->user_id);
        if ($activeCellId) {
            // user is part of an active study
        } else {
            // user not part of an active study
            // so find one to assign them to
            $builder = new Sql_GetQualifyingStudies();
            $builder->setUserId($this->user_id);
            $study = $builder->run()->getFirst();
            // where I got to: this is the initial query object that finds qualifying studies
            // for the current user .. there is much to add to it, as well as some post
            // processing of the result collection, and possibly some further queries
            // in order to determine the correct study.
            // in this example, I am just getting the first one (via getFirst() and returning it)
            // test URL is at http://local.sayso.com/api/study/get/user_id/1 (assumes user id 1 exists)
            return $this->_resultType($study);
        }
    }
    
    public function submitAction () {
        $this->_validateRequiredParameters(array('data', 'user_id'));
        $this->_authenticateUser(false, true); // true = admin user
        $data = json_decode($this->data);
        $type = $data->type;
        
        switch ($type) {
            case 'ADjuster' :
                $tagsDomainsData = $data->domainAvail;
                break;
            case 'ADgregator' :
            default :
                $tagsDomainsData = $data->tagdomain;
                break;
        }
        
        Record::beginTransaction();
        
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
        
        // quotas
        
        foreach ($data->quota as $quotaData) {
            $quota = new Study_Quota();
            $quota->study_id = $study->getId();
            $quota->percentile_id = $quotaData->percentId;
            $quota->gender_id = $quotaData->genderId;
            $quota->age_range_id = $quotaData->ageId;
            $quota->ethnicity_id = $quotaData->ethnicityId;
            // $quota->income_range_id // not currently supported in admin tool
            $quota->save();
        }
        
        
        // cells
        
        foreach ($data->cells as $cellData) {
            $cell = new Study_Cell();
            $cell->study_id = $study->getId();
            $cell->size = $cellData->size;
            $cell->cell_type = $cellData->type;
            $cell->save();
            foreach ($cellData->adtag as $adTagId) {
                $tag = $tagsByClientIds[$adTagId];
                /* @var $tag Study_Tag */
                $map = new Study_CellTagMap();
                $map->cell_id = $cell->getId();
                $map->tag_id = $tag->getId();
                $map->save();
            }
            if ($cellData->qualifier) {
                if ($cellData->qualifier->browse) {
                    foreach ($cellData->qualifier->browse as $browseQualifierData) {
                        $browseQualifier = new Study_CellBrowsingQualifier();
                        $browseQualifier->cell_id = $cell->getId();
                        if ($browseQualifierData->include === 'Exclude') $browseQualifier->exclude = 1;
                        $browseQualifier->site = $browseQualifierData->site;
                        if ($browseQualifierData->timeframeId) $browseQualifier->timeframe_id = $browseQualifierData->timeframeId;
                        $browseQualifier->save(); 
                    }
                }
                if ($cellData->qualifier->search) {
                    foreach ($cellData->qualifier->search as $searchQualifierData) {
                        $searchQualifier = new Study_CellSearchQualifier();
                        $searchQualifier->cell_id = $cell->getId();
                        if ($searchQualifierData->include === 'Exclude') $searchQualifier->exclude = 1;
                        $searchQualifier->term = $searchQualifierData->term;
                        if ($searchQualifierData->timeframeId) $searchQualifier->timeframe_id = $searchQualifierData->timeframeId;
                        $searchQualifier->save(); 
                        if ($searchQualifierData->whichIds) {
                            foreach ($searchQualifierData->whichIds as $searchEngineId) {
                                $map = new Study_CellSearchQualifierMap();
                                $map->cell_qualifier_search_id = $searchQualifier->getId();
                                $map->search_engines_id = $searchEngineId;
                                $map->save();
                            }
                        }
                    }
                }
            }
        }
        
        Record::commitTransaction();
        
        return $this->_resultType($study);
       
        
    }
}

function convertLameDateFormat ($date) {
    $parts = explode('/', $date);
    return $parts[2] . '-' . $parts[0] . '-' . $parts[1];
}
