<?php

require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Api_IndexController extends Api_GlobalController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        return $this->_resultType(new Object(array('foo' => 'bar')));
    }

    public function testBigDoorAction () {
        $key = Api_Registry::getConfig()->bigDoor->apiKey;
        $secret = Api_Registry::getConfig()->bigDoor->apiSecret;
        $client = new Gaming_BigDoor_HttpClient($key, $secret);
        $client->getNamedTransactionGroup(714413);
        Debug::exitNicely($client->getLastResponse()->getBody());
    }

}

