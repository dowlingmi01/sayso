<?php
/**
 * Class to find qualifying studies for the current user
 * 
 * @author davidbjames
 *
 */
class Sql_GetStudies extends Sql_Abstract
{
    /**
     * @var Study_Collection
     */
    protected $_collection = null;
    
    /**
     * Create a UserCollection
     */
    public function init ()
    {
        $this->_collection = new Study_Collection();
    }
    
    /**
     * @see SqlAbstract::build()
     * @param array|Iterator $traversableData
     * @return Study
     */
    public function build (& $data, $builder = null)
    {
        $study = $this->_collection->getItem($data['study_id'], new Study());
        /* @var $study Study */
        if (!$study->hasId()) {
            $study->build($data);
        }
        
        // @todo add search and social activity settings
        
        $cell = $study->getCells()->getItem($data['study_cell_id'], new Study_Cell());
        /* @var $cell Study_Cell */
        if (!$cell->hasId()) {
            $cell->build($data);
            $study->addCell($cell);
        }
        
        $tag = $cell->getTags()->getItem($data['study_tag_id'], new Study_Tag());
        /* @var $tag Study_Tag */
        if (!$tag->hasId()) {
            $tag->build($data);
            $cell->addTag($tag);
        }
        
        $domain = $tag->getDomains()->getItem($data['study_domain_id'], new Study_Domain());
        /* @var $domain Study_Domain */
        if (!$domain->hasId()) {
            $domain->build($data);
            $tag->addDomain($domain);
        }
        
        $creative = $tag->getCreatives()->getItem($data['study_creative_id'], new Study_Creative());
        if (!$creative->hasId() && $data['study_creative_id']) { // <-- there may not be creatives
            $creative->build($data);
            $tag->addCreative($creative);
        }
        
        return $study;
    }
}
