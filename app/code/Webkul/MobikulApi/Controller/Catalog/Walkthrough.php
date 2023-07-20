<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Catalog;

/**
 * Class GetCategoryList
 * To provide list of categories
 */
class Walkthrough extends AbstractCatalog
{
    /**
     * Execite Funciton for class Walkthrough
     *
     * @return json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $walkthroughCollection = $this->walkthroughFactory->create()->getCollection()
                ->addFieldToFilter('status', 1)
                ->setOrder('sort_order', 'ASC')
                ->addFieldToSelect(['image', 'title', 'description', 'color_code']);
            $walkthroughData = [];
            foreach ($walkthroughCollection as $walkthrough) {
                $data = [];
                $width = (int)$this->width/2;
                $height = (int)$this->width/2;
                $image = explode('/', $walkthrough->getImage());
                $image = end($image);
                $basePath = $this->helper->getBaseMediaDirPath();
                $imagePath = $basePath.$walkthrough->getImage();
                $newPath = $basePath.'mobikul'.DS.'walkthroughs'.DS.$width.'x'.$height.DS.$image;
                $this->helperCatalog->resizeNCache($imagePath, $newPath, $width, $height);
                $newImageUrl = $this->helper->getUrl("media")."mobikul".DS."walkthroughs".DS.$width."x".
                    $height.DS.$image;
                $data["title"] = $walkthrough->getTitle();
                $data["content"] = $walkthrough->getDescription();
                $data['image'] = $newImageUrl;
                $data['imageDominantColor'] = $this->helper->getDominantColor($imagePath);
                $data["colorCode"] = $walkthrough->getColorCode();
                $walkthroughData[] = $data;
            }
            $this->returnArray["walkthroughVersion"] = $this->helper->getConfigData(
                "mobikul/walkthrough/walkthrough_version"
            );
            $this->returnArray["walkthroughData"] = $walkthroughData;
            $this->returnArray["success"] = true;
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->width = $this->wholeData["width"] ?? 1000;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
