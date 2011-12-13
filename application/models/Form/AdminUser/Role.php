<?php
/**
 * Administrative user login form handling
 *
 * @author alecksmart
 */
class Form_AdminUser_Role extends Zend_Form
{
	public $grid;

	public function setGrid(Bvb_Grid $grid)
	{
		$this->grid = $grid;
	}

	/**
	 * @todo Create validation for passed values
	 * @param Zend_Db_Select $allRoles
	 * @param array $post 
	 */
	public function validateRoles(Zend_Db_Select $allRoles, array $post)
	{
		return true;
	}

	public function initDeferred()
	{
		$this->setAttrib('id', 'entity');

		$freeElement = new Form_Markup_Element_AnyHtml('freeElement');
			$freeElement->setValue($this->grid->deploy())
				->removeDecorator('Label')
				->addDecorators(array(
					'ViewHelper',
					array('HtmlTag', array('tag' => 'div', 'class'=>'admin-table'))
		));

		$submitBtn = $this->createElement('submit', 'submitBtn')
			->setLabel('Save');

		$this->addElements(array(
			$freeElement,
			$submitBtn
		));
	}
}