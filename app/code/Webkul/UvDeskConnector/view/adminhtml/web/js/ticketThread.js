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
    "mage/loader"
], function ($, template) {
    "use strict";
    $.widget('mage.ticketThread', {
        _create: function () {
            var self = this;
            var pageNoParameter = self.options.pageNoParameter;
            var ticketThreadIndexAjaxUrl = self.option.ticketThreadIndexAjaxUrl;
            $(document).ready(function () {
               $(":submit").click(function (e) {
                    e.preventDefault();
                    if ($("#uv-admin-reply-ticket").validation('isValid')!==false) {
                        $('body').loader('show');
                        $('#uv-admin-reply-save-btn span span').text("Saving"+'....');
                        $('#uv-admin-reply-save-btn').css('opacity','0.7');
                        $('#uv-admin-reply-save-btn').css('cursor','default');
                        $('#uv-admin-reply-save-btn').attr('disabled','disabled');
                        $('#uv-admin-reply-ticket').submit();
                    }
                });
              var expandButton  = $('#button-load');
                var x;
                $('#addFile').on('click',function () {
                  var employee = "";
                  var employeeTemplate = template('#ticketPagination-template');
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

                $(".reply").on('change','input',function (e) {
                  var preview = $(this).parent().find("span:first-child");
                  var file    = this.files[0];
                  var label = "<label>"+file.name+"</label>";
                  var reader  = new FileReader();
                  reader.onloadend = function () {
                    preview.css('background-image','url(' +reader.result+ ')');
                    preview.css('background-size','cover');
                    preview.parent().find('label').remove();
                    preview.parent().append(label);
                  }
                  if (file) {
                    reader.readAsDataURL(file);
                  } else {
                    preview.src = "";
                  }
                });
                expandButton.on('click',function () {
                    var nextPageNo = expandButton.data("nextPage");
                    if (nextPageNo) {
                    var parameter =  pageNoParameter+nextPageNo;
                      $.ajax({
                          url:ticketThreadIndexAjaxUrl+parameter,
                          type:"GET",
                          dataType:"json",
                          beforeSend:function () {
                              $("#wait").css("display", "block");
                          },
                          complete: function () {
                              $("#wait").css("display", "none");
                          },
                          success: function ($data) {
                              var employee="";
                              var employeTab=""
                              var ticketPagination=""
                              $.each($data['ticket_thread']['thread'], function () {
                                  var employeeTemplate = template('#ticket-thread-template');
                                  employee += employeeTemplate({
                                                  data: {
                                                    id:this['id'],
                                                    name:this['name'],
                                                    userSmallThumbNail:this['userSmallThumbNail'],
                                                    customerDetail:this['customerDetail'],
                                                    userType:this['userType'],
                                                    reply:this['reply'],
                                                    formatedCreatedAt:this['formatedCreatedAt']
                                                  }
                                  });
                                  employee +="<hr>";
                              });
                              $('.ticket-thread').append(employee);
                                  if ($data['ticket_thread']['pagination']['currentPage'] == $data['ticket_thread']['pagination']['lastPage']) {
                                    expandButton.data('nextPage',0);
                                    expandButton.text('All Expanded');
                                    expandButton.unbind("click");
                                  } else {
                                    expandButton.data("nextPage",$data['ticket_thread']['pagination']['next']);
                                    var noOfThreadsRemaining = $data['ticket_thread']['pagination']['totalCount'] - ($data['ticket_thread']['pagination']['currentPage'] * $data['ticket_thread']['pagination']['numItemsPerPage']);
                                    expandButton.text('Expand'+" "+noOfThreadsRemaining+" "+'More...');
                                  }
                          }
                      });
                    } else {
                      expandButton.data('nextPage',0);
                      expandButton.text('All Expanded');
                      expandButton.unbind("click");
                  }
                });
            });
        }
    });
    return $.mage.ticketThread;
});