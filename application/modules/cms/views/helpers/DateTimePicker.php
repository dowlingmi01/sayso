<?php
/**
 * dateTimePicker
 * @author Darius Matulionis
 */

class Zend_View_Helper_dateTimePicker extends ZendX_JQuery_View_Helper_UiWidget
{

    public function dateTimePicker($id, $value = null, array $params = array(), array $attribs = array())
    {
	printf("<p>In datetimePicker</p>");
        $attribs = $this->_prepareAttributes($id, $value, $attribs);

        $params2 = ZendX_JQuery::encodeJson($params);

        $pr = array();
        foreach ($params as $key => $val){
            $pr[] = '"'.$key.'":'.ZendX_JQuery::encodeJson ( $val );
        }
        $pr = '{'.implode(",", $pr).'}';

        $js = sprintf('%s("#%s").datetimepicker(%s);',
                ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
                $attribs['id'],
                $pr
        );

        $this->jquery->addOnLoad($js);
        $this->jquery->addJavascriptFile('javascripts/jquery/addons/jquery-ui-timepicker-addon.js');

        return $this->view->formText($id, $value, $attribs);
    }
}

