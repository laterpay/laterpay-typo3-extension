(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendAppearance
    function laterPayBackendAppearance() {
        var $o = {
                // paid content preview
                previewForm                     : $('#lp_js_previewModeForm'),
                togglePreviewMode               : $('.lp_js_togglePreviewMode'),

                // position of LaterPay elements
                purchaseButtonPositionForm      : $('#lp_js_purchaseButtonPositionForm'),
                togglePurchaseButtonPosition    : $('#lp_js_togglePurchaseButtonPosition'),
                purchaseButtonExplanation       : $('#lp_js_purchaseButtonPosition__explanation'),
                timePassPositionForm            : $('#lp_js_timePassesPositionForm'),
                toggleTimePassesPosition        : $('#lp_js_toggleTimePassesPosition'),
                timePassesExplanation           : $('#lp_js_timePassesPosition__explanation'),

                // auto-teaser generation
                teaserConfigForm                : $('#lp_js_autoTeaserContent'),
                teaserSaveConfigLink            : $('#lp_js_AutoTeaserContentSave'),
            },

            bindEvents = function() {
                // toggle paid content preview mode
                $($o.togglePreviewMode, $o.previewForm)
                .change(function() {
                    saveData($o.previewForm);
                });

                // toggle positioning mode of purchase button
                $o.togglePurchaseButtonPosition
                .change(function() {
                    saveData($o.purchaseButtonPositionForm);

                    // show / hide explanation how to customize position
                    if ($o.purchaseButtonExplanation.is(':visible')) {
                        $o.purchaseButtonExplanation.slideUp(250);
                    } else {
                        $o.purchaseButtonExplanation.slideDown(250);
                    }
                });

                // toggle positioning mode of time passes
                $o.toggleTimePassesPosition
                .change(function() {
                    saveData($o.timePassPositionForm);

                    // show / hide explanation how to customize position
                    if ($o.timePassesExplanation.is(':visible')) {
                        $o.timePassesExplanation.slideUp(250);
                    } else {
                        $o.timePassesExplanation.slideDown(250);
                    }
                });

                $o.teaserSaveConfigLink
                .click(function() {
                    $.post(
                        ajaxurl,
                        $o.teaserConfigForm.serializeArray(),
                        function(data) {
                            if (data.success) {
                                $o.teaserConfigForm.find('input').removeClass('error');
                                $o.teaserConfigForm.find('span').empty();
                                setMessage(data);
                            } else {
                                setMessage(data);

                                for (var error_field_name in data.errors) {
                                    if(data.errors.hasOwnProperty(error_field_name)) {

                                        $o.teaserConfigForm.find(
                                            'input[name='+data.errors[error_field_name]+']'
                                        ).addClass('error');
                                        $o.teaserConfigForm.find(
                                            'span[name='+data.errors[error_field_name]+']'
                                        ).empty().append(data.error_message[data.errors[error_field_name]]);
                                    }
                                }
                            }
                        }
                    );
                    return false;
                });
            },

            saveData = function( $form ) {
                $.post(
                    ajaxurl,
                    $form.serializeArray(),
                    function(data) {
                        setMessage(data);
                    }
                );
            },

            styleInputs = function() {
                $('.lp_js_styleInput').ezMark();
            },

            initializePage = function() {
                bindEvents();
                styleInputs();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendAppearance();

});})(jQuery);
