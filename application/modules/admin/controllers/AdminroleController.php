<?php
/**
 * @author alecksmart
 */
require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Admin_AdminroleController extends Admin_CommonController
{
    public function init()
    {
        parent::init();

        if (!$this->_request->isXmlHttpRequest())
        {
            $this->setLayoutBasics();
        }
    }

    public function adminuserAction()
    {
        if(!$this->checkAccess(array('superuser')))
        {
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }

        $filter = new Zend_Filter_Int();
        $userId = $filter->filter($this->_getParam('entry_id'));
        $entry = new AdminUser();
        $entry->loadData($userId);
        if(false === $entry->getId() > 0)
        {
            $this->msg->addMessage('Error: user not found!');
            $this->rd->gotoSimple('index');
        }
        if($entry->getId() == $this->currentUser->getId())
        {
            $this->msg->addMessage('Error: you cannot edit yourself!');
            $this->rd->gotoSimple('index', 'user');
        }

        $this->view->entry = $entry;

        $this->view->headScript()->appendFile('/modules/admin/adminrole/adminuser.js');
        $this->view->indexLink = '<a href="' . $this->view->url(array('action' => 'index', 'controller'=>'user'))
            . '">Back to All Admins</a>';
        $this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add', 'controller'=>'user'))
            . '">Create A New Admin</a>';



        /*$grid   = new Data_Markup_Grid();
        $select = Zend_Registry::get('db')->select()->from('admin_user');
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setGridColumns(array('id', 'email', 'first_name', 'last_name', 'created', 'modified', 'edit', 'roles', 'delete'));

        $extraColumnEdit = new Bvb_Grid_Extra_Column();
		$extraColumnEdit
			->position('right')
			->name('edit')
			->title(' ')
			->callback(
                array(
                    'function'  => array($this, 'generateEditButtonLink'),
                    'params'    => array('{{id}}')
                )
            );
        $grid->addExtraColumns($extraColumnEdit);

        $extraColumnEditRoles = new Bvb_Grid_Extra_Column();
		$extraColumnEditRoles
			->position('right')
			->name('roles')
			->title(' ')
			->callback(
                array(
                    'function'  => array($this, 'generateEditRolesLink'),
                    'params'    => array('{{id}}')
                )
            );
        $grid->addExtraColumns($extraColumnEditRoles);

        $extraColumnDelete = new Bvb_Grid_Extra_Column();
		$extraColumnDelete
			->position('right')
			->name('delete')
			->title(' ')
			->callback(
                array(
                    'function'  => array($this, 'generateDeleteButtonLink'),
                    'params'    => array('{{id}}')
                )
            );
        $grid->addExtraColumns($extraColumnDelete);

        $grid->updateColumn('id',
			array(
                'class' => 'align-right'
			)
		);
        $grid->updateColumn('email',
			array(
				'callback' => array(
					'function'  => array($this, 'generateEditLink'),
					'params'    => array('{{id}}', '{{email}}')
				),
                'class' => 'align-left important'
			)
		);

        $this->view->grid = $grid->deploy();*/
    }

}
