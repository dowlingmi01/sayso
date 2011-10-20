<?php
/**
 * Class representing a Starbar Game
 * 
 */
class Starbar_Game extends Collection
{
    protected $_levels;

    public function setLevels (Collection $levels = null) {
        if (!$levels) {
	        $client = Gaming_BigDoor_HttpClient::getInstance('2107954aa40c46f090b9a562768b1e18', '76adcb0c853f486297933c34816f1cd2');
	        $client->getNamedLevelCollection(43352);
	        $data = $client->getData();
	        $levels = new Collection();
	        foreach ($data->named_levels as $levelData) {
	            $level = new Gaming_BigDoor_Level();
	            $level->setId($levelData->id);
	            $level->title = $levelData->end_user_title;
	            $level->description = $levelData->end_user_description;
	            $level->urls = Gaming_BigDoor_Url::buildUrlCollection($levelData->urls);
	            $level->timestamp = $levelData->created_timestamp;
	            $level->ordinal = $levelData->threshold;
	            $levels[] = $level;
	        }
		}
        $this->_levels = $levels;
    }
    
    /**
     * @return Levels
     */
    public function getLevels () {
        return $this->_levels;
    }

    public function exportData() {
        return array();
    }
    
    public function exportProperties($parentObject = null) {
        $props = array(
            '_game' => $this->_game
        );
        return array_merge(parent::exportProperties($parentObject), $props);
    }
}
