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
], function ($, template, alert) {
    "use strict";
    $.widget('mage.ticketThread', {
        _create: function () {
            var self = this;
            var emailParameter  = self.options.emailParameter
            var ticketThreadAddcollaboraterAjaxUrl = self.options.ticketThreadAddcollaboraterAjaxUrl ;
            var collabaoratorParameter = self.options.collabaoratorParameter ;
            var ticketThreadRemovecollaboratorAjaxUrl = self.options.ticketThreadRemovecollaboratorAjaxUrl ;
            var pageNoParameter = self.options.pageNoParameter ;
            var     ticketThreadIndexAjaxUrl = self.options.ticketThreadIndexAjaxUrl ;
            $(document).ready(function () {
                var $addCollaborator = $('#addCollaborator');

                $addCollaborator.keypress(function (event) {
                    var inputRef = this;
                    if (event.keyCode == 13) {
                        if (validateEmail($(this).val())) {
                        var parameter =  emailParameter+$(this).val();
                            $.ajax({
                                url:ticketThreadAddcollaboraterAjaxUrl+parameter,
                                type:"GET",
                                dataType:"json",
                                beforeSend:function () {
                                    $("#wait").css("display", "block");
                                    $addCollaborator.val('');
                                },
                                complete: function () {
                                    $("#wait").css("display", "none");
                                },
                                success: function ($data) {
                                if (typeof $data['collaborator'] !== "undefined") {
                                    var res = $data['collaborator']['email'].split("@");
                                    var colab = "";
                                    var colabTemplate = template('#collaborator-template');
                                    colab += colabTemplate({
                                    data: {
                                        id:$data['collaborator']['id'],
                                        name:res[0],
                                        title:$data['collaborator']['email'],
                                    }
                                    });
                                    $('.noCollaboratordiv').remove();
                                    $('#collaborator-panel').append(colab);
                                    var successHtml = '<div class="text-success" style="padding: 5px;">'+$data['message']+'</div>';
                                    $(inputRef).parent().append(successHtml);
                                    $('.text-success').fadeOut(3000, function () {
 $(this).remove();});
                                } else if (typeof $data['error'] !== "undefined") {
                                    var message = "We cannot process your request.Please contact administration";
                                    var errorHtml = '<div class="text-danger" style="padding:5px;">'+message+'</div>';
                                    $(inputRef).parent().find('.text-danger').remove('.text-danger');
                                    $(inputRef).parent().append(errorHtml);
                                    $('.text-danger').fadeOut(3000, function () {
 $(this).remove();});
                                }
                                }
                            });
                        } else {
                        $(this).parent().find('.text-danger').remove('.text-danger');
                        $(this).parent().append('<div class="text-danger" style="margin-left: 5px;">Please enter a valid email</div>');
                        $('.text-danger').fadeOut(3000, function () {
 $(this).remove();});
                        }
                    }
                });

                $('div#collaborator-panel').on('click','.removeCollaborator',function () {
                    var div = $(this).parent();
                    var collaboratorId = div.attr('col-id');
                    if (collaboratorId=="") {
                        div.parent().parent().parent().parent().append('<div class="text-danger" style="margin-left: 5px;">Please enter a valid email</div>');
                        $('.text-danger').fadeOut(3000, function () {
 $(this).remove();});
                        return false;
                    }
                    var parameter =  collabaoratorParameter+collaboratorId;
                    $.ajax({
                        url:ticketThreadRemovecollaboratorAjaxUrl+parameter,
                        type:"GET",
                        dataType:"json",
                        beforeSend:function () {
                            $("#wait").css("display", "block");
                        },
                        complete: function () {
                            $("#wait").css("display", "none");
                        },
                        success: function ($data) {
                        if (typeof $data['info']!== "undefined" && $data['info'] == 200) {
                            div.parent().fadeOut(1000, function () {
                                $(this).remove();
                                if ($("#collaborator-panel > div").length < 1) {
                                var noCollabHtml = '<div class="coll-div noCollaboratordiv" style="margin: 10px 0;">There is no collaborator available for this ticket.</div>';
                                $('#collaborator-panel').append(noCollabHtml);
                                }
                            });
                            div.parent().parent().parent().parent().append('<div class="text-success" style="margin-left: 5px;">'+$data['response']['message']+'</div>');
                            $('.text-success').fadeOut(3000);
                        } else {
                            if (typeof $data['response']['error'] !== "undefined") {
                            var message = "We cannot process your request.Please contact administration";
                            div.parent().parent().parent().parent().append('<div class="text-danger" style="margin-left: 5px;">'+message+'</div>');
                            $('.text-danger').fadeOut(3000, function () {
                                $(this).remove();}
                            );
                            }
                        }
                        }
                    });
                });

                var expandButton  = $('#button-load');
                
                $(":submit").click(function (e) {
                    e.preventDefault();
                    if ($("#edit-ticket").valid()!==false) {
                        if ($('#description').val() == "") {
                        $('#custom_wysiwyg-error').remove();
                        $('#description').parent().prepend('<div class="mage-error" generated="true" id="custom_wysiwyg-error">This is a required field.</div>');
                        return false;
                        } else {
                        $('#save-btn span span').text("Saving"+'....');
                        $('#save-btn').css('opacity','0.7');
                        $('#save-btn').css('cursor','default');
                        $('#save-btn').attr('disabled','disabled');
                        $('#edit-ticket').submit();
                        }
                    }
                });

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
                            if ($data['error']==true) {
                                alert({
                                title: 'Some title',
                                content: 'Some content',
                                actions: {
                                    always: function (){}
                                }
                                });
                            } else {
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
                            }
                        });
                    } else {
                        expandButton.data('nextPage',0);
                        expandButton.text('All Expanded');
                        expandButton.unbind("click");
                    }
                });
            });
            function validateEmail(email)
            {
                var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }
        }
    });
    return $.mage.ticketThread;
});