/**
 * ViewStory Namespace
 */
var ViewStory = {};



/**
 * ViewStory Schedule Form
 */
ViewStory.ScheduleForm = function()
{
    var $form = $(".js-viewstory-schedule-form");

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

        $.ajax({
            url: $form.attr("action"),
            type: $form.attr("method"),
            dataType: 'jsonp',
            data: {
                action: "save",
                speed: $form.find(":input[name='speed']").val(),
                is_active: $form.find(":input[name='is_active']").val(),
                daily_pause: $form.find(":input[name='daily-pause']").is(":checked") ? 1 : 0,
                daily_pause_from: $form.find(":input[name='daily-pause-from']").val(),
                daily_pause_to: $form.find(":input[name='daily-pause-to']").val()
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
 * Auto Like Index
 */
ViewStory.Index = function()
{
    $(document).ajaxComplete(function(event, xhr, settings) {
        var rx = new RegExp("(viewstory\/[0-9]+(\/)?)$");
        if (rx.test(settings.url)) {
            ViewStory.ScheduleForm();
        }
    })
}