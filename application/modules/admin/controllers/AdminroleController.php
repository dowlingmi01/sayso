<?php
/**
 * @author alecksmart
 */
require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Admin_AdminroleController extends Admin_CommonController
{
	/**
	 * @var AdminUser_AdminRoleCollection
	 */
	private $allAdminUserRoles;

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

		$form   = new Form_AdminUser_Role();
		$select = Zend_Registry::get('db')->select()->from('admin_role');

		if ($this->_request->isPost())
		{			
			try
			{
				// validate
				$roles = !isset($_POST['admin_roles']) || !is_array($_POST['admin_roles'])
					? array()
					: $_POST['admin_roles'];
				if(!$form->validateRoles($select, $roles))
				{
					throw new Exception("Error: The form data cannot pass validation!");
				}

				//  write
				Record::beginTransaction();
				$entry->saveAdminRoles($roles);
				Record::commitTransaction();
				$this->msg->addMessage('Success: user roles updated!');
				$this->rd->gotoSimple('index', 'user');
			}
			catch(Exception $e)
			{
				$this->msg->addMessage('Error: user roles cannot be updated!');
				if(getenv('APPLICATION_ENV') != 'production')
				{
					$this->msg->addMessage($e->getMessage());					
				}
				$this->rd->gotoSimple('adminuser', 'adminrole', 'admin', array('entry_id'=>$userId));
			}
		}

		$this->allAdminUserRoles = $entry->getAdminRoles();

		$grid = new Data_Markup_Grid();		
		$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
		$grid->setGridColumns(array('id', 'name', 'description', 'action'));

		$extraColumnEdit = new Bvb_Grid_Extra_Column();
		$extraColumnEdit
			->position('right')
			->name('action')
			->title(' ')
			->callback(
				array(
					'function'  => array($this, 'generateCbSelected'),
					'params'	=> array('{{id}}')
				)
			);
		$grid->addExtraColumns($extraColumnEdit);
		
		$form->setGrid($grid);
		$form->initDeferred();
		$this->view->form = $form;
	}

	public function generateCbSelected($id)
	{
		$checked = '';
		if(!empty($this->allAdminUserRoles))
		{
			foreach($this->allAdminUserRoles as $role)
			{
				if($role->getId() == $id)
				{
					$checked = ' checked="checked"';
				}
			}
		}
		return sprintf('<input type="checkbox" name="admin_roles[]" value="%d" id="admin_role_%s"%s />', $id, $id, $checked);
	}

}
