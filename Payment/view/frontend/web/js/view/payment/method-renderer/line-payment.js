/**
 * Copyright © 2026 Line. All rights reserved.
 */
define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Ui/js/model/messageList',
    'Line_Payment/js/model/config',
    'Line_Payment/js/action/get-promotions-by-bin',
    'Line_Payment/js/action/get-promotions',
    'Line_Payment/js/model/promotions',
    'Magento_Payment/js/model/credit-card-validation/credit-card-data',
    'Line_Payment/js/view/payment/card-number-mask',
    'Magento_Checkout/js/model/totals'
], function (
    $,
    ko,
    _,
    Component,
    messageList,
    config,
    getPromotionsByBinAction,
    getPromotionsAction,
    model,
    creditCardData,
    cardNumberMask,
    checkoutTotals
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Line_Payment/payment/line-payment',

            creditCardType: '',
            creditCardExpYear: '',
            creditCardExpMonth: '',
            creditCardNumber: '',
            creditCardSsStartMonth: '',
            creditCardSsStartYear: '',
            creditCardSsIssue: '',
            creditCardVerificationNumber: '',
            selectedCardType: null,
            selectedMerchantNumber: ko.observable(false),
            creditCardMethod: 'CREDIT',
            isInstallmentsVisible: ko.observable(true),
            creditCardDocumentType: 'DNI',

            code: 'linepayment',
            // final installment value to be sent
            installments: '',
            // list of promotions retrieved from Gateway
            promotions: {},
            // list of options for Checkout Dropdown
            // promotions matching different continions, corresponds to the exposed values in the Checkout
            availableInstallments: ko.observable(false),

            loadingPromotions: false,
            additionalData: {},

            // Make configuration object available for conditional rendering
            config: {},

            // if any data isn't loaded, we'll flag the form
            // to avoid checking out with this method
            hasLoadingErrors: ko.observable(false)
        },

        // Used to display exceptions during api interactions
        messageDispatcher: ko.observable(),

        initObservable: function () {
            this._super()
                .observe([
                    'creditCardType',
                    'creditCardBrand',
                    'creditCardExpYear',
                    'creditCardExpMonth',
                    'creditCardNumber',
                    'creditCardVerificationNumber',
                    'creditCardSsStartMonth',
                    'creditCardSsStartYear',
                    'creditCardSsIssue',
                    'selectedCardType',
                    'selectedCardBrand',

                    'creditCardHolderName',
                    'creditCardDocumentType',
                    'creditCardDocumentNumber',
                    'installments',
                    'creditCardMethod',
                    'isInstallmentsVisible'
                ]);

            return this;
        },

        hasErrors: function () {
            return this.hasLoadingErrors;
        },

        /**
         * Init component
         */
        initialize: function () {
            let self = this;

            this._super();

            this.config = config;

            model.paymentData.subscribe(function (data) {
                self.selectedCardType(data.card.type);
                self.selectedCardBrand(data.card.brand);
                self.creditCardType(data.card.type);
                self.creditCardBrand(data.card.brand);

                self.selectedMerchantNumber(data.merchant);
            });

            model.promotionsList.subscribe(function (promos) {
                // clean up previously selected, if any
                self.installments('');
                self.availableInstallments(promos);
                // reset selected plan when installment list changes
                model.selectedPlan(null);

                if (promos && promos.length === 1) {
                    self.installments(promos[0].value);
                    model.selectedPlan(promos[0]);
                }
            });

            // When installment selection changes, update selectedPlan on the model
            this.installments.subscribe(function (qty) {
                if (!qty) {
                    model.selectedPlan(null);
                    return;
                }
                let plans = self.availableInstallments() || [];
                let plan  = _.find(plans, function (p) { return p.value == qty; }) || null;
                model.selectedPlan(plan);
            });

            // Set credit card number to credit card data object
            this.creditCardNumber.subscribe(_.debounce(function (value) {
                value = value.replace(/\s/g, '');

                // If value is empty or less than 15 digits reset
                if (!value || value.length < 15) {
                    self.selectedCardType(null);
                    self.selectedCardBrand(null);
                    self.creditCardType(null);
                    self.creditCardBrand(null);
                    self.creditCardExpYear(null);
                    self.creditCardExpMonth(null);
                    self.availableInstallments(false);
                    self.selectedMerchantNumber(false);
                    self.installments(null);

                    return false;
                }

                // stop subscription if it's already running
                if (this.loadingPromotions) return;

                // if we already loaded promotions, then clean up
                if ((!value.length || value.length < 15) && self.availableInstallments().length) {
                    self.availableInstallments(false);
                    self.installments(0);
                }

                // if card didn't reach 15 digits, wait for more input
                if (value.length < 15) {
                    return;
                }

                $('body').trigger('processStart');

                // retrieve bin number from field value (first 6 digits)
                let bin = value.substr(0, 6);

                // retrieve promotions
                getPromotionsByBinAction(config, bin, false)
                    .done(function (response) {


                        // BIN not found (case: error 500) - won't allow anything
                        if (!response.result.promotions) {
                            self.publishErrorMessage(
                                $.mage.__('Credit Card not recognized, please try with another one')
                            );
                            $('body').trigger('processStop');
                            return;
                        }

                        // If no Promotions came, then block the Payment form
                        if (response && response.errors) {
                            self.publishErrorMessage(
                                $.mage.__('No Promotions available, review your configuration or contact support')
                            );
                            self.hasLoadingErrors(true);
                            self.isPlaceOrderActionAllowed(false);
                            $('body').trigger('processStop');
                            return;
                        }

                        // we've a matching by the BIN endpoint
                        if (response.result.promotions.length) {
                            model.loadPromotions(response.result);
                            $('body').trigger('processStop');
                        } else {
                            // No promotions for this specific brand-bank
                            // pull promotions for all Card Brands
                            getPromotionsAction(config, response.result.cardBrand, false)
                                .done(function (response) {
                                    // return response.result;
                                    model.loadPromotions(response.result);
                                    $('body').trigger('processStop');
                                });
                        }
                    });
            }, 800));

            // Set expiration year to credit card data object
            this.creditCardExpYear.subscribe(function (value) {
                creditCardData.expirationYear = value;
            });

            // Set expiration month to credit card data object
            this.creditCardExpMonth.subscribe(function (value) {
                creditCardData.expirationMonth = value;
            });

            // Set cvv code to credit card data object
            this.creditCardVerificationNumber.subscribe(function (value) {
                creditCardData.cvvCode = value;
            });

            this.creditCardMethod.subscribe(function (value) {
                self.creditCardNumber('');
                self.creditCardExpYear('');
                self.creditCardExpMonth('');
                self.creditCardVerificationNumber('');

                if (value === 'DEBIT') {
                    self.isInstallmentsVisible(false);
                    self.installments('1');
                } else if (value !== typeof 'undefined' && value !== '') {
                    self.isInstallmentsVisible(true);
                }
            });
        },

        /**
         * @return {string}
         */
        getCode: function () {
            return config.getCode();
        },

        /**
         * @return {Object}
         */
        getData: function () {
            let data = {
                'method': this.item.method,
                'additional_data': {
                    'cardholder_name': this.creditCardHolderName(),
                    'cardholder_doc_number': this.creditCardDocumentNumber(),
                    'cardholder_doc_type': this.creditCardDocumentType(),
                    'credit_card_number': this.creditCardNumber(),
                    'credit_card_type': this.creditCardBrand(),
                    'credit_card_exp_year': this.creditCardExpYear(),
                    'credit_card_exp_month': this.creditCardExpMonth(),
                    'credit_card_cvv': this.creditCardVerificationNumber(),
                    'cc_method': this.creditCardMethod(),
                    'credit_card_method': this.creditCardType(),
                    'installments': this.installments(),
                    'merchant_number': this.selectedMerchantNumber()
                    // `installment_rate` is intentionally not sent. The rate decides how much is
                    // charged, so the server looks it up from the promotions service at
                    // authorization time and ignores anything the browser proposes.
                }
            };

            return data;
        },

        /**
         * @return {boolean}
         */
        isActive: function () {
            return config.getIsActive();
        },

        /**
         * @return {Object}
         */
        getCcAvailableTypes: function () {
            return config.getCcAvailableTypes();
        },

        /**
         * @return {Object}
         */
        getCcMethods: function () {
            return config.getCcMethods();
        },

        /**
         * @param {string} type
         * @return {boolean}
         */
        getIcons: function (type) {
            return config.getIcons(type);
        },

        /**
         * @return {Object}
         */
        getCcMonths: function () {
            return config.getCcMonths();
        },

        /**
         * @return {Object}
         */
        getCcYears: function () {
            return config.getCcYears();
        },

        /**
         * @return {boolean}
         */
        hasVerification: function () {
            return config.hasVerification();
        },

        /**
         * @return {*}
         */
        getDocumentTypes: function () {
            return config.getDocumentTypes();
        },

        /**
         * @return {string}
         */
        getCvvImageUrl: function () {
            return config.getCvvImageUrl();
        },

        /**
         * @return {string}
         */
        getCvvImageHtml: function () {
            return '<img src="' + this.getCvvImageUrl() +
                '" alt="' + $.mage.__('Card Verification Number Visual Reference') +
                '" title="' + $.mage.__('Card Verification Number Visual Reference') +
                '" />';
        },

        /**
         * @return {Object}
         */
        getCcMonthsValues: function () {
            return _.map(this.getCcMonths(), function (value, key) {
                return {
                    'value': key,
                    'month': value
                };
            });
        },

        /**
         * @return {Object}
         */
        getCcYearsValues: function () {
            return _.map(this.getCcYears(), function (value, key) {
                return {
                    'value': key,
                    'year': value
                };
            });
        },

        /**
         * @return {*}
         */
        getCcDocumentTypes: function () {
            return _.map(this.getDocumentTypes(), function (value, key) {
                return {
                    'value': key,
                    'type': value
                }
            });
        },

        /**
         * @return {Object}
         */
        getCcAvailableTypesValues: function () {
            let map = _.map(this.getCcAvailableTypes(), function (value, key) {
                return {
                    'value': key,
                    'type': value
                };
            });

            return map;
        },

        /**
         * @return {boolean}
         */
        isShowLegend: function () {
            return config.isShowLegend();
        },

        /**
         * @param {string} code
         * @return {string}
         */
        getCcTypeTitleByCode: function (code) {
            if (!code) return '';

            let title = '',
                keyValue = 'value',
                keyType = 'type';

            _.each(this.getCcAvailableTypesValues(), function (value) {
                if (value[keyValue] === code) {
                    title = value[keyType];
                }
            });

            return title;
        },

        /**
         * Prepare credit card number to output
         * @param {String} number
         * @returns {String}
         */
        formatDisplayCcNumber: function (number) {
            if (!number || !number.length) return '';

            return 'xxxx-' + number.substring(number.length - 4);
        },

        /**
         * Get credit card details
         * @returns {Array}
         */
        getInfo: function () {
            let creditCardType = this.getCcTypeTitleByCode(this.creditCardBrand() + this.creditCardType()),
                creditCardNumber = this.formatDisplayCcNumber(this.creditCardNumber());

            if (!creditCardType || !creditCardNumber) return [];

            return [
                { 'name': $.mage.__('Credit Card Type'), value: creditCardType },
                { 'name': $.mage.__('Credit Card Number'), value: creditCardNumber }
            ];
        },

        /**
         * @returns bool
         */
        validate: function () {
            let $form = $('#' + this.getCode() + '-form');
            return $form.validation() && $form.validation('isValid');
        },

        /**
         * @param {string} message Message to be added into the list
         */
        publishErrorMessage: function (message) {
            messageList.addErrorMessage({
                message: message
            });
        }
    });
});
