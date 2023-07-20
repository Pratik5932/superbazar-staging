/**
 * @category   Webkul
 * @package    Webkul_UvDeskConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/template',
    'Magento_Ui/js/modal/modal',
    "mage/translate"
], function ($, template, modal) {
    "use strict";
    var popup;
    $.widget('mage.ticketList', {
        _create: function () {
            var self = this;
            var getTicketListAjaxUrl = self.options.getTicketListAjaxUrl;
            $(document).ready(function () {
                $('#uvdesk_create_ticket').on('click',function () {
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: false,
                        title: 'Create Tickets',
                        buttons: [{
                            text: 'Submit',
                            class: '',
                            id:'new-ticket-data',
                            click: function () {
                                var subject = $('#subject').val()
                                var message = $('#message').val()
                                var type = $('#type').val()
                                if ((subject =="" || subject == null || subject == undefined) || (message =="" || message == null || message == undefined) || (type =="" || type == null || type == undefined)) {
                                    $('#new-ticket-data').trigger('click');
                                    return false;
                                }
                                $('#new-ticket-data').trigger('click');
                                $("#wait").css("display", "block");
                            } //handler on button click
                        },{
                            text: 'Reset',
                            class: '',
                            id:'new-ticket-data',
                            click: function () {
                                $('#subject').val("");
                                $('#message').val("");
                                $('#type').val("");
                            }
                        }
                        ]
                    };
                    var popup = modal(options, $('#popup-mpdal'));
                    $('#popup-mpdal').modal('openModal');
                });

                $('.pagination').on('click','li',function () {
                    var pageNo = $(this).attr('data');
                    if (pageNo == undefined) {
                        return false;
                    }
                    var parameter = addPageNo(pageNo);
                    var selectedPageNo = pageNo;
                    $(".pagination a").removeClass('active');
                    $(this).find('a').addClass('active');
                    $.ajax({
                        url:getTicketListAjaxUrl+parameter,
                        type:"GET",
                        data:{'isAjax':'true'},
                        dataType:"json",
                        beforeSend:function () {
                            $("#wait").css("display", "block");
                        },
                        complete: function () {
                            $("#wait").css("display", "none");
                        },
                        success: function ($data) {
                            var ticketRow="";
                            var ticketPagination="";
                            $.each($data['ticket_data'], function () {
                                var ticketTemplate = template('#ticket-template');;
                                 ticketRow += ticketTemplate({
                                                data: {
                                                    priority: this['priority'],
                                                    priorityColor: this['priority_color'],
                                                    ticket: this['id'],
                                                    name: this['name'],
                                                    status: this['status']['name'],
                                                    statusColor: this['status']['color'],
                                                    subject: this['subject'],
                                                    date: this['creation_date'],
                                                    replies: this['replies'],
                                                    agent: this['agent'],
                                                    incrementId:this['incrementId']
                                                }
                                });
                            });
                            $.each($data['pagination_data'], function () {
                                var ticketPaginationTemplate = template('#ticketPagination-template');;
                                          ticketPagination += ticketPaginationTemplate({
                                                    data: {
                                                        first: this['first'],
                                                        last: this['last'],
                                                        previous: this['previous'],
                                                        next: this['next'],
                                                        firstPageInRange: this['firstPageInRange'],
                                                        lastPageInRange: this['lastPageInRange'],
                                                        pagesInRange: this['pagesInRange'],
                                                        selectedPageNo:selectedPageNo,
                                                        pageCount:this['pageCount']
                                                    }
                                    });
                            });
                            $('#ticketBody').html(ticketRow);
                            $('.pagination').html(ticketPagination);
                        }
                    });
                });
                function addPageNo($pageNo)
                {
                    var pathname = window.location.pathname; // Returns path only
                    var url      = window.location.href;
                    var splitArray = pathname.split('pageNo/');
                    if (splitArray.length<2) {
                        if (pathname.substring(pathname.length-1, pathname.length) == '/') {
                            var a = pathname+'pageNo/'+$pageNo;
                        } else {
                            var a = pathname+'/pageNo/'+$pageNo;
                        }
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('index/');
                        return pathnameSplit[1];
                    } else {
                        var aa = splitArray[1].split('/').slice(1).join('/');
                        var a = splitArray[0]+'pageNo/'+$pageNo+'/'+aa;
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('index/');
                        return pathnameSplit[1];
                    }
                }

                var x;

                $('#addFile').on('click',function () {
                  var employee = "";
                  var employeeTemplate = template('#ticketAttachment-template');
                    employee += employeeTemplate({
                    data: {}
                  });
                  $('.wk-uvdesk-attachments').append(employee);
                });

                $('.wk-uvdesk-attachments').on('click','span',function () {
                    if ($(this).index() == 0) {
                      x =$(this).parent().find("input[type='file']");
                      x.trigger('click');
                    } else {
                      var j = $(this).parent();
                      j.parent().remove();
                      $(this).parent().remove();
                    }
                });

                $("#popup-mpdal").on('change','input[name="attachment[]"]',function (e) {
                  var preview = $(this).parent().find("span:first-child");
                  var file    = this.files[0];
                  var reader  = new FileReader();
                  reader.onloadend = function () {
                    preview.css('background-image','url(' +reader.result+ ')');
                    preview.css('background-size','cover');
                  }
                  if (file) {
                    reader.readAsDataURL(file);
                  } else {
                    preview.src = "";
                  }
                });

                function getFiltersParameter()
                {
                    if ($(window).width() <= 540) {
                        var status = $('#filter_status_res').val();
                    } else {
                        var status = $('#filter_status').val();
                    }
                    var sort = $('#sort_ticket').val();
                    var parameter = "" ;
                    if (status!="") {
                        if (parameter=="") {
                            parameter += 'status/'+status;
                        } else {
                            parameter += '/status/'+status;
                        }
                    }
                    if (sort != "") {
                        if (parameter=="") {
                            parameter += 'sort/'+sort;
                        } else {
                            parameter += '/sort/'+sort;
                        }
                    }
                    return parameter;
                }
                
                function addFiltersParameter(parameter)
                {
                    var pathname = window.location.pathname+"index"; // Returns path only
                    var url      = window.location.href;
                    var splitArray = pathname.split('index/');
                    if (splitArray.length<2) {
                        if (pathname.substring(pathname.length-1, pathname.length) == '/') {
                            var a = pathname+parameter;
                        } else {
                            var a = pathname+"/"+parameter;
                        }
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('index/');
                        return pathnameSplit[1];
                    } else {
                        var aa = splitArray[1];
                        var a = splitArray[0]+'index/'+parameter;
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('index/');
                        return pathnameSplit[1];
                    }
                }

                $("#filter_button, #filter_button_res").on('click', function () {
                    var parameter = getFiltersParameter();
                    parameter = addFiltersParameter(parameter);
                    $.ajax({
                        url:getTicketListAjaxUrl+parameter,
                        type:"GET",
                        data:{'isAjax':'true'},
                        dataType:"json",
                        beforeSend:function () {
                            $("#wait").css("display", "block");
                        },
                        complete: function () {
                            $("#wait").css("display", "none");
                        },
                        success: function ($data) {
                            var ticketRow="";
                            var ticketPagination="";
                            $.each($data['ticket_data'], function () {
                                var ticketTemplate = template('#ticket-template');
                                 ticketRow += ticketTemplate({
                                                data: {
                                                    priority: this['priority'],
                                                    priorityColor: this['priority_color'],
                                                    status: this['status']['name'],
                                                    statusColor: this['status']['color'],
                                                    ticket: this['id'],
                                                    name: this['name'],
                                                    subject: this['subject'],
                                                    date: this['creation_date'],
                                                    replies: this['replies'],
                                                    agent: this['agent'],
                                                    incrementId:this['incrementId']
                                                }
                                });
                            });
                            $.each($data['pagination_data'], function () {
                                var ticketPaginationTemplate = template('#ticketPagination-template');;
                                          ticketPagination += ticketPaginationTemplate({
                                                    data: {
                                                        first: this['first'],
                                                        last: this['last'],
                                                        previous: this['previous'],
                                                        next: this['next'],
                                                        firstPageInRange: this['firstPageInRange'],
                                                        lastPageInRange: this['lastPageInRange'],
                                                        pagesInRange: this['pagesInRange'],
                                                        pageCount:this['pageCount']
                                                    }
                                    });
                            });
                            if (ticketRow == "") {
                                ticketRow = '<tr><td colspan=8 style="text-align:center;vertical-align:middle;">No Ticket Found</td></tr>';
                            }
                            $('#ticketBody').html(ticketRow);
                            $('.pagination').html(ticketPagination);
                        }
                    });
                });
            });
        }
    });
    return $.mage.ticketList;
});