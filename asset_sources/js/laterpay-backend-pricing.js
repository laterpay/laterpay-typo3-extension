(function($) {$(function() {

    // encapsulate all LaterPay Javascript in function laterPayBackendPricing
    function laterPayBackendPricing() {
        var $o = {
                revenueModel                            : '.lp_js_revenueModel',
                revenueModelLabel                       : '.lp_js_revenueModel_label',
                revenueModelLabelDisplay                : '.lp_js_revenueModel_labelDisplay',
                revenueModelInput                       : '.lp_js_revenueModel_input',
                priceInput                              : '.lp_js_priceInput',

                // time passes
                addTimePass                             : $('#lp_js_addTimePass'),
                timePassEditor                          : $('.lp_js_timePassEditor'),
                timePassTemplate                        : $('#lp_js_timePassTemplate'),
                timePassWrapper                         : '.lp_js_timePassWrapper',
                timePassFormTemplate                    : $('#lp_js_timePassFormTemplate'),
                timePassFormId                          : 'lp_js_timePassForm',
                timePassForm                            : '.lp_js_timePassEditor_form',
                timePassDuration                        : '.lp_js_switchTimePassDuration',
                timePassDurationClass                   : 'lp_js_switchTimePassDuration',
                timePassPeriod                          : '.lp_js_switchTimePassPeriod',
                timePassPeriodClass                     : 'lp_js_switchTimePassPeriod',
                timePassScope                           : '.lp_js_switchTimePassScope',
                timePassScopeClass                      : 'lp_js_switchTimePassScope',
                timePassScopeCategory                   : '.lp_js_switchTimePassScopeCategory',
                timePassScopeCategoryClass              : 'lp_js_switchTimePassScopeCategory',
                timePassCategoryId                      : '.lp_js_timePassCategoryId',
                timePassCategoryWrapper                 : '.lp_js_timePassCategoryWrapper',
                timePassTitle                           : '.lp_js_timePassTitleInput',
                timePassTitleClass                      : 'lp_js_timePassTitleInput',
                timePassPrice                           : '.lp_js_timePassPriceInput',
                timePassPriceClass                      : 'lp_js_timePassPriceInput',
                timePassRevenueModel                    : '.lp_js_timePassRevenueModelInput',
                timePassDescription                     : '.lp_js_timePassDescriptionTextarea',
                timePassDescriptionClass                : 'lp_js_timePassDescriptionTextarea',
                timePassPreviewTitle                    : '.lp_js_timePassPreviewTitle',
                timePassPreviewDescription              : '.lp_js_timePassPreviewDescription',
                timePassPreviewValidity                 : '.lp_js_timePassPreviewValidity',
                timePassPreviewAccess                   : '.lp_js_timePassPreviewAccess',
                timePassPreviewPrice                    : '.lp_js_timePassPreviewPrice',
                timePassId                              : '.lp_js_timePassId',

                // vouchers
                voucherPriceInput                       : '.lp_js_voucherPriceInput',
                generateVoucherCode                     : '.lp_js_generateVoucherCode',
                voucherDeleteLink                       : '.lp_js_deleteVoucher',
                voucherEditor                           : '.lp_js_voucherEditor',
                voucherHiddenPassId                     : '#lp_js_timePassEditor_hiddenPassId',
                voucherPlaceholder                      : '.lp_js_voucherPlaceholder',
                voucherList                             : '.lp_js_voucherList',
                voucher                                 : '.lp_js_voucher',
                voucherTimesRedeemed                    : '.lp_js_voucherTimesRedeemed',

                // strings cached for better compression
                editing                                 : 'lp_is-editing',
                unsaved                                 : 'lp_is-unsaved',
                payPerUse                               : 'ppu',
                singleSale                              : 'sis',
                selected                                : 'lp_is-selected',
                disabled                                : 'lp_is-disabled',
                hidden                                  : 'lp_u_hide',
            },

            bindEvents = function() {
                // time passes events ----------------------------------------------------------------------------------
                // add
                $o.addTimePass
                .mousedown(function() {
                    addTimePass();
                })
                .click(function(e) {e.preventDefault();});

                // edit
                $o.timePassEditor
                .on('mousedown', '.lp_js_editTimePass', function() {
                    editTimePass($(this).parents($o.timePassWrapper));
                })
                .on('click', '.lp_js_editTimePass' , function(e) {e.preventDefault();});

                // toggle revenue model
                $o.timePassEditor
                .on('change', $o.timePassRevenueModel, function() {
                    toggleTimePassRevenueModel($(this).parents('form'));
                });

                // change duration
                $o.timePassEditor
                .on('change', $o.timePassDuration, function() {
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                // change period
                $o.timePassEditor
                .on('change', $o.timePassPeriod, function() {
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                // change scope
                $o.timePassEditor
                .on('change', $o.timePassScope, function() {
                    changeTimePassScope($(this));
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                $o.timePassEditor
                .on('change', $o.timePassScopeCategory, function() {
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                // update time pass configuration
                $o.timePassEditor
                .on('input', [$o.timePassTitle, $o.timePassDescription].join(), function() {
                    updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                });

                // set price
                $o.timePassEditor
                .on('keyup', $o.timePassPrice, debounce(function() {
                        validatePrice($(this).parents('form'), false, $(this));
                        updateTimePassPreview($(this).parents($o.timePassWrapper), $(this));
                    }, 800)
                );

                // cancel
                $o.timePassEditor
                .on('click', '.lp_js_cancelEditingTimePass', function(e) {
                    cancelEditingTimePass($(this).parents($o.timePassWrapper));
                    e.preventDefault();
                });

                // save
                $o.timePassEditor
                .on('click', '.lp_js_saveTimePass', function(e) {
                    saveTimePass($(this).parents($o.timePassWrapper));
                    e.preventDefault();
                });

                // delete
                $o.timePassEditor
                .on('click', '.lp_js_deleteTimePass', function(e) {
                    deleteTimePass($(this).parents($o.timePassWrapper));
                    e.preventDefault();
                });

                // flip
                $o.timePassEditor
                .on('mousedown', '.lp_js_flipTimePass', function() {
                    flipTimePass(this);
                })
                .on('click', '.lp_js_flipTimePass', function(e) {e.preventDefault();});

                // set voucher price
                $o.timePassEditor
                .on('keyup', $o.voucherPriceInput, debounce(function() {
                        validatePrice($(this).parents('form'), true, $(this));
                    }, 800)
                );

                // generate voucher code
                $o.timePassEditor
                .on('mousedown', $o.generateVoucherCode, function() {
                    generateVoucherCode($(this).parents($o.timePassWrapper));
                })
                .on('click', $o.generateVoucherCode, function(e) {
                    e.preventDefault();
                });

                // delete voucher code
                $o.timePassEditor
                .on('click', $o.voucherDeleteLink, function(e) {
                    deleteVoucher($(this).parent());
                    e.preventDefault();
                });
            },

            validatePrice = function($form, invalidPrice, $input) {
                var $priceInput = $input ? $input : $('.lp_numberInput', $form),
                    price       = $priceInput.val();

                // strip non-number characters
                price = price.replace(/[^0-9\,\.]/g, '');

                // convert price to proper float value
                if (price.indexOf(',') > -1) {
                    price = parseFloat(price.replace(',', '.')).toFixed(2);
                } else {
                    price = parseFloat(price).toFixed(2);
                }

                // prevent non-number prices
                if (isNaN(price)) {
                    price       = 0;
                }

                // prevent negative prices
                price = Math.abs(price);

                if (!invalidPrice) {
                    // correct prices outside the allowed range of 0.05 - 149.49
                    if (price > 149.99) {
                        price       = 149.99;
                    } else if (price > 0 && price < 0.05) {
                        price       = 0.05;
                    }

                    validateRevenueModel(price, $form);
                }

                // format price with two digits
                price = price.toFixed(2);

                // localize price
                if (lpVars.locale === 'de_DE') {
                    price = price.replace('.', ',');
                }

                // update price input
                $priceInput.val(price);

                return price;
            },

            validateRevenueModel = function(price, $form) {
                var currentRevenueModel;

                // for passes
                if ($form.hasClass('lp_js_timePassEditor_form')) {
                    var $toggle         = $($o.timePassRevenueModel, $form),
                        hasRevenueModel = $toggle.prop('checked');

                    currentRevenueModel = hasRevenueModel ? $o.singleSale : $o.payPerUse;

                    // switch revenue model, if combination of price and revenue model is not allowed
                    if (price > 5 && currentRevenueModel === $o.payPerUse) {
                        // Pay-per-Use purchases are not allowed for prices > 5.00 Euro
                        $toggle.prop('checked', true);
                    } else if (price < 1.49 && currentRevenueModel === $o.singleSale) {
                        // Single Sale purchases are not allowed for prices < 1.49 Euro
                        $toggle.prop('checked', false);
                    }
                // for category price and global price
                } else {
                    var $payPerUse          = $('.lp_js_revenueModel_input[value=' + $o.payPerUse + ']', $form),
                        $singleSale         = $('.lp_js_revenueModel_input[value=' + $o.singleSale + ']', $form);

                    currentRevenueModel = $('input:radio:checked', $form).val();

                    if (price === 0 || (price >= 0.05 && price <= 5)) {
                        // enable Pay-per-Use for 0 and all prices between 0.05 and 5.00 Euro
                        $payPerUse.removeProp('disabled')
                            .parent('label').removeClass($o.disabled);
                    } else {
                        // disable Pay-per-Use
                        $payPerUse.prop('disabled', 'disabled')
                            .parent('label').addClass($o.disabled);
                    }

                    if (price >= 1.49) {
                        // enable Single Sale for prices >= 1.49 Euro
                        // (prices > 149.99 Euro are fixed by validatePrice already)
                        $singleSale.removeProp('disabled')
                            .parent('label').removeClass($o.disabled);
                    } else {
                        // disable Single Sale
                        $singleSale.prop('disabled', 'disabled')
                            .parent('label').addClass($o.disabled);
                    }

                    // switch revenue model, if combination of price and revenue model is not allowed
                    if (price > 5 && currentRevenueModel === $o.payPerUse) {
                        // Pay-per-Use purchases are not allowed for prices > 5.00 Euro
                        $singleSale.prop('checked', 'checked');
                    } else if (price < 1.49 && currentRevenueModel === $o.singleSale) {
                        // Single Sale purchases are not allowed for prices < 1.49 Euro
                        $payPerUse.prop('checked', 'checked');
                    }

                    // highlight current revenue model
                    $('label', $form).removeClass($o.selected);
                    $('.lp_js_revenueModel_input:checked', $form).parent('label').addClass($o.selected);
                }
            },

            formatSelect2TimePass = function(data, container) {
                var $form = $(container).parents('form');

                if (data.id) {
                    $($o.timePassCategoryId, $form).val(data.id);
                }
                $($o.timePassScopeCategory, $form).val(data.text);

                return data.text;
            },

            renderCategorySelect = function($form, selector, form, format_func) {
                $(selector, $form).select2({
                    allowClear      : true,
                    ajax            : {
                                        url         : ajaxurl,
                                        data        : function(term) {
                                                        return {
                                                            form    : form,
                                                            term    : term,
                                                            action  : 'laterpay_pricing'
                                                        };
                                                    },
                                        results     : function(data) {
                                                        var return_data = [];

                                                        $.each(data, function(index) {
                                                            var term = data[ index ];
                                                            return_data.push({
                                                                id     : term.term_id,
                                                                text   : term.name
                                                            });
                                                        });

                                                        return {results: return_data};
                                                    },
                                        dataType    : 'json',
                                        type: 'POST'
                                    },
                    initSelection   : function(element, callback) {
                                        var id = $(element).val();
                                        if (id !== '') {
                                            var data = {text: id};
                                            callback(data);
                                        } else {
                                            $.post(
                                                ajaxurl,
                                                {
                                                    form    : form,
                                                    term    : '',
                                                    action  : 'laterpay_pricing'
                                                },
                                                function(data) {
                                                    if (data && data[0] !== undefined) {
                                                        var term = data[0];
                                                        callback({id: term.term_id, text: term.name});
                                                    }
                                                }
                                            );
                                        }
                                    },
                    formatResult    : function(data) {return data.text;},
                    formatSelection : format_func,
                    escapeMarkup    : function(m) {return m;}
                });
            },

            addTimePass = function() {
                // hide "add time pass" button
                $o.addTimePass.fadeOut(250);

                // prepend cloned time pass template to time pass editor
                $o.timePassEditor.prepend($o.timePassTemplate.clone().removeAttr('id'));
                var $timePass = $('.lp_js_timePassWrapper', $o.timePassEditor).first();
                $($o.timePassForm, $timePass).attr('id', $o.timePassFormId).addClass($o.unsaved);

                populateTimePassForm($timePass);

                // show time pass
                $timePass
                .slideDown(250, function() {
                    $(this).removeClass('lp_u_hide');
                })
                    .find($o.timePassForm)
                    .slideDown(250, function() {
                        $(this).removeClass('lp_u_hide');
                    });
            },

            editTimePass = function($timePass) {
                // insert cloned form into current time pass editor container
                var $timePassForm = $o.timePassFormTemplate.clone().attr('id', $o.timePassFormId);
                $('.lp_js_timePass_editorContainer', $timePass).html($timePassForm);

                populateTimePassForm($timePass);

                // hide action links required when displaying time pass
                $('.lp_js_editTimePass, .lp_js_deleteTimePass', $timePass).addClass('lp_u_hide');

                // show action links required when editing time pass
                $('.lp_js_saveTimePass, .lp_js_cancelEditingTimePass', $timePass).removeClass('lp_u_hide');

                $timePassForm.removeClass('lp_u_hide');
            },

            populateTimePassForm = function($timePass) {
                var passId      = $timePass.data('pass-id'),
                    passData    = lpVars.time_passes_list[passId],
                    vouchers    = lpVars.vouchers_list[passId],
                    $toggle     = $($o.timePassRevenueModel, $timePass),
                    name        = '';

                if (!passData) {
                    return;
                }

                // apply passData to inputs
                $('input, select, textarea', $timePass)
                .each(function(i, v) {
                    name = $(v, $timePass).attr('name');
                    if (name !== '' && passData[name] !== undefined && name !== 'revenue_model') {
                        $(v, $timePass).val(passData[name]);
                        updateTimePassPreview($timePass, $(v, $timePass));
                    }
                });

                // validate price after inserting
                validatePrice($timePass.find('form'), false, $($o.timePassPrice, $timePass));
                // set price input value into the voucher price input
                $($o.voucherPriceInput, $timePass).val($($o.timePassPrice, $timePass).val());

                // apply passData to revenue model toggle
                if (passData.revenue_model === $o.singleSale) {
                    $toggle.prop('checked', true);
                }

                $($o.timePassCategoryWrapper, $timePass).hide();
                // render category select
                renderCategorySelect(
                    $timePass,
                    $o.timePassScopeCategory,
                    'laterpay_get_categories',
                    formatSelect2TimePass
                );

                // show category select, if required
                var $currentScope = $($o.timePassScope, $timePass).find('option:selected');
                if ($currentScope.val() !== '0') {
                    // show category select, because scope is restricted to or excludes a specific category
                    $($o.timePassCategoryWrapper, $timePass).show();
                }

                // re-generate vouchers list
                clearVouchersList($timePass);
                if (vouchers instanceof Object) {
                    $.each(vouchers, function(code, priceValue) {
                        addVoucher(code, priceValue, $timePass);
                    });
                }
            },

            updateTimePassPreview = function($timePass, $input) {
                // insert at least one space to avoid placeholder to collapse
                var text = ($input.val() !== '') ? $input.val() : ' ';

                if ($input.hasClass($o.timePassDurationClass) || $input.hasClass($o.timePassPeriodClass)) {
                    var duration    = $($o.timePassDuration, $timePass).val(),
                        period      = $($o.timePassPeriod, $timePass).find('option:selected').text();
                    // pluralize period (TODO: internationalize properly)
                    period  = (parseInt(duration, 10) > 1) ? period + 's' : period;
                    text    = duration + ' ' + period;
                    // update pass validity in pass preview
                    $($o.timePassPreviewValidity, $timePass).text(text);
                } else if ($input.hasClass($o.timePassScopeClass) || $input.hasClass($o.timePassScopeCategoryClass)) {
                    var currentScope = $($o.timePassScope, $timePass).find('option:selected');
                    text = currentScope.text();
                    if (currentScope.val() !== '0') {
                        // append selected category, because scope is restricted to or excludes a specific category
                        text += ' ' + $($o.timePassScopeCategory, $timePass).val();
                    }
                    // update pass access in pass preview
                    $($o.timePassPreviewAccess, $timePass).text(text);
                } else if ($input.hasClass($o.timePassPriceClass)) {
                    // update pass price in pass preview
                    $('.lp_purchaseLink', $timePass).html(text + '<small>' + lpVars.defaultCurrency + '</small>');
                    $($o.timePassPreviewPrice, $timePass).text(text + ' ' + lpVars.defaultCurrency);
                } else if ($input.hasClass($o.timePassTitleClass)) {
                    // update pass title in pass preview
                    $($o.timePassPreviewTitle, $timePass).text(text);
                } else if ($input.hasClass($o.timePassDescriptionClass)) {
                    // update pass description in pass preview
                    $($o.timePassPreviewDescription, $timePass).text(text);
                }
            },

            cancelEditingTimePass = function($timePass) {
                // show vouchers
                $timePass.find($o.voucherList).show();

                if ($($o.timePassForm, $timePass).hasClass($o.unsaved)) {
                    // remove entire time pass, if it is a new, unsaved pass
                    $timePass.fadeOut(250, function() {
                        $(this).remove();
                    });
                } else {
                    // remove cloned time pass form
                    $($o.timePassForm, $timePass).fadeOut(250, function() {
                        $(this).remove();
                    });
                }
                // TODO: unbind events

                // show action links required when displaying time pass
                $('.lp_js_editTimePass, .lp_js_deleteTimePass', $timePass).removeClass('lp_u_hide');

                // hide action links required when editing time pass
                $('.lp_js_saveTimePass, .lp_js_cancelEditingTimePass', $timePass).addClass('lp_u_hide');

                // show "add time pass" button, if it is hidden
                if ($o.addTimePass.is(':hidden')) {
                    $o.addTimePass.fadeIn(250);
                }
            },

            saveTimePass = function($timePass) {
                $.post(
                    ajaxurl,
                    $($o.timePassForm, $timePass).serializeArray(),
                    function(r) {
                        if (r.success) {
                            // form has been saved
                            var passId = r.data.pass_id;
                            // update vouchers
                            lpVars.vouchers_list[passId] = r.vouchers;

                            // re-generate vouchers list
                            clearVouchersList($timePass);
                            if (lpVars.vouchers_list[passId] instanceof Object) {
                                $.each(lpVars.vouchers_list[passId], function(code, priceValue) {
                                    addVoucherToList(code, priceValue, $timePass);
                                });

                                // show vouchers
                                $timePass.find($o.voucherList).show();
                            }

                            if (lpVars.time_passes_list[passId]) {
                                // pass already exists (update)
                                lpVars.time_passes_list[passId] = r.data;
                                // insert time pass rendered on server
                                $('.lp_js_timePassPreview', $timePass).html(r.html);

                                // hide action links required when editing time pass
                                $('.lp_js_saveTimePass, .lp_js_cancelEditingTimePass', $timePass).addClass('lp_u_hide');
                                // show action links required when displaying time pass
                                $('.lp_js_editTimePass, .lp_js_deleteTimePass', $timePass).removeClass('lp_u_hide');
                                $($o.timePassForm, $timePass).fadeOut(250, function() {
                                    $(this).remove();
                                });
                            } else {
                                // pass was just created (add)
                                lpVars.time_passes_list[passId] = r.data;
                                var $newTimePass = $o.timePassTemplate.clone().removeAttr('id').data('pass-id', passId);

                                // show assigned pass id
                                $($o.timePassId, $newTimePass)
                                .text(passId)
                                    .parent()
                                    .show(250);

                                $('.lp_js_timePassPreview', $newTimePass).html(r.html);
                                $($o.timePassForm, $timePass).remove();

                                $o.addTimePass.after($newTimePass);

                                populateTimePassForm($newTimePass);

                                // hide action links required when editing time pass
                                $('.lp_js_saveTimePass, .lp_js_cancelEditingTimePass', $newTimePass)
                                .addClass('lp_u_hide');
                                // show action links required when displaying time pass
                                $('.lp_js_editTimePass, .lp_js_deleteTimePass', $newTimePass)
                                .removeClass('lp_u_hide');

                                $timePass.fadeOut(250, function() {
                                    $(this).remove();
                                    $newTimePass.removeClass('lp_u_hide');
                                });
                            }
                        }

                        if ($o.addTimePass.is(':hidden')) {
                            $o.addTimePass.fadeIn(250);
                        }

                        setMessage(r.message, r.success);
                    },
                    'json'
                );
            },

            deleteTimePass = function($timePass) {
                // require confirmation
                if (confirm(lpVars.i18n.confirmDeleteTimePass)) {
                    // fade out and remove time pass
                    $timePass
                    .slideUp({
                        duration: 250,
                        start: function() {
                            $.post(
                                ajaxurl,
                                {
                                    action  : 'laterpay_pricing',
                                    form    : 'time_pass_delete',
                                    pass_id : $timePass.data('pass-id'),
                                },
                                function(r) {
                                    if (r.success) {
                                        $(this).remove();
                                        // TODO: unbind events
                                    } else {
                                        $(this).stop().show();
                                    }
                                    setMessage(r.message, r.success);
                                },
                                'json'
                            );
                        }
                    });
                }
            },

            flipTimePass = function(trigger) {
                $(trigger).parents('.lp_timePass').toggleClass('lp_is-flipped');
            },

            changeTimePassScope = function($trigger) {
                var o = $('option:selected', $trigger).val();
                if (o === '0') {
                    // option 'all content'
                    $($o.timePassCategoryWrapper).hide();
                } else {
                    // option restricts access to or excludes access from specific category
                    $($o.timePassCategoryWrapper).show();
                }
            },

            toggleTimePassRevenueModel = function($form) {
                // validate price
                validatePrice($form, false, $($o.timePassPrice, $form));
            },

            generateVoucherCode = function($timePass) {
                $.post(
                    ajaxurl,
                    {
                        form   : 'generate_voucher_code',
                        action : 'laterpay_pricing'
                    },
                    function(r) {
                        if (r.success) {
                            addVoucher(r.code, $timePass.find($o.voucherPriceInput).val(), $timePass);
                        }
                    }
                );
            },

            addVoucher = function(code, priceValue, $timePass) {
                var price   = priceValue + ' ' + lpVars.defaultCurrency,
                    voucher =   '<div class="lp_js_voucher lp_voucherRow" ' +
                                        'data-code="' + code + '" ' +
                                        'style="display:none;">' +
                                    '<input type="hidden" name="voucher[]" value="' + code + '|' + priceValue + '">' +
                                    '<span class="lp_voucherCodeLabel">' + code + '</span>' +
                                    '<span class="lp_voucherCodeInfos">' +
                                        lpVars.i18n.voucherText + ' ' + price +
                                    '</span>' +
                                    '<a href="#" class="lp_js_deleteVoucher lp_editLink lp_deleteLink" data-icon="g">' +
                                        lpVars.i18n.delete +
                                    '</a>' +
                                '</div>';

                $timePass.find($o.voucherPlaceholder).prepend(voucher).find('div').first().slideDown(250);
            },

            addVoucherToList = function(code, priceValue, $timePass) {
                var passId          = $timePass.data('pass-id'),
                    timesRedeemed   = lpVars.vouchers_statistic[passId] ? lpVars.vouchers_statistic[passId] : 0,
                    price           = priceValue + ' ' + lpVars.defaultCurrency,
                    voucher =   '<div class="lp_js_voucher lp_voucherRow" ' + 'data-code="' + code + '">' +
                                    '<span class="lp_voucherCodeInfos">' +
                                        lpVars.i18n.voucherText + ' ' + price + '.<br>' +
                                        '<span class="lp_js_voucherTimesRedeemed">' +
                                            timesRedeemed +
                                        '</span>' + lpVars.i18n.timesRedeemed +
                                    '</span>' +
                                '</div>';

                $timePass.find($o.voucherList).append(voucher);
            },

            clearVouchersList = function($timePass) {
                $timePass.find($o.voucher).remove();
            },

            deleteVoucher = function($voucher) {
                // slide up and remove voucher
                $voucher
                .slideUp(250, function() {
                    $(this).remove();
                });
            },

            // throttle the execution of a function by a given delay
            debounce = function(fn, delay) {
              var timer;
              return function () {
                var context = this,
                    args    = arguments;

                clearTimeout(timer);

                timer = setTimeout(function() {
                  fn.apply(context, args);
                }, delay);
              };
            },

            initializePage = function() {
                bindEvents();
            };

        initializePage();
    }

    // initialize page
    laterPayBackendPricing();

});})(jQuery);
