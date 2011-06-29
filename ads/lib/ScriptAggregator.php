<?php
require_once 'RecursiveDOMIterator.php';

class ScriptAggregator implements Iterator
{
    protected $_dom;
    protected $_scripts = array();
    
    
    
    public function __construct($rawDom) {
        
        $this->_dom = new DOMDocument('1.0', 'utf-8');
        $this->_dom->loadXML('<?xml version="1.0" encoding="utf-8" ?>
<root>' . $rawDom . '</root>');
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDOMIterator($this->_dom), 
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $node) {
            // drill down: node type ..
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE : // DOMElement
                    /* @var $node DOMElement */
                    // .. node name
                    switch ($node->nodeName) {
                        case 'script' :
                            $this->_scripts[] = new Script($node);
                            break;
                        default : // other html
                    }
                    break;
                case XML_ATTRIBUTE_NODE : // DOMAttr
                    break;
                case XML_TEXT_NODE : // DOMText
                    break;
                case XML_CDATA_SECTION_NODE : // DOMCharacterData
                    break;
                case XML_ENTITY_REF_NODE : // DOMEntityReference
                    break;
                case XML_ENTITY_NODE : // DOMEntity
                    break;
                case XML_PI_NODE : // DOMProcessingInstruction
                    break;
                case XML_COMMENT_NODE : // DOMComment
                    break;
                default : 
                // one of the others listed here http://www.php.net/manual/en/dom.constants.php
                    
            }
        }
    }
    
    public function replaceWithOutput (array $output) {
        foreach ($this->_scripts as $index => $script) {
            /* @var $script Script */
            $script->replaceWithOutput($output[$index]);
        }
    }
    
    // Iterator interface
    
    /**
     * @return DOMElement
     */
    public function current ()
    {
        return current($this->_scripts);
    }

    public function next ()
    {
        return next($this->_scripts);
    }

    public function key ()
    {
        return key($this->_scripts);
    }

    public function valid ()
    {
        return key($this->_scripts) !== null;
    }

    public function rewind ()
    {
        return reset($this->_scripts);
    }
    
    public function __toString() {
        return $this->_dom->saveXML();
    }
}

class Script {
    /**
     * @var DOMElement
     */
    protected $_node;
    protected $_rawScript;
    const TYPE_SCRIPT = 1;
    const TYPE_INCLUDE = 2;
    public function __construct (DOMElement $node) {
        $this->_node = $node;
    }
    public function getType () {
        if ($this->_node->getAttributeNode('src')) {
            return self::TYPE_INCLUDE;
        } else {
            return self::TYPE_SCRIPT;
        }
    }
    public function getRawScript () {
        if (!$this->_rawScript) {
            if ($this->getType() === self::TYPE_INCLUDE) {
                $this->_rawScript = $this->_node->getAttributeNode('src')->value;
            } else {
                $this->_rawScript = trim((string) $this->_node->nodeValue); 
            }
        }
        return $this->_rawScript; 
    }
    
    public function setRawScript ($rawScript) {
        $this->_rawScript = $rawScript;
    }
    
    public function replaceWithOutput ($output) {
        $this->_node->parentNode->replaceChild(new DOMText($output), $this->_node);
    }
    
    public function __toString() {
        return $this->getRawScript();
    }
}






















