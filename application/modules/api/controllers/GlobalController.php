<?php

class Api_GlobalController extends Api_AbstractController
{
    protected function _authenticateUser($targetUserMustMatch = false, $adminOnly = false) {
        if ($this->_authenticated) return;
        
        if (!$this->user_key)
        {
            // throw a missing key exception
            throw new Api_Exception(Api_Error::create(Api_Error::USER_KEY_MISSING));
        }
        $userSession = Api_UserSession::getInstance($this->user_key);
        
        // exception may be thrown here if session expired OR user doesn't exist
        $user = $userSession->getUser();
        
        $this->_authenticated = true;
        
        // if this call requires admins and the current user
        // is not an admin, then throw exception
        // @todo use ordinals instead of IDs 
        if ($adminOnly && $user->user_role_id < 4) 
        {
            throw new Api_Exception(Api_Error::create(Api_Error::ADMIN_ONLY_ACTION));
        }
        
    }
}

