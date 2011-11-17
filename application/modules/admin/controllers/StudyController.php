<?php

require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Admin_StudyController extends Admin_CommonController
{

    public function init()
    {
        parent::init();

        if (!$this->_request->isXmlHttpRequest())
        {
            $this->setLayoutBasics();
        }

    }

    public function indexAction()
    {
        if(!$this->checkAccess(array('superuser')))
        {
            $this->_helper->viewRenderer->setNoRender(true);
        }
        $this->view->headScript()->appendFile('/modules/admin/study/index.js');
        $this->view->headLink()->appendStylesheet('/modules/admin/study/module.css', 'screen');
        $this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add')) . '">Add New</a>';

        $columns    = array();
        $source     = array();
        $grid       = new Data_Markup_Grid();
        $grid->setCollectionSource($source, $columns);
        $this->view->grid = $grid->deploy();
    }

    public function addAction()
    {
        if(!$this->checkAccess(array('superuser')))
        {
            $this->_helper->viewRenderer->setNoRender(true);
        }
        $this->view->headScript()->appendFile('/modules/admin/study/add.js');
        $this->view->headLink()->appendStylesheet('/modules/admin/study/module.css', 'screen');
        $this->view->indexLink = '<a href="' . $this->view->url(array('action' => 'index')) . '">List Studies</a>';
    }

    public function editAction()
    {
        if(!$this->checkAccess(array('superuser')))
        {
            $this->_helper->viewRenderer->setNoRender(true);
        }
        $this->view->headScript()->appendFile('/modules/admin/study/edit.js');
        $this->view->headLink()->appendStylesheet('/modules/admin/study/module.css', 'screen');
        $this->view->indexLink = '<a href="' . $this->view->url(array('action' => 'index')) . '">List Studies</a>';
        $this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add')) . '">Add New</a>';
    }

    public function deleteAction()
    {
        if(!$this->checkAccess(array('superuser')))
        {
            $this->_helper->viewRenderer->setNoRender(true);
        }
    }

    /**
     * @deprecated To be deleted after addAction is done ...
     */
    public function createNewAction ()
    {
        if(!$this->checkAccess(array('superuser')))
        {
            die('Access denied!');
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $data       = $this->_getParam('data');
        $type       = $data['type'];

        $response   = array('result' => false, 'messages' => array());

        switch ($type) {
            case 'ADjuster Creative' :
                $tagsDomainsData = $data['domainAvail'];
                break;
            case 'ADjuster Campaign' :
            default :
                $tagsDomainsData = $data['tagdomain'];
                break;
        }

        // Transaction is closed within try{} block
        // and never comits in case of exception...
        Record::beginTransaction();

        try
        {
            // study

            $study = new Study();
            $study->user_id         = $this->currentUser->id;
            $study->name            = $data['basic']['name'];
            $study->size            = $data['basic']['size'];
            $study->size_minimum    = $data['basic']['minimum'];
            $study->begin_date      = convertLameDateFormat(isset($data['basic']['begindate']) ? strval($data['basic']['begindate']) : '' );
            $study->end_date        = convertLameDateFormat(isset($data['basic']['enddate']) ? strval($data['basic']['enddate']) : '');
            $study->click_track     = $data['metrics']['clicktrack'] === 'Yes' ? 1 : 0;
            $study->save();

            // search engines

            if(isset($data['metrics']) && isset($data['metrics']['searchengineIds']) && !empty($data['metrics']['searchengineIds']))
            {
                foreach ($data['metrics']['searchengineIds'] as $searchEngineId)
                {
                    $map                    = new Study_SearchEnginesMap();
                    $map->study_id          = $study->getId();
                    $map->search_engines_id = $searchEngineId;
                    $map->save();
                }
            }

            // social activity types

            if(isset($data['metrics']) && isset($data['metrics']['socialIds']) && !empty($data['metrics']['socialIds']))
            {
                foreach ($data['metrics']['socialIds'] as $socialId)
                {
                    $map                            = new Study_SocialActivityTypeMap();
                    $map->study_id                  = $study->getId();
                    $map->social_activity_type_id   = $socialId;
                    $map->save();
                }
            }

            // survey

            // for now only support surveys with urls
            if (isset($data['surveyinfo']) && isset($data['surveyinfo']['url']))
            {
                $survey             = new Study_Survey();
                $survey->url        = $data['surveyinfo']['url'];
                $survey->setStudy($study);
                foreach ($data['surveyinfo']['deliverIf'] as $deliverIf)
                {
                    $criterion                  = new Study_SurveyCriterion();
                    $criterion->site            = $deliverIf['domain'];
                    $criterion->timeframe_id    = $deliverIf['timeframeId'];
                    $survey->addCriterion($criterion);
                }
                $survey->save();
            }

            // tags / domains

            $tags = new Study_TagCollection();
            $tagsByClientIds = array(); // Tag objects by client side guids
            if(!empty($tagsDomainsData))
            {
                foreach ($tagsDomainsData as $tagClientId => $tagDomainData)
                {
                    $tag = new Study_Tag();
                    $tag->name      = $tagDomainData['label'];
                    $tag->tag       = $tagDomainData['tag'];
                    $tag->target_url = $tagDomainData['targetUrl'];
                    $tag->user_id   = $this->currentUser->id;
                    $tags->addItem($tag);

                    // this is used below for ADjuster to grab the correct mapped tag
                    $tagsByClientIds[$tagClientId] = $tag;
                    if (!empty($tagDomainData['domain']))
                    {
                        // add domains for this Tag
                        foreach ($tagDomainData['domain'] as $domainData)
                        {
                            $domain             = new Study_Domain();
                            $domain->domain     = $domainData['name'];
                            $domain->user_id    = $this->currentUser->id;
                            $tag->addDomain($domain);
                        }
                    }
                }
                $tags->save();
            }

            // creatives

            if ($type === 'ADjuster Creative')
            {
                $creatives = new Study_CreativeCollection();
                // don't resave the tags, just the mappings see Study_Creative
                Study_Creative::$saveTagsOnSave = false;
                foreach ($data['creative'] as $creativeData)
                {
                    $creative = new Study_Creative();
                    $creative->user_id      = $this->currentUser->id;
                    $creative->mime_type_id = $creativeData['contentType'];
                    $creative->name         = $creativeData['name'];
                    $creative->url          = $creativeData['creativeUrl'];
                    $creative->target_url   = $creativeData['targetUrl'];
                    // associate Study for this Creative
                    $creative->setStudy($study);
                    $creatives->addItem($creative);
                    // add Tags mapped to this Creative
                    if (!empty($creativeData['domainAvail'])) {
                        foreach ($creativeData['domainAvail'] as $domainAvailClientId)
                        {
                            $creative->addTag($tagsByClientIds[$domainAvailClientId]);
                        }
                    }
                }
                $creatives->save();
            }

            // quotas

            foreach ($data['quota'] as $quotaData)
            {
                $quota                  = new Study_Quota();
                $quota->study_id        = $study->getId();
                $quota->percentile_id   = $quotaData['percentId'];
                $quota->gender_id       = $quotaData['genderId'];
                $quota->age_range_id    = $quotaData['ageId'];
                $quota->ethnicity_id    = $quotaData['ethnicityId'];
                $quota->save();
            }

            // cells

            foreach ($data['cells'] as $cellData)
            {
                $cell               = new Study_Cell();
                $cell->study_id     = $study->getId();
                $cell->description  = $cellData['description'];
                $cell->size         = $cellData['size'];
                $cell->cell_type    = $cellData['type'];
                $cell->save();

                foreach ($cellData['adtag'] as $adTagId)
                {
                    $tag = $tagsByClientIds[$adTagId];
                    /* @var $tag Study_Tag */
                    $map            = new Study_CellTagMap();
                    $map->cell_id   = $cell->getId();
                    $map->tag_id    = $tag->getId();
                    $map->save();
                }
                if (isset($cellData['qualifier']))
                {
                    if (isset($cellData['qualifier']['browse']))
                    {
                        foreach ($cellData['qualifier']['browse'] as $browseQualifierData)
                        {
                            $browseQualifier            = new Study_CellBrowsingQualifier();
                            $browseQualifier->cell_id   = $cell->getId();
                            if ($browseQualifierData['include'] === 'Exclude')
                            {
                                $browseQualifier->exclude = 1;
                            }
                            $browseQualifier->site      = $browseQualifierData['site'];
                            if ($browseQualifierData['timeframeId'])
                            {
                                $browseQualifier->timeframe_id = $browseQualifierData['timeframeId'];
                            }
                            $browseQualifier->save();
                        }
                    }
                    if (isset($cellData['qualifier']['search']))
                    {
                        foreach ($cellData['qualifier']['search'] as $searchQualifierData)
                        {
                            $searchQualifier            = new Study_CellSearchQualifier();
                            $searchQualifier->cell_id   = $cell->getId();
                            if ($searchQualifierData['include'] === 'Exclude')
                            {
                                $searchQualifier->exclude = 1;
                            }
                            $searchQualifier->term      = $searchQualifierData['term'];
                            if ($searchQualifierData['timeframeId'])
                            {
                                $searchQualifier->timeframe_id = $searchQualifierData['timeframeId'];
                            }
                            $searchQualifier->save();
                            if ($searchQualifierData['whichIds'])
                            {
                                foreach ($searchQualifierData['whichIds'] as $searchEngineId)
                                {
                                    $map                            = new Study_CellSearchQualifierMap();
                                    $map->cell_qualifier_search_id  = $searchQualifier->getId();
                                    $map->search_engines_id         = $searchEngineId;
                                    $map->save();
                                }
                            }
                        }
                    }
                }
            }

            Record::commitTransaction();
            $response['result'] = true;
            $response['messages'][] = 'Survey saved!';
        }
        catch(Exception $e)
        {
            $response['result'] = false;
            $response['messages'][] = 'Exception occurred while saving new study';
            if(getenv('APPLICATION_ENV') != 'production')
            {
                $response['messages'][] = $e->getMessage();
            }
        }

        echo json_encode($response);
        exit(0);
    }
}

/**
 * Simple function to convert American-style dates
 * to dates that make sense. :)
 * (used in the above class)
 *
 * @param string $date
 *
 * @todo Create tools repo under application/models/Data/Tools, set as a static method and refactor usage
 *
 */
function convertLameDateFormat ($date)
{
    $parts = explode('/', $date);
    return
        count($parts) == 3
        ? ($parts[2] . '-' . $parts[0] . '-' . $parts[1])
        : '0000-00-00';
}
