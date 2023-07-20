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
define([
    "jquery",
    "mage/calendar"
], function ($) {
    "use strict";
    $.widget('bss.calendar', {

        _create: function () {
            var self = this,
                image = '',
                icon = false,
                processTime = 0,
                days_off_arr_temporary = [],
                minTime = 0;
            window.config = this;
            window.dateRequired = this.options.dateRequired;
            window.timesRequired = this.options.timesRequired;
            window.commentRequired = this.options.commentRequired;

            if (this.options.asProcessingDays) {
                /* as processing days */
                if (this.options.processingTime) {
                    processTime = Number(this.options.processingTime);
                    minTime = Number(this.options.processingTime) - 1;
                }
                var day_off = this.options.dayOff,
                    day_off_arr = [],
                    currentTime = this.options.currentTime * 1000,
                    block_out_holidays = this.options.blockOutHoliday;
                    
                if (day_off) {
                    day_off_arr = day_off.split(',');
                }
                var count = 0;
                if(minTime >= 0){
                    if (day_off) {
                        for(var i = 0; i <= minTime; i++) {
                            var processDate = new Date(currentTime + i*86400000);
                                y = processDate.getFullYear(),
                                    m = parseFloat(processDate.getMonth()) + 1,
                                    d = processDate.getDate();
                                d = d.toString().length > 1 ? d : '0' + d;
                            if (m < 10) {
                                m = '0'+m; 
                            }
                            var data = y+'-'+m+'-'+d;
                            if(day_off_arr.indexOf(processDate.getDay().toString())>=0) {
                                count++;
                                days_off_arr_temporary.push(data);
                            }
                        }
                    }
                    minTime += count;
                    
                    if (block_out_holidays) {
                        for(var i = 0; i <= minTime; i++) {
                            var processDate = new Date(currentTime + i*86400000),
                                y = processDate.getFullYear(),
                                m = parseFloat(processDate.getMonth()) + 1,
                                d = processDate.getDate();
                            d = d.toString().length > 1 ? d : '0' + d;
                            if (m < 10) {
                                m = '0'+m; 
                            }
                            var data = y+'-'+m+'-'+d;
                            if(block_out_holidays.indexOf(data) >= 0) {
                                minTime++;
                            }
                            if ($.inArray(data, days_off_arr_temporary) == -1 && day_off_arr.indexOf(processDate.getDay().toString()) >=0) {
                                minTime++;
                            }
                        }
                    }
                }

                minTime = minTime + 1;
                /* end */
            }

            $(this.element).datepicker({
                dateFormat:this.options.dateFormat,
                buttonText: "",
                minDate: new Date(),
                showsTime: false,
                controlType: 'select',
                showOn: "button",
                minDate: minTime,
                serverTimezoneSeconds: parseInt(this.options.currentTime),
                serverTimezoneOffset: parseInt(this.options.timeZone),
                buttonImageOnly: false,
                beforeShowDay: self.configDate
            });
        },

        configDate: function (date) {
            var minTime = $.datepicker._getTimezoneDate().getTime() + (window.config.options.processingTime - 1) * 60 * 60 * 24 * 1000;
            if (date.getTime() < minTime) {
                return false;
            }

            var string = $.datepicker.formatDate('yy-mm-dd', date);
            var day = date.getDay(),
                block_out_holidays = window.config.options.blockOutHoliday;
            var day_off_arr = [];
            var day_off = window.config.options.dayOff;
            if (day_off) {
                day_off_arr = day_off.split(',');
            }
            for (var i = 0; i < day_off_arr.length; i++) {
                day_off_arr[i] = parseInt(day_off_arr[i]);
            }
            if (day_off_arr.indexOf(day) == -1 && block_out_holidays.indexOf(string) == -1) {
                return[true, ''];
            }

            return [false, ''];
        }
    });
    return $.bss.calendar;
});
