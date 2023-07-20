<?php    
namespace Bss\OrderDeliveryDate\Block\Adminhtml\Form\Field;
    
    // use Magento\Framework\View\Element\Context;
    
    class Textarea extends \Magento\Framework\View\Element\Template
    {
        /**
         * @return string
         */
        public function _toHtml()
        {
            $inputName = $this->getInputName();
            $columnName = $this->getColumnName();
            $column = $this->getColumn();
    
            return '<textarea id="' . $this->getInputId().'" name="' . $inputName . '" ' .
                ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
                (isset($column['class']) ? $column['class'] : 'input-text') . '"'.
                (isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '></textarea>';
        }
    }