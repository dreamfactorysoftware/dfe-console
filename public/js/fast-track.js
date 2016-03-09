/**
 * @type {{ignoreTitle: boolean, errorClass: string, errorElement: string, errorPlacement: DefaultValidateOptions.errorPlacement, highlight: DefaultValidateOptions.highlight, unhighlight: DefaultValidateOptions.unhighlight, rules: {}}}
 */
var DefaultValidateOptions = {
    ignoreTitle:    true,
    errorClass:     'help-block',
    errorElement:   'p',
    errorPlacement: function(error, element) {
        error.appendTo($(element).closest('div'));
    },
    highlight:      function(element, errorClass) {
        $(element).closest('.form-group').removeClass('has-success has-feedback').addClass('has-error has-feedback');
    },
    unhighlight:    function(element, errorClass) {
        $(element).closest('.form-group').removeClass('has-error has-feedback').addClass('has-success has-feedback');
    },
    rules:          {}
};

/** doc ready */
jQuery(function($) {
    var _rules = {
        email:                 {required: true, email: true},
        'first-name':          {required: true, minlength: 3, maxlength: 64},
        'last-name':           {required: true, minlength: 3, maxlength: 64},
        nickname:              {required: false, minlength: 3, maxlength: 64},
        company:               {required: false, minlength: 3, maxlength: 64},
        phone:                 {required: false, minlength: 3, maxlength: 64},
        password:              {required: true, minlength: 5, maxlength: 64},
        password_confirmation: {required: true, minlength: 5, maxlength: 64},
        submitHandler:         function(form) {
            //  do something...

            //  Show overlay
            $('.please-wait').removeClass('hidden');

            //  make call
            $.ajax(fast_track_endpoint, {
                method:   'POST',
                dataType: 'json',
                data:     $(form).serializeArray()
            }).done(function(data) {
                    //  Successful? Redirect
                    if (!data.success && data.error) {
                        $('#error-body')
                            .html('<p>' + (data.error.message.length ? data.error.message : 'System unavailable. Please try again later.') + '</p>');
                        $('#error-alert').removeClass('hidden');
                    } else {
                        if (data.response.redirect.location) {
                            window.top.location = data.response.redirect.location;
                        }

                        //  Partial success, show that stuff
                        console.log(JSON.stringify(data));
                    }
                }
            ).fail(function(data) {
                //  Fail? Show errors
                alert('fatal');
            }).always(function() {
                $('.please-wait').addClass('hidden');
            });

            return false;
        }
    };

    var _validator = $('#ft-register').validate($.extend({}, DefaultValidateOptions, _rules));
    var $_body = $('body'), _of = $_body.css('overflow');
});
