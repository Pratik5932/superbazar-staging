<?php
namespace Superbazaar\General\Plugin;
use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;
class DefaultRendererPlugin
{
    public function aroundGetColumnHtml(DefaultRenderer $defaultRenderer, \Closure $proceed,\Magento\Framework\DataObject $item, $column, $field=null)
    {
        if ($column == 'expected-delivery-time'){
            $html = $item->getAaisle();
            $result = $html;
        }else{
            if ($field){
                $result = $proceed($item,$column,$field);
            }else{
                $result = $proceed($item,$column);

            }
        }

        return $result;
    }
}