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
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'jquery/jquery-storageapi'
], function ($, template, alert, confirmation) {
    "use strict";
    $.widget('mage.ticketsGrid', {
        _create: function () {
            var self = this;
            var agent = self.options.agent;
            var customer = self.options.customer;
            var group = self.options.group;
            var mailbox = self.options.mailbox;
            var priority = self.options.priority;
            var team = self.options.team;
            var type = self.options.type;
            var tag = self.options.tag;
            var baseUvdeskAdminUrl = self.options.baseUvdeskAdminUrl;
            var ticketsGridDeleteAjaxUrl = self.options.ticketsGridDeleteAjaxUrl;
            var ticketsGridGetTicketsAjaxUrl = self.options.ticketsGridGetTicketsAjaxUrl;
            var ticketsGridAgentAssignAjaxUrl = self.options.ticketsGridAgentAssignAjaxUrl;
            $(document).ready(function () {

                /*to remove all the selected filters for tickets*/
                $(window).on('load', function () {
                    var pathname = window.location.pathname;
                    var url      = window.location.href;
                    var haveLabels = url.split('labels/');
                    var baseUrl = baseUvdeskAdminUrl;
                    if (haveLabels.length == 1) {
                        window.history.replaceState({}, "", baseUrl);
                    }
                    if (haveLabels.length == 2) {
                        $labels = haveLabels[1].split('/');
                        window.history.replaceState({}, "", haveLabels[0]+'labels/'+$labels[0]);
                    }
                });

                var addListAfterElemetId;
                window.currentInputSearch = "";
                var selectedPageNo = 0;
                var filterType;
                var agent_array = [];
                var customer_array = [];
                var group_array = [];
                var priority_array = [];
                var mailbox_array = [];
                var team_array = [];
                var type_array = [];
                var tag_array = [];
                var activeTabId;
                $.localStorage.set('uvdesk_agent',agent);
                $.localStorage.set('uvdesk_customer',customer);
                $.localStorage.set('uvdesk_group',group);
                $.localStorage.set('uvdesk_priority',priority);
                $.localStorage.set('uvdesk_mailbox',mailbox);
                $.localStorage.set('uvdesk_team',team);
                $.localStorage.set('uvdesk_type',type);
                $.localStorage.set('uvdesk_tag',tag);

                $('#check_all').click(function () {
                    var status = this.checked;
                    $("tr td input[type='checkbox']").prop('checked', status);
                });

                $("#wk_delete_tickets").on('click',function () {
                    var ticket_ids = $('tr td input[name="selected"]:checked').map(function () {
                        return this.value;
                    }).get();
                    if (ticket_ids.length == 0) {
                        alert({
                            title: 'Attention',
                            content: 'You have not selected any items!',
                            actions: {
                                always: function () {
                                    return false;
                                }
                            }
                        });
                    } else if (ticket_ids.length != 0) {
                                confirmation({
                                    title: 'Confirm Action',
                                    content: 'Are you sure you want to perform this action?',
                                    actions: {
                                        confirm: function () {
                                            $.ajax({
                                                url     :   ticketsGridDeleteAjaxUrl,
                                                type    :   "GET",
                                                dataType:   "json",
                                                async   :   true,
                                                beforeSend:function () {
                                                    $("#wait").css("display", "block");
                                                },
                                                data    :   {
                                                                id: ticket_ids
                                                            },
                                                success :   function (data) {
                                                    location.reload("true");
                                                }
                                            });
                                        },
                                        cancel: function () {
                                            return false;
                                        },
                                        always: function () {

                                        }
                                    }
                                });
                    }
                });

                $('.pagination').on('click','li',function () {
                    $('.pagination li').removeClass('active');
                    var pageNo = $(this).attr('data');
                    if (pageNo == undefined) {
                        return false;
                    }
                    selectedPageNo = pageNo;
                    $(this).addClass('active');
                    var parameter = addPageNo(pageNo);
                    ajaxRequest(parameter,1);
                });

                $('body').on('click',function (e) {
                    if (e.toElement.nodeName == 'I' || e.toElement.nodeName =='SPAN' || e.toElement.nodeName == 'INPUT') {
                        //return false;
                    } else {
                        $('div.bootstrap-select').attr('class','btn-group bootstrap-select');
                        if (window.currentInputSearch!="") {
                            $ul = window.currentInputSearch.parent().siblings('ul');
                            $ticketId = $(this).data('ticketid');
                            var agentList = $.localStorage.get('uvdesk_agent');
                            var agentListSearchHtml = "";
                            $.each(agentList.agent, function (index, element) {
                                var agentListTemplate = template('#agentList-template');
                                agentListSearchHtml += agentListTemplate({
                                                data: {
                                                    agentId: element.id,
                                                    agentName: element.name,
                                                    ticketId: $ticketId
                                                }
                                });
                            });
                            $ul.html(agentListSearchHtml);
                            $('div.bootstrap-select').attr('class','btn-group bootstrap-select');
                        }
                    }
                });

                $('body').on('click','span.agentButton',function () {
                        $('div.bootstrap-select').attr('class','btn-group bootstrap-select');
                        $(this).siblings('div').toggleClass('open');
                        window.selectedAgentListObject = $(this).siblings('div');
                    });

                $('body').on('click','li.agentLists',function () {
                        var currentAgentSelector = $(this).parent().parent().parent().siblings('span:nth-child(2)');
                        var ticketId = $(this).children('a').data('ticketid');
                        var agentId  = $(this).children('a').data('agentid');
                        var SelectedAgentName  = $(this).children('a').children('span:first-child').text();
                        window.selectedAgentListObject.attr('class','btn-group bootstrap-select');
                        agentAssignAjaxRequest(ticketId,agentId,currentAgentSelector,SelectedAgentName);
                    });

                $('body').on('keyup','.agentSearchInput',function () {
                        $ul = $(this).parent().siblings('ul');
                        $ticketId = $(this).data('ticketid');
                        var agentDataa = [];
                        var agentList = $.localStorage.get('uvdesk_agent');
                        if ($(this).val().length>1) {
                            var str = $(this).val();
                            $.each(agentList,function (outerindex,value) {
                                $.each(value,function (innerindex,prop) {
                                    var patt = new RegExp(str,'i');
                                    var res = patt.test(prop.name);
                                if (res) {
                                    var d = {};
                                    d.id = prop.id;
                                    d.name = prop.name;
                                    d.ticketId = $ticketId;
                                    agentDataa.push(d);
                                }
                                });
                            });
                        }
                        if ($(this).val().length == 0) {
                            agentDataa = agentList.agent;
                        }
                        var agentListSearchHtml = "";
                        $.each(agentDataa, function (index, element) {
                            var agentListTemplate = template('#agentList-template');
                            agentListSearchHtml += agentListTemplate({
                                            data: {
                                                agentId: element.id,
                                                agentName: element.name,
                                                ticketId: $ticketId
                                            }
                            });
                        });
                    $ul.html(agentListSearchHtml);

                });

                $('body').on('blur','.agentSearchInput',function () {
                    window.currentInputSearch = $(this);
                });

                $("#filters > div > div > input").on({
                    'keyup':function () {
                        filterType = $(this).parent().attr('filter-type');
                        addListAfterElemetId = $(this).attr('id');
                        var flag = 0;
                        if ($(this).val().length>1) {
                        var str = $(this).val();
                            $('.uvdesk-dropdown').remove();
                            var html = '<div class="uvdesk-dropdown"><ul>';
                            var agentList = $.localStorage.get('uvdesk_'+filterType);
                            $.each(agentList,function (index,value) {
                                 $.each(value,function (index,prop) {
                                        var patt = new RegExp(str,'i');
                                        var res = patt.test(prop.name);
                                    if (res) {
                                        flag = 1;
                                     html+='<li filter-id="'+prop.id+'">'+prop.name+'</li>';
                                    }
                                 });
                            });
                            html += '</ul></div>';
                            if (flag == 1) {
                                $(html).insertAfter("#"+addListAfterElemetId);
                            }
                        }
                    },
                    'blur':function () {
                        $('.uvdesk-dropdown').fadeOut('slow');
                    }
                });

                $('.pos-relative').on('click','li',function () {
                    var refer = $(this);
                    var name = $(this).text();
                    var id =$(this).attr('filter-id');
                    var ar = typeOfArray(filterType);
                    if (ar.indexOf(id)<0) {
                    var html = '<span class="label label-info">'+name+'<i class="fa fa-times remove" filter-id="'+id+'"></i></span>';
                        $(html).insertBefore(refer.parent().parent().parent());
                        $('#'+addListAfterElemetId).val('');
                        ar.push(id);
                        var parameter = addFiltersId(filterType,id);
                        ajaxRequest(parameter,1);
                    } else {
                        return false;
                    }
                });

                $('div#filters').on('click','i',function () {
                    filterType = $(this).parent().parent().children('div .pos-relative').attr('filter-type');
                    var id = $(this).attr('filter-id');
                    var ar = typeOfArray(filterType);
                    var i = ar.indexOf(id);
                    if (i != -1) {
                        ar.splice(i, 1);
                    }
                    $(this).parent().remove();
                    var parameter = removeFiltersId(filterType,id);
                    ajaxRequest(parameter,1);
                });

                function typeOfArray($type)
                {
                    switch ($type) {
                        case 'agent':
                        return agent_array;
                        break;
                        case 'customer':
                        return customer_array;
                        break;
                        case 'group':
                        return group_array;
                        break;
                        case 'priority':
                        return priority_array;
                        break;
                        case 'mailbox':
                        return mailbox_array;
                        break;
                        case 'team':
                        return team_array;
                        break;
                        case 'type':
                        return type_array;
                        break;
                        case 'tag':
                        return tag_array;
                        break;
                    }
                }
                
                $('.btn-group.width-100').on('click','a',function () {
                    $(".btn-group.width-100 a").removeClass("active");
                    $(this).addClass('active');
                    var id = $(this).attr('tab-id');
                    activeTabId = id;
                    var parameter = addTabId(id);
                    ajaxRequest(parameter,1);

                });

                function addTabId($id)
                {
                    var pathname = window.location.pathname; // Returns path only
                    var url      = window.location.href;
                    var splitArray = pathname.split('tab/');
                    if (splitArray.length<2) {
                        if (pathname.substring(pathname.length-1, pathname.length) == '/') {
                            var a = pathname+'tab/'+$id;
                        } else {
                            var a = pathname+'/tab/'+$id;
                        }
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    } else {
                        var aa = splitArray[1].split('/').slice(1).join('/');
                        var a = splitArray[0]+'tab/'+$id+'/'+aa;
                        var havePageNo = a.split('pageNo/');
                        if (havePageNo.length<2) {
                            history.replaceState({}, "", a);
                        } else {
                            a = havePageNo[0]+havePageNo[1].split('/').slice(1).join('/');
                            history.replaceState({}, "", a);
                        }
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    }
                }

                function addFiltersId($filtertype,$id)
                {
                    var pathname = window.location.pathname; // Returns path only
                    var url      = window.location.href;
                    var splitArray = pathname.split($filtertype+'/');
                    if (splitArray.length<2) {
                        if (pathname.substring(pathname.length-1, pathname.length) == '/') {
                            var a = pathname+$filtertype+'/'+$id;
                        } else {
                        var a = pathname+'/'+$filtertype+'/'+$id;
                        }
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    } else {
                        var a = splitArray[0]+$filtertype+'/'+$id+','+splitArray[1];
                        var x = splitArray[1].split('/')
                        if (x.length>=2) {
                            a+'/'+x[1];
                        }
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    }
                }

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
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    } else {
                        var aa = splitArray[1].split('/').slice(1).join('/');
                        var a = splitArray[0]+'pageNo/'+$pageNo+'/'+aa;
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    }
                }
                function removeFiltersId($filterType,$id)
                {
                    var pathname = window.location.pathname; // Returns path only
                    var url      = window.location.href;
                    var splitArray = pathname.split($filterType+'/');
                    var aa =         splitArray[1].split('/').slice(1).join('/');
                    var splitArray2 = splitArray[1].split('/');
                    var last = splitArray2[0].split($id);
                    if (splitArray2[0] == '' && splitArray2[1] == '/') {
                        splitArray[0] = splitArray[0].substring(0, splitArray[0].length-1);
                        var a = splitArray[0];
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    }
                    if (last[0] == '' && last[1] == '') {
                        var a = splitArray[0];
                        if (a.substring(a.length-1, a.length) == '/') {
                            a = a.substring(0, a.length-1);
                        }
                        if (aa.charAt(0) != '/') {
                            aa = '/'+aa;
                        }
                        history.replaceState({}, "", a+aa);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    }
                    if (last[0] == '' && last[1] != '') {
                        last[1] = last[1].substring(1, last[1].length);
                        // splitArray2[1] = splitArray2[1].splice(1);
                        var a = splitArray[0]+$filterType+'/'+last[1]+aa;
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    }if (last[0] != '' && last[1] == '') {
                        last[0] = last[0].substring(0, last[0].length-1);
                        var a = splitArray[0]+$filterType+'/'+last[0]+aa;
                        history.replaceState({}, "", a);
                        pathname = window.location.pathname;
                        var pathnameSplit = pathname.split('key/');
                        return pathnameSplit[1].split('/').slice(1).join('/');
                    }
                }

                function ajaxRequest($parameter,$havePagination = 0)
                {
                    $.ajax({
                        url:ticketsGridGetTicketsAjaxUrl+$parameter,
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
                            $.each($data['ticket_data'], function (index,element) {
                                if (index != "allAgent" ) {
                                    var employeeTemplate = template('#employee-template');
                                    employee += employeeTemplate({
                                                    data: {
                                                        priority: this['priority'],
                                                        priorityColor: this['priority_color'],
                                                        ticket: this['id'],
                                                        name: this['name'],
                                                        subject: this['subject'],
                                                        date: this['creation_date'],
                                                        replies: this['replies'],
                                                        agent: this['agent'],
                                                        incrementId :this['incrementId'],
                                                        allAgentList : $data['agent-information'],
                                                        pageCount:this['pageCount']
                                                    }
                                    });
                                }
                            });
                            if (employee == "") {
                                employee = '<tr><td class="text-center" colspan="9">No Tickets Found!</td></tr>';
                            }
                            $.each($data['tab_data'], function () {
                                var employeTabTemplate = template('#employeetab-template');
                                 employeTab += employeTabTemplate({
                                                data: {
                                                    tabId: this['tab_id'],
                                                    tabCount: this['tab_count'],
                                                    tabName: this['tab_name']
                                                }
                                });
                            });
                            if ($havePagination) {
                                $.each($data['pagination_data'], function () {
                                    var ticketPaginationTemplate = template('#ticketPagination-template');
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
                            $('.pagination').html(ticketPagination);
                            }

                            $('#ticket-body').html(employee);
                            $('.uvdesk_navbar').html(employeTab);
                            $("[tab-id='"+activeTabId+"']").addClass('active');
                        }
                    });
               }
               
               function agentAssignAjaxRequest($ticketId,$agentId,$currentAgentSelector,$SelectedAgentName)
               {
                   $.ajax({
                        url:ticketsGridAgentAssignAjaxUrl,
                        type:"GET",
                        dataType:"json",
                        data:{
                            ticketid:$ticketId,
                            agentId:$agentId
                        },
                        beforeSend:function () {
                            $("#wait").css("display", "block");
                        },
                        complete: function () {
                            $("#wait").css("display", "none");
                        },
                       success:function ($data) {
                        if ($data['info'] == 200) {
                            $currentAgentSelector.text($SelectedAgentName);
                            alert({
                                title: 'Agent Assignment Process..',
                                content: "Success ! Agent successfully assigned.",
                                actions: {
                                    always: function (){}
                                }
                            });
                        } else {
                            alert({
                                title: 'Agent Assignment Process..',
                                content: "Error ! Agent not assigned.",
                                actions: {
                                    always: function (){}
                                }
                            });
                        }
                       },
                       error:function () {
                        alert({
                            title: 'Agent Assignment Process..',
                            content: "Error ! Agent not assigned.",
                            actions: {
                                always: function (){}
                            }
                        });
                       }
                   });
               }
            });
        }
    });
    return $.mage.ticketsGrid;
});