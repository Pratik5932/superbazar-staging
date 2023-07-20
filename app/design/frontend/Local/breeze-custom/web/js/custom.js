require(['jquery', 'jquery/ui'], function ($) {
  $(document).ready(function () {


    // For remove title from hover the myacount and wishlist from home page
    $(".page-header  .header.content > .switcher").find('a').removeAttr("title");

    // For Adding Date picker from dob on create customer 
    setTimeout(function () {
      $(".customer-account-create .control.customer-dob > input#dob").attr("type", "date");
    }, 2500);


    var sellerlength = $(".account.customer-account-index .block.account-nav.block-collapsible-nav.wk-mp-main .account-nav .nav.items .nav.item ").length;
    if (sellerlength == 1) {
      $(" .block.account-nav.block-collapsible-nav.wk-mp-main .account-nav .nav.items .nav.item").parents(".wk-mp-main").hide();
    }

    $(".dob-tip span#tooltip").click(function () {
      $(".dob-tip-content").toggleClass("active");
    });

    var myDiv = $('.link.wishlist .counter.qty');

    setTimeout(function () {
      if ($('.link.wishlist .counter.qty').length) {
        $(".switcher-wishlist a.action.toggle.switcher-trigger").addClass('item-available');
      }
      else {
        $(".switcher-wishlist a.action.toggle.switcher-trigger").removeClass('item-available');
      }
    }, 2500);
    $(".search-cleartext").on("keyup", function () {
      let value = $(this).val();
      $(".clearable__clear").css('display', 'block');
    });
    $(".clearable__clear").click(function () {
      $(".search-cleartext").val("");
      $(".clearable__clear").css('display', 'none');
    });

  });

});