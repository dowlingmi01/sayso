<?php

/**
 * Breadcrumb view helper
 *
 * This helper is automated and uses the
 * current module, controller & action to
 * generated the breadcrumb. A custom breadcrumb
 * can be specified, using the set method.
 *
 * @todo Refactor code to allow building of a "path"
 * to current location. Use this in conjuction with
 * "path building" idea to subcategorise controllers
 * and still have a nice breadcrumb trail.
 *
 * @author Steven Bakhtiari <steven@ctisn.com>
 * @licence Use at will - no strings.
 */
class Zend_View_Helper_BreadCrumb extends Zend_View_Helper_Abstract
{
    /**
     * Request Object
     *
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * Breadcrumb separator
     *
     * @var string
     */
    protected $_separator = '&rsaquo;';

    /**
     * Breadcrumb
     *
     * @var array
     */
    protected $_breadcrumb = array();

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $fc = Zend_Controller_Front::getInstance();
        $this->_request = $fc->getRequest();
    }

    /**
     * Set the breadcrumb separator
     *
     * @param string $separator
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;
    }

    /**
     * Set custom breadcrumb
     *
     * @param array $breadcrumb
     * @return My_View_Helper_Breadcrumb
     */
    public function set(array $breadcrumb)
    {
        $this->_breadcrumb = $breadcrumb;

        return $this;
    }

    /**
     * breadcrumb
     *
     * @param array $breadcrumb Set a custom breadcrumb
     * @return My_View_Helper_Breadcrumb
     */
    public function breadcrumb(array $breadcrumb = array())
    {
        if (empty($this->_breadcrumb)) {
            if (!empty($breadcrumb)) {
                $this->set($breadcrumb);
            } else {
                $module     = $this->_request->getModuleName();
                $controller = $this->_request->getControllerName();
                $action     = $this->_request->getActionName();

                if ($module != 'default') {
                    $this->_breadcrumb[] = array(
                        'title' => $module,
                        'url' => $this->view->url(array('module' => $module), 'default', true)
                    );
                }

                if ($controller != 'index') {
                    $this->_breadcrumb[] = array(
                        'title' => $controller,
                        'url' => $this->view->url(array('module' => $module, 'controller' => $controller), 'default', true)
                    );
                }

                if ($action != 'index') {
                    $this->_breadcrumb[] = array(
                        'title' => $action,
                        'url' => $this->view->url(array('module' => $module, 'controller' => $controller, 'action' => $action), 'default', true)
                    );
                }

                $this->_breadcrumb[count($this->_breadcrumb) - 1]['url'] = null;
            }
        }

        return $this;
    }

    /**
     * Compile and output the breadcrumb
     *
     * @return string
     */
    public function __toString()
    {
        if (count($this->_breadcrumb) == 1) {
            $breadcrumb = '';
        } else {
            $breadcrumb = '<ol class="breadcrumb">';

            foreach ($this->_breadcrumb as $i => $bc) {
                $breadcrumb .= '<li>' . ($i != 0 ? '<span>' . $this->_separator . '</span>' : null);

                if ($bc['url'] === null) {
                    $breadcrumb .= $this->view->escape($bc['title']);
                } else {
                    $breadcrumb .= '<a href="' . $bc['url'] . '">' . $this->view->escape($bc['title']) . '</a>';
                }

                $breadcrumb .= '</li>';
            }

            $breadcrumb .= '</ol>';
        }

        return $breadcrumb;
    }
}




