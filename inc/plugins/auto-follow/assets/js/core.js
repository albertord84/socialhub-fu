/**
 * AutoFollow Namespane
 */
var AutoFollow = {};



/**
 * AutoFollow Schedule Form
 */
AutoFollow.ScheduleForm = function()
{
    var $form = $(".js-auto-follow-schedule-form");
    var $searchinp = $form.find(":input[name='search']");
    var query;
    var icons = {};
        icons.hashtag = "mdi mdi-pound";
        icons.location = "mdi mdi-map-marker";
        icons.people = "mdi mdi-instagram";
    var target = [];

    // Current tags
    $form.find(".tag").each(function() {
        target.push($(this).data("type") + "-" + $(this).data("id"));
    });


    // Search input
    $searchinp.devbridgeAutocomplete({
        serviceUrl: $searchinp.data("url"),
        type: "GET",
        dataType: "jsonp",
        minChars: 2,
        deferRequestBy: 200,
        appendTo: $form,
        forceFixPosition: true,
        paramName: "q",
        params: {
            action: "search",
            type: $form.find(":input[name='type']:checked").val(),
        },
        onSearchStart: function() {
            $form.find(".js-search-loading-icon").removeClass('none');
            query = $searchinp.val();
        },
        onSearchComplete: function() {
            $form.find(".js-search-loading-icon").addClass('none');
        },

        transformResult: function(resp) {
            return {
                suggestions: resp.result == 1 ? resp.items : []
            };
        },

        beforeRender: function (container, suggestions) {
            for (var i = 0; i < suggestions.length; i++) {
                var type = $form.find(":input[name='type']:checked").val();
                if (target.indexOf(type + "-" + suggestions[i].data.id) >= 0) {
                    container.find(".autocomplete-suggestion").eq(i).addClass('none')
                }
            }
        },

        formatResult: function(suggestion, currentValue){
            var pattern = '(' + currentValue.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + ')';
            var type = $form.find(":input[name='type']:checked").val();

            return suggestion.value
                        .replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/&lt;(\/?strong)&gt;/g, '<$1>') +
                    (suggestion.data.sub ? "<span class='sub'>"+suggestion.data.sub+"<span>" : "");
        },

        onSelect: function(suggestion){
            $searchinp.val(query);
            var type = $form.find(":input[name='type']:checked").val();

            if (target.indexOf(type+"-"+suggestion.data.id) >= 0) {
                return false;
            }

            var $tag = $("<span style='margin: 0px 2px 3px 0px'></span>");
                $tag.addClass("tag pull-left preadd");
                $tag.attr({
                    "data-type": type,
                    "data-id": suggestion.data.id,
                    "data-value": suggestion.value,
                });
                $tag.text(suggestion.value);

                $tag.prepend("<span class='icon "+icons[type]+"'></span>");
                $tag.append("<span class='mdi mdi-close remove'></span>");

            $tag.appendTo($form.find(".tags"));

            setTimeout(function(){
                $tag.removeClass("preadd");
            }, 50);

            target.push(type+ "-" + suggestion.data.id);

        }
    });


    // Change search source
    $form.find(":input[name='type']").on("change", function() {
        var type = $form.find(":input[name='type']:checked").val();

        $searchinp.autocomplete('setOptions', {
            params: {
                action: "search",
                type: type
            }
        });

        $searchinp.trigger("blur");
        setTimeout(function(){
            $searchinp.trigger("focus");
        }, 200)
    });


    // Remove target
    $form.on("click", ".tag .remove", function() {
        var $tag = $(this).parents(".tag");

        var index = target.indexOf($tag.data("type") + "-" + $tag.data("id"));
        if (index >= 0) {
            target.splice(index, 1);
        }

        $tag.remove();
    });

    // Reset Form
    $(".remove-tags").on("click", function() {
  
        var tags = $form.find(".tags");
		target = [];
        tags[0].innerHTML = "";
    });

    // Import targets
    $(".import-tags").on("click", function() {

        var x = document.getElementById("myTargets").value;

        var t = JSON.parse(x);

        if(t === null){
            return false;
        }

        t.forEach(function(importtarget) {

            var $tag = $("<span style='margin: 0px 2px 3px 0px'></span>");

            $tag.addClass("tag pull-left preadd");
    
            $tag.attr({
                "data-type": importtarget.type,
                "data-id": importtarget.id,
                "data-value": importtarget.value,
            });
    
            $tag.text(importtarget.value);
            $tag.prepend("<span class='icon "+icons[importtarget.type]+"'></span>");
            $tag.append("<span class='mdi mdi-close remove'></span>");
            $tag.appendTo($form.find(".tags"));
    
            setTimeout(function(){
                $tag.removeClass("preadd");
            }, 50);

            target.push(importtarget.type+ "-" + importtarget.id);

        });

    });

    // Daily pause
    $form.find(":input[name='daily-pause']").on("change", function() {
        if ($(this).is(":checked")) {
            $form.find(".js-daily-pause-range").css("opacity", "1");
            $form.find(".js-daily-pause-range").find(":input").prop("disabled", false);
        } else {
            $form.find(".js-daily-pause-range").css("opacity", "0.25");
            $form.find(".js-daily-pause-range").find(":input").prop("disabled", true);
        }
    }).trigger("change");


    // Submit form
    $form.on("submit", function() {
        $("body").addClass("onprogress");

        var target = [];

        $form.find(".tags .tag").each(function() {
            var t = {};
                t.type = $(this).data("type");
                t.id = $(this).data("id").toString();
                t.value = $(this).data("value");

            target.push(t);
        });

        $.ajax({
            url: $form.attr("action"),
            type: $form.attr("method"),
            dataType: 'jsonp',
            data: {
                action: "save",
                target: JSON.stringify(target),
                speed: $form.find(":input[name='speed']").val(),
                is_active: $form.find(":input[name='is_active']").val(),
                powerlike: $form.find(":input[name='powerlike']").val(),
                powerlike_count: $form.find(":input[name='powerlike_count']").val(),
                powerlike_random: $form.find(":input[name='powerlike_random']").is(":checked") ? 1 : 0,
                filter_gender: $form.find(":input[name='filter_gender']").val(),
                filter_privat: $form.find(":input[name='filter_privat']").val(),
                filter_picture: $form.find(":input[name='filter_picture']").val(),
                filter_blacklist: $form.find(":input[name='filter_blacklist']").val(),                
                filter_business: $form.find(":input[name='filter_business']").val(),
                filter_media_min: $form.find(":input[name='filter_media_min']").val(),
                filter_accuracy: $form.find(":input[name='filter_accuracy']").val(),
                filter_media_days: $form.find(":input[name='filter_media_days']").val(),
                filter_unfollowed: $form.find(":input[name='filter_unfollowed']").val(),
                filter_followed_min: $form.find(":input[name='filter_followed_min']").val(),
                filter_followed_max: $form.find(":input[name='filter_followed_max']").val(),
                filter_following_min: $form.find(":input[name='filter_following_min']").val(),
                filter_following_max: $form.find(":input[name='filter_following_max']").val(),
                daily_pause: $form.find(":input[name='daily-pause']").is(":checked") ? 1 : 0,
                daily_pause_from: $form.find(":input[name='daily-pause-from']").val(),
                daily_pause_to: $form.find(":input[name='daily-pause-to']").val(),
            },
            error: function() {
                $("body").removeClass("onprogress");
                NextPost.DisplayFormResult($form, "error", __("Oops! An error occured. Please try again later!"));
            },

            success: function(resp) {
                if (resp.result == 1) {
                    NextPost.DisplayFormResult($form, "success", resp.msg);
                } else {
                    NextPost.DisplayFormResult($form, "error", resp.msg);
                }

                $("body").removeClass("onprogress");
            }
        });

        return false;
    });
}

/**
 * Auto Follow Index
 */
AutoFollow.Index = function()
{
    $(document).ajaxComplete(function(event, xhr, settings) {
        var rx = new RegExp("(auto-follow\/[0-9]+(\/)?)$");
        if (rx.test(settings.url)) {
            AutoFollow.ScheduleForm();
        }
    })
}
