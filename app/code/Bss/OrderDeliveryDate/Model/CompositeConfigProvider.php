<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* @category   BSS
* @package    Bss_OrderDeliveryDate
* @author     Extension Team
* @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/

namespace Bss\OrderDeliveryDate\Model;

class CompositeConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    const TIME_OF_DAY_IN_SECONDS = 86400;

    /**
    * @var \Bss\OrderDeliveryDate\Helper\Data
    */
    protected $bssHelper;

    /**
    * @var \Magento\Config\Model\Config\Source\Locale\Weekdays
    */
    protected $weekdays;

    /**
    * CompositeConfigProvider constructor.
    * @param \Bss\OrderDeliveryDate\Helper\Data $bssHelper
    * @param \Magento\Config\Model\Config\Source\Locale\Weekdays $weekdays
    */
    public function __construct(
        \Bss\OrderDeliveryDate\Helper\Data $bssHelper,
        \Magento\Config\Model\Config\Source\Locale\Weekdays $weekdays
    ) {
        $this->bssHelper = $bssHelper;
        $this->weekdays = $weekdays;
    }

    /**
    * Add ODD variable to Checkout Page
    *
    * @return array
    * @throws \Magento\Framework\Exception\NoSuchEntityException
    */
    public function getConfig()
    {
        $output = [];
        if ($this->bssHelper->isEnabled()) {
            $output['bss_delivery_enable'] = (boolean) $this->bssHelper->isEnabled();
            if ($this->bssHelper->getTimeSlot()) {
                $output['bss_delivery_timeslot'] = $this->bssHelper->getTimeSlot();
                $output['bss_delivery_has_timeslot'] = true;
            }
            $day_off = $this->bssHelper->getDayOff();
            
            #echo $day_off;exit;
            $block_out_holidays = $this->bssHelper
            ->returnClassSerialize()
            ->unserialize($this->bssHelper->getBlockHoliday());
            $current_time = (int) $this->bssHelper->getStoreTimestamp();
            $cut_off_time_convert = $this->bssHelper->getCutOffTime();




            $process_time = $this->bssHelper->getProcessingTime();
            // Check if over cut of time in day then + 1 processing day
            
           # echo $current_time > $cut_off_time_convert;exit;
            if ($cut_off_time_convert &&
            $current_time > $cut_off_time_convert &&
            !$this->isProcessingDayDisabled()) {

                $process_time++;
            }
           #echo  $this->bssHelper->getStoreTimezone();exit;
            $block_out_holidays = !empty($block_out_holidays) ? json_encode($block_out_holidays) : '';
            $output['bss_shipping_comment'] = (boolean) $this->bssHelper->isShowShippingComment();
            $output['bss_delivery_process_time'] = $process_time;
            $output['bss_delivery_block_out_holidays'] = $block_out_holidays;
            $output['bss_delivery_day_off'] = $day_off;
            $output['bss_delivery_date_fomat'] = $this->bssHelper->getDateFormat();
            $output['bss_delivery_current_time'] = $current_time;
            $output['bss_delivery_time_zone'] = $this->bssHelper->getTimezoneOffsetSeconds();
            $output['as_processing_days'] = $this->bssHelper->isAsProcessingDays();
            $output['store_time_zone'] = $this->bssHelper->getStoreTimezone();
            if ($this->bssHelper->getIcon()) {
                $output['bss_delivery_icon'] = $this->bssHelper->getIcon();
            }
            $output['date_field_required'] = $this->bssHelper->isFieldRequired('required_date');
            $output['times_field_required'] = $this->bssHelper->isFieldRequired('required_timeslot');
            $output['comment_field_required'] = $this->bssHelper->isFieldRequired('required_comment');
            $output['on_which_page'] = $this->bssHelper->getDisplayAt();
            $output['action_payment_save'] = $this->bssHelper->getPaymentSaveAction();
            $output['today_date'] = $this->bssHelper->getDateToday();
            $output['min_date'] = $this->getMindate($day_off, $block_out_holidays, $process_time, $current_time);
            $output['bss_format_date'] = $this->bssHelper->formatDate();
            if(!$cut_off_time_convert)
            $output['hide_calendar'] = true;
            
            #print_R($output);exit;
        }
        return $output;
    }

    /**
    * Whatever we should add to day to processing day
    *
    * @return bool
    */
    public function isProcessingDayDisabled()
    {
        if ($this->bssHelper->isAsProcessingDays()) {
            return false;
        }
       # echo $this->bssHelper->isAsProcessingDays();exit;
        $weekDays = $this->weekdays->toOptionArray();
        $dayOff = explode(',', $this->bssHelper->getDayOff());
        $disableDayName = [];
        foreach ($weekDays as $weekDay) {
            if (isset($weekDay['value']) &&
            isset($weekDay['label']) &&
            in_array($weekDay['value'], $dayOff)) {
                $disableDayName[] = strtolower($weekDay['label']);
            }
        }
        if (in_array(strtolower($this->bssHelper->getDayOfWeekName()), $disableDayName)) {
            return true;
        }
        return false;
    }

    /**
    * Get min date
    *
    * @param string $day_off
    * @param array|string $block_out_holidays
    * @param int|string $process_time
    * @return array
    */
    protected function getMindate($day_off, $block_out_holidays, $process_time, $current_time)
    {
        // If exclude processing day = no, then return config processing time
       # echo $process_time;exit;
        if ($this->bssHelper->isAsProcessingDays()) {
            return $process_time;
        }
        // If processing time <= 0, then return config processing time
        if ($process_time <= 0) {
            return $process_time;
        }
        // If day off is empty, then return config processing time
        $dayOffArr = $this->getDayAsArray($day_off);
        $holidays = $this->getDayAsArray($block_out_holidays);
        $timeOfDayInSeconds = self::TIME_OF_DAY_IN_SECONDS;
        $tempProcessTime = $process_time;
        $tempDisabledDays = [];
        if (!empty($dayOffArr)) {
            for ($i = 0; $i < $tempProcessTime; $i++) {
                $nextOfDayInTime = $current_time + $i * $timeOfDayInSeconds;
                $momentDate = date('Y-m-d', $nextOfDayInTime);
                $momentDay = date('w', $nextOfDayInTime);
                if (in_array($momentDay, $dayOffArr)) {
                    $tempProcessTime++;
                    $tempDisabledDays[] = $momentDate;
                }
            }
        }
        if (!empty($holidays)) {
            for ($j = 0; $j < $tempProcessTime; $j++) {
                $nextOfDayInTime = $current_time + $j * $timeOfDayInSeconds;
                $momentDate = date('Y-m-d', $nextOfDayInTime);
                $momentDay = date('w', $nextOfDayInTime);
                if (in_array($momentDay, $holidays) && !in_array($momentDay, $tempDisabledDays)) {
                    $tempProcessTime ++;
                }
            }
        }

        $newTime = $tempProcessTime * $timeOfDayInSeconds + $current_time;

        return [
            'dayOfWeek' => date('w', $newTime),
            'extendedDay' => ($tempProcessTime < 7) ? 0 : ($tempProcessTime - ($tempProcessTime % 7)) / 7
        ];
    }

    /**
    * Get day as array
    *
    * @param $days
    * @return array
    */
    private function getDayAsArray($days)
    {
        return is_string($days) ? explode(',', $days) : (is_array($days) ? $days : []);
    }
}
