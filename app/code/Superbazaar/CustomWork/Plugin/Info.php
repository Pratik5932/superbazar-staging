<?php
namespace Superbazaar\CustomWork\Plugin;

class Info extends \Magento\Payment\Block\Info
{
	/**
     * Get some specific information in format of array($label => $value)
     *
     * @return array
     */
    public function getSpecificInformation()
    {
        $infoAray = $this->_prepareSpecificInformation()->getData();
		$info = $this->getInfo();   
		if (!empty($this->getInfo()->getAdditionalInformation("instructions"))) {
			$infoAray['Instructions'] = $this->getInfo()->getAdditionalInformation("instructions");
		}
        return $infoAray;
    }
}
