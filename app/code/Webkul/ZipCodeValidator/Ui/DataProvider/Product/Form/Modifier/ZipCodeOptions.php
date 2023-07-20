<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\ZipCodeValidator\Helper\Data as HelperData;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\MultiSelect;
use Magento\Ui\Component\Form\Element\DataType\Text;

class ZipCodeOptions extends CustomOptions
{
    /**#@+
     * Group values
     */
    const GROUP_BOOKING_OPTIONS_PREVIOUS_NAME = 'general';
    const GROUP_BOOKING_OPTIONS_DEFAULT_SORT_ORDER = 6;
    /**#@-*/

    /**#@+
     * Field values
     */
    const FIELD_ZIP_CODE_VALIDATION = 'zip_code_validation';
    const FIELD_SELECT_REGION = 'available_region';
    /**#@-*/

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param LocatorInterface      $locator
     * @param RequestInterface      $request
     * @param HelperData            $helperData
     */
    public function __construct(
        LocatorInterface $locator,
        RequestInterface $request,
        HelperData $helper,
        \Webkul\ZipCodeValidator\Model\Config\Source\RegionOptions $availableRegions
    ) {
        $this->locator = $locator;
        $this->request = $request;
        $this->helper = $helper;
        $this->availableRegions = $availableRegions;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return array_replace_recursive($data, [
            $this->locator->getProduct()->getId() => [
                self::DATA_SOURCE_DEFAULT => [
                    static::FIELD_ZIP_CODE_VALIDATION => $this->locator->getProduct()->getZipCodeValidation() ?? 2,
                    static::FIELD_SELECT_REGION => $this->locator->getProduct()->getAvailableRegion(),
                ],
            ]
        ]);
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $productType = $this->getProductType();
        $allowedTypes = [
            "simple",
            "configurable",
            "bundle",
            "grouped"
        ];
        if ($this->helper->getEnableDisable() && in_array($productType, $allowedTypes)) {
            if ($this->helper->getApplyStatus()) {
                $this->createZipCodeValidationPanel();
            }
            $this->createZipCodeRegionsPanel();
        }
        return $this->meta;
    }

    protected function createZipCodeValidationPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                $this->getGeneralPanelName($this->meta) => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Zip Code Validation'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::GROUP_CUSTOM_OPTIONS_SCOPE,
                                'collapsible' => true,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    static::GROUP_BOOKING_OPTIONS_PREVIOUS_NAME,
                                    static::GROUP_BOOKING_OPTIONS_DEFAULT_SORT_ORDER
                                ),
                                'opened' => true,
                                'canShow' => true,
                                'value' => 2,
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_ZIP_CODE_VALIDATION => $this->getZipCodeValidationFieldConfig(),
                    ]
                ]
            ]
        );
        return $this;
    }

    /**
     * Get Price Charged Per Qty Field Config
     *
     * @return array
     */
    protected function getZipCodeValidationFieldConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Zip Code Validation'),
                        'template' => 'Webkul_ZipCodeValidator/form/field',
                        'visible' => true,
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_ZIP_CODE_VALIDATION,
                        'dataType' => Text::NAME,
                        'additionalClasses' => 'wk-select-wide',
                        'options' => [
                            ['value' => 1, 'label' => __('None')],
                            ['value' => 2, 'label' => __('Apply default Configuration')],
                            ['value' => 0, 'label' => __('Particular Product')],
                        ]
                    ]
                ]
            ]
        ];
    }

    public function createZipCodeRegionsPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                $this->getGeneralPanelName($this->meta) => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Select the Regions'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::GROUP_CUSTOM_OPTIONS_SCOPE,
                                'collapsible' => true,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    static::GROUP_BOOKING_OPTIONS_PREVIOUS_NAME,
                                    static::GROUP_BOOKING_OPTIONS_DEFAULT_SORT_ORDER
                                ),
                                'opened' => true,
                                'canShow' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_SELECT_REGION => $this->getSelectRegionsFieldConfig(),
                    ]
                ]
            ]
        );
        return $this;
    }

    /**
     * Get Price Charged Per Qty Field Config
     *
     * @return array
     */
    protected function getSelectRegionsFieldConfig()
    {
        $arr = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Select the Regions'),
                        'template' => 'Webkul_ZipCodeValidator/form/field',
                        'visible' => true,
                        'componentType' => Field::NAME,
                        'formElement' => MultiSelect::NAME,
                        'dataScope' => static::FIELD_SELECT_REGION,
                        'dataType' => Text::NAME,
                        'additionalClasses' => 'wk-select-wideel',
                        'required' => true,
                        'imports' => [
                            // 'disabled' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT
                                    // . '.zip_code_validation:'. 2 ,
                            'visible' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT
                                    . '.zip_code_validation:value',
                        ],
                        'validation' => [
                            'required-entry' => true
                        ],
                        'options' => $this->availableRegions->getAllOptions(),
                    ]
                ]
            ]
        ];

        if (!$this->helper->getApplyStatus()) {
            unset($arr['arguments']['data']['config']['imports']);
            unset($arr['arguments']['data']['config']['required']);
            unset($arr['arguments']['data']['config']['validation']);
        }

        return $arr;
    }

    /**
     * Get product type
     *
     * @return null|string
     */
    private function getProductType()
    {
        return (string)$this->request->getParam(
            'type',
            $this->locator->getProduct()->getTypeId()
        );
    }
}
