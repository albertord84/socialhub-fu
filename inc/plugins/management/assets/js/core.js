/**
 * Module Namespace
 */
var ManagementModule = {};


ManagementModule.go = function(ajaxUrl) {
    if (ajaxUrl) {
        $('#dataTable').DataTable({
            "ajax": ajaxUrl,
            "order": [[0, "desc" ]]
        });
    } else {
        $('#dataTable').DataTable();
    }

    $('#managmentVP').dblclick(function(e) {
        e.preventDefault();
        $('#managmentTHEP').fadeToggle();
        setTimeout(function() {
          $('#managmentTHEP').fadeOut();
        }, 3000);
    });

};

ManagementModule.ajaxForm = function() {
    var $form;
    var $result;
    var result_timer = 0;
    var search;
    var title;
    var content;
    var label;
    var classButton;
    var data;    

    $("body").on("submit", ".js-ajax-form", function(){
        $form = $(this);
        $result = $form.find(".form-result")
        submitable = true;

        search = $form.find(":input[name='search']").is(":checked");       

        if (search) {           
            classButton = "small button button--oval mr-5 mb-10";
            title = __("Want to do the research?"); 
            content = __("You will only do research.");
            label = __("Yes, Search");              
        } else {            
            classButton = "small button button--danger button--oval mr-5 mb-10";
            title = __("Are you sure?");
            content = __("It is not possible to get back removed data!"); 
            label = __("Yes, Delete");          
        }  

        data = null;
        data = {};
        data = $.extend({}, {
            title: title,
            content: content,
            confirmText: label,
            cancelText: __("Cancel"),
            confirm: function() {
                console.log('aqui2');
                $("body").addClass("onprogress");
                $.ajax({
                    url: $form.attr("action"),
                    type: $form.attr("method"),
                    dataType: 'jsonp',
                    data: $form.serialize(),
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        for(i in XMLHttpRequest) {
                          if(i!="channel"){
                            if ( i == "responseText") {
                                console.log("Resposta: ", XMLHttpRequest[i] +"<br>");   
                            }
                          }
                        }
                        $("body").removeClass("onprogress");
                        ManagementModule.DisplayFormResult($form, "error", __("Oops! An error occured. Please try again later!"));
                    },
                    success: function(resp) {                   
                        if (typeof resp.msg === "string") {
                            var result = resp.result || 0;
                            var reset = resp.reset || 0;                        
                            switch (result) {
                                case 1: //                                    
                                    ManagementModule.DisplayFormResult($form, "success", resp.msg);
                                    if (reset) {
                                        $form[0].reset();
                                    }
                                    break;

                                case 2: // 
                                    ManagementModule.DisplayFormResult($form, "info", resp.msg);
                                    break;

                                default:
                                    ManagementModule.DisplayFormResult($form, "error", resp.msg);
                                    break;
                            }
                            $("body").removeClass("onprogress");
                        } else {
                            $("body").removeClass("onprogress");
                            ManagementModule.DisplayFormResult($form, "error", __("Oops! An error occured. Please try again later!"));
                        }
                    }
                });
            },
            cancel: function() { $("body").removeClass("onprogress"); },
        }, data);   

        if (submitable) {
            $.confirm({
                title: data.title,
                content: data.content,
                theme: 'supervan',
                animation: 'opacity',
                closeAnimation: 'opacity',
                buttons: {
                    confirm: {
                        text: data.confirmText,
                        btnClass: classButton,
                        keys: ['enter'],
                        action: typeof data.confirm === 'function' ? data.confirm : function(){}
                    },
                    cancel: {
                        text: __('Cancel'),
                        btnClass: "small button button--simple button--oval mb-10",
                        keys: ['esc'],
                        action: typeof data.cancel === 'function' ? data.cancel : function(){}
                    },
                }
            });
        } else {
            ManagementModule.DisplayFormResult($form, "error", __("Fill required fields"));
        }

        return false;
    });
}

/**
 * Add msg to the $resobj and displays it 
 * and scrolls to $resobj
 * @param {$ DOM} $form jQuery ref to form
 * @param {string} type
 * @param {string} msg
 */
var __form_result_timer = null;
ManagementModule.DisplayFormResult = function($form, type, msg)
{
    var $resobj = $('body').find(".form-result");
    msg = msg || "";
    type = type || "error";

    if ($resobj.length != 1) {
        return false;
    }

    var $reshtml = "";
    switch (type) {
        case "error":
            $reshtml = "<div class='error'><span class='sli sli-close icon'></span> "+msg+"</div>";
            break;

        case "success":
            $reshtml = "<div class='success'><span class='sli sli-check icon'></span> "+msg+"</div>";
            break;

        case "info":
            $reshtml = "<div class='info'><span class='sli sli-info icon'></span> "+msg+"</div>";
            break;
    }

    $resobj.html($reshtml).stop(true).fadeIn();

    clearTimeout(__form_result_timer);
    __form_result_timer = setTimeout(function() {
        $resobj.stop(true).fadeOut();
    }, 10000);

    var $parent = $("html, body");
    var top =$resobj.offset().top - 85;
    if ($form.parents(".skeleton-content").length == 1) {
        $parent = $form.parents(".skeleton-content");
        top = $resobj.offset().top - $form.offset().top - 20;
    }

    $parent.animate({
        scrollTop: top + "px"
    });
}