<?php
/**
 * Created by tvl.
 * Date: 6/4/2020
 * Time: 09:36
 */

namespace Tvl\MultipleWeight\Ui\DataProvider\Product\Form\Modifier;


use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;

class Weights extends AbstractModifier
{
    const ATTRIBUTE = 'weights';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var string
     */
    protected $scopeName;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->scopeName = $scopeName;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $fieldCode = self::ATTRIBUTE;

        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        $weightsAndPrice = $model->getData(self::ATTRIBUTE);

        if ($weightsAndPrice) {
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/'. self::ATTRIBUTE;
            $data = $this->arrayManager->set($path, $data, $weightsAndPrice);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this -> initFields();
        return $this->meta;
    }

    /**
     *
     * @return $this
     */
    protected function initFields()
    {
        $path = $this->arrayManager->findPath(
            self::ATTRIBUTE,
            $this->meta,
            null,
            'children'
        );

        if ($path) {
            $this->meta = $this->arrayManager->merge(
                $path,
                $this->meta,
                $this->initFieldStructure($path)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($path, 0, -3)
                . '/' . self::ATTRIBUTE,
                $this->meta,
                $this->arrayManager->get($path, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($path, 0, -2),
                $this->meta
            );
        }

        return $this;
    }


    /**
     * Get attraction highlights dynamic rows structure
     *
     * @param string $path
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function initFieldStructure($path)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Weight And Qty'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($path . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'weight' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Weight (gm)'),
                                        'dataScope' => 'weight',
                                        'require' => '1',
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-greater-than-zero' => true,
                                            'validate-digits' => false,
                                            'validate-number' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Qty'),
                                        'dataScope' => 'qty',
                                        'require' => '1',
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-zero-or-greater' => true,
                                            'validate-digits' => false,
                                            'validate-number' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}