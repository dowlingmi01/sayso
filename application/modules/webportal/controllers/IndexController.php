<?php
/**
 * Actions in this controller are for the webportal. Most of it is branching logic for themes, dev/prod, etc.
 */
require_once APPLICATION_PATH . '/modules/api/controllers/GlobalController.php';

class Webportal_IndexController extends Api_GlobalController
{

    public function preDispatch() {
        // branching logic for including generic css and portal themed css
        // $this->view->headLink()->appendStylesheet('/css/webportal/generic.css');
        // $this->view->headLink()->appendStylesheet('/css/webportal/machinima.css');
    }
}
