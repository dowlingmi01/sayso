<?php
/**
 * Class to find qualifying studies for the current user
 * 
 * @author davidbjames
 *
 */
class Sql_GetQualifyingStudies extends Sql_Abstract
{
    /**
     * Create a UserCollection
     */
    public function init ()
    {
        $this->_collection = new Study_Collection();
    }
    
    /**
     * Query by User id
     * 
     * @param int $userId
     */
    public function setUserId ($userId)
    {
        $this->user_id = $userId;
    }
    
    /**
     * @return Collection
     */
    public function run () {
        $userSql = "
        	SELECT 
    			gender_id, 
    			ethnicity_id, 
    			income_range_id, 
    			(date_format(now(),'%Y') - date_format(birthdate,'%Y')) - (date_format(now(),'00-%m-%d') < date_format(birthdate,'00-%m-%d')) AS age
			FROM user 
			WHERE id = ?";
        $userData = Db_Pdo::fetch($userSql, $this->user_id);
        if (!$userData) { 
            throw new Exception(get_class($this) . ' cannot find user with ID ' . $this->user_id);
        }
        $this->gender_id = $userData['gender_id'];
        $this->ethnicity_id = $userData['ethnicity_id'];
        $this->income_range_id = $userData['income_range_id'];
        $this->age = $userData['age'];
        return parent::run();
    }
    
    /**
     * @see SqlAbstract::build()
     * @param array|Iterator $traversableData
     * @return Study
     */
    public function build (& $data, $builder = null)
    {
        $study = new Study();
        $study->build($data);
        return $study;
    }
}
