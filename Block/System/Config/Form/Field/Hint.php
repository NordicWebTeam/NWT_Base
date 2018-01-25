<?php
/**
 *
 * @category    NWT
 * @package     NWT_Base
 * @copyright   Copyright (c) 2017 Nordic Web Team ( http://nordicwebteam.se/ )
 * @license     NWT Commercial License (NWTCL 1.0)
 *
 */

namespace NWT\Base\Block\System\Config\Form\Field;

class Hint extends \Magento\Backend\Block\Template implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    protected $_template = 'aboutus.phtml';

    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $elementOriginalData = $element->getOriginalData();
        return $this->toHtml();
    }
    

}
