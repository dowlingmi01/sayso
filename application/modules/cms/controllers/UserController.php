<?php
/**
 * @author alecksmart
 */

require_once APPLICATION_PATH . '/modules/admin/controllers/CommonController.php';

class Cms_UserController extends Admin_CommonController
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
			return;
		}

		$this->view->headScript()->appendFile('/modules/admin/user/index.js');
		$this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add')) . '">Create A New Admin</a>';

		$grid   = new Data_Markup_Grid();
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
					'params'	=> array('{{id}}')
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
					'params'	=> array('{{id}}')
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
					'params'	=> array('{{id}}')
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
					'params'	=> array('{{id}}', '{{email}}')
				),
				'class' => 'align-left important'
			)
		);

		$this->view->grid = $grid->deploy();
	}

	public function generateEditLink($id, $email)
	{
		$email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
		if($this->currentUser->getId() == $id)
		{
			return '<a href="javascript:alert(\'You cannot edit yourself...\');void(null);">'.
				($email ? $email : '<span class="disabled">email malformed</span>') .'</a>';
		}
		return '<a href="' . $this->view->url(array('action' => 'edit', 'entry_id' => intval($id))) . '">'.
			($email ? $email : '<span class="disabled">email malformed</span>') .'</a>';
	}

	public function generateEditRolesLink($id)
	{
		if($this->currentUser->getId() == $id)
		{
			return '-';
		}
		return '<a href="'
			. $this->view->url(array('controller' => 'adminrole', 'action' => 'adminuser', 'entry_id' => intval($id)))
			. '" class="button-roles" title="Edit Roles"></a>';
	}

	public function generateEditButtonLink($id)
	{
		if($this->currentUser->getId() == $id)
		{
			return '-';
		}
		return  '<a href="' . $this->view->url(array('action' => 'edit', 'entry_id' => intval($id)))
					. '" class="button-edit" title="Edit"></a>';
	}

	public function generateDeleteButtonLink($id)
	{
		if($this->currentUser->getId() == $id)
		{
			return '-';
		}
		return  '<a href="' . $this->view->url(array('action' => 'delete', 'entry_id' => intval($id)))
					. '" class="button-delete" title="Delete"></a>';
	}

	public function addAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			$this->_helper->viewRenderer->setNoRender(true);
			return;
		}

		$this->view->headScript()->appendFile('/modules/admin/user/add.js');
		$this->view->indexLink = '<a href="' . $this->view->url(array('action' => 'index')) . '">Back to All Admins</a>';

		$this->view->form = new Form_AdminUser_AddEdit();
		$this->view->form->buildDeferred();

		if ($this->_request->isPost() && $this->view->form->isValid($_POST))
		{
			Record::beginTransaction();
			try
			{
				$adminUser = new AdminUser();
				$values = $this->view->form->getValues();
				AdminUser::saveUserFromValues($adminUser, $values);
				Record::commitTransaction();
				$this->msg->addMessage('Success: entry saved!');
				$this->rd->gotoSimple('index');
			}
			catch(Exception $e)
			{
				$this->msg->addMessage('Error: entry cannot be saved!');
				if(getenv('APPLICATION_ENV') != 'production')
				{
					$this->msg->addMessage($e->getMessage());
				}
			}
		}
	}

	public function editAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			$this->_helper->viewRenderer->setNoRender(true);
			return;
		}

		$this->view->headScript()->appendFile('/modules/admin/user/add.js');

		$this->view->indexLink = '<a href="' . $this->view->url(array('action' => 'index')) . '">Back to All Admins</a>';
		$this->view->addLink = '<a href="' . $this->view->url(array('action' => 'add')) . '">Create A New Admin</a>';

		$entry = new AdminUser();
		$entry->loadData(intval($this->_getParam('entry_id')));

		if($entry->getId() == $this->currentUser->getId())
		{
			$this->msg->addMessage('Error: you cannot edit yourself!');
			$this->rd->gotoSimple('index');
		}

		if(false === $entry->id > 0)
		{
			throw new Exception('Bad parameters, possibly a security issue..!');
			$this->rd->gotoSimple('index');
		}
		$this->view->entry = $entry;
		$this->view->form   = new Form_AdminUser_AddEdit();
		$this->view->form->setUser($entry);
		$this->view->form->buildDeferred();

		if ($this->_request->isPost() && $this->view->form->isValid($_POST))
		{
			Record::beginTransaction();
			try
			{
				$values = $this->view->form->getValues();
				AdminUser::saveUserFromValues($entry, $values, 'update');
				Record::commitTransaction();
				$this->msg->addMessage('Success: entry saved!');
				$this->rd->gotoSimple('index');
			}
			catch(Exception $e)
			{
				$this->msg->addMessage('Error: entry cannot be saved!');
				if(getenv('APPLICATION_ENV') != 'production')
				{
					$this->msg->addMessage($e->getMessage());
					$this->view->render('user/edit.phtml');
				}
			}
		}
		else
		{
			$details = array(
				'txtLogin'	  => $entry->email,
				'passwPassword' => '',
				'txtFirstName'  => $entry->first_name,
				'txtLastName'   => $entry->last_name ,
			);
			$this->view->form->populate($details);
		}
	}

	public function deleteAction()
	{
		if(!$this->checkAccess(array('superuser')))
		{
			$this->_helper->viewRenderer->setNoRender(true);
			return;
		}

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		try
		{
			$entry = new AdminUser();
			$entry->loadData(intval($this->_getParam('entry_id')));

			if($entry->getId() == $this->currentUser->getId())
			{
				$this->msg->addMessage('Error: you cannot delete yourself!');
				$this->rd->gotoSimple('index');
			}

			if(false === $entry->id > 0)
			{
				throw new Exception('Bad parameters, possibly a security issue..!');
			}
			$entry->delete();
		}
		catch(Exception $e)
		{
			$this->msg->addMessage('Operation caused exception!');
			if(getenv('APPLICATION_ENV') != 'production')
			{
				$this->msg->addMessage($e->getMessage());
			}
			$this->rd->gotoSimple('index');
		}

		$this->msg->addMessage('Entry deleted!');
		$this->rd->gotoSimple('index');
	}

	public function loginAction()
	{
		if(!$this->_request->isXmlHttpRequest() || $this->auth->hasIdentity())
		{
			die('You are not supposed to be here...');
		}

		$this->_helper->layout->setLayout('empty');
		$form = new Form_AdminUser_Login();
		// String used for javascript
		$this->view->loginResult = array('ok' => false, 'message' => array());

		if ($this->_request->isPost() && $form->isValid($_POST))
		{
			try
			{
				$emailAddress = $form->getValue('txtLogin');
				$password = $form->getValue('passwPassword');

				$adapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('db'), 'admin_user', 'email', 'password', 'MD5(?)');
				$adapter->setIdentity($emailAddress)->setCredential($password);
				$result = $this->auth->authenticate($adapter);

				if($result->isValid())
				{
					$this->view->loginResult['ok'] = true;
				}
				else
				{
					$this->view->loginResult['message'][] = 'Login failed';
				}
			}
			catch(Exception $e)
			{
				$this->view->loginResult['message'][]   = 'Login failed with exception';
				if(getenv('APPLICATION_ENV') != 'production')
				{
					$this->view->loginResult['message'][] = $e->getMessage();
				}
			}
		}

		$this->view->form = $form;
	}

	public function logoutAction()
	{
		if(!$this->_request->isXmlHttpRequest())
		{
			$this->rd->gotoSimple('index', 'index', 'admin');
		}
		$this->auth->clearIdentity();
		exit(0);
	}

}
