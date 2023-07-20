require([
    'jquery'
    ], function ($) {
        'use strict';
        jQuery(document).ready(function(){

            setTimeout(function(){
                console.log("ajax1");

                $( document ).ajaxComplete(function() {
                    setTimeout(function(){ 

                        console.log("ajax");
                        require('uiRegistry').get('index = product_listing')
                        .source
                        .reload({'refresh': true});

                        }, 5000);

                    /*jQuery(".action-primary").click(function(){
                    //console.log("click");
                    alert('cliek');

                    setTimeout(function(){ alert("reload");
                    require('uiRegistry').get('index = product_listing')
                    .source
                    .reload({'refresh': true});

                    }, 5000);

                    });
                    */  
                });              
                }, 15000);
        });
});