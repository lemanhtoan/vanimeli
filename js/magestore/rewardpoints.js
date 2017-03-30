/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/********** Update JS Prototype - Fix for Magento 1.4 **********/
if (typeof Element.clone == 'undefined') {
    Element.clone = function (element, deep) {
        if (!(element = $(element)))
            return;
        var clone = element.cloneNode(deep);
        clone._prototypeUID = void 0;
        if (deep) {
            var descendants = Element.select(clone, '*'),
                    i = descendants.length;
            while (i--) {
                descendants[i]._prototypeUID = void 0;
            }
        }
        return Element.extend(clone);
    };
}

/********** Reward Points Slider **********/
var RewardPointsSlider = Class.create();
RewardPointsSlider.prototype = {
    initialize: function (pointEl, trackEl, handleEl, zoomOutEl, zoomInEl, pointLbl) {
        this.pointEl = $(pointEl);
        this.trackEl = $(trackEl);
        this.handleEl = $(handleEl);
        this.pointLbl = $(pointLbl);

        this.minPoints = 0;
        this.maxPoints = 1;
        this.pointStep = 1;

        this.slider = new Control.Slider(this.handleEl, this.trackEl, {
            axis: 'horizontal',
            range: $R(this.minPoints, this.maxPoints),
            values: this.availableValue(),
            onSlide: this.changePointOnSlide.bind(this),
            onChange: this.changePoint.bind(this)
        });
        this.changePointCallback = function (v) {
        };

        Event.observe($(zoomOutEl), 'click', this.zoomOut.bind(this));
        Event.observe($(zoomInEl), 'click', this.zoomIn.bind(this));
    },
    availableValue: function () {
        var values = [];
        for (var i = this.minPoints; i <= this.maxPoints; i += this.pointStep) {
            values.push(i);
        }
        return values;
    },
    applyOptions: function (options) {
        this.minPoints = options.minPoints || this.minPoints;
        this.maxPoints = options.maxPoints || this.maxPoints;
        this.pointStep = options.pointStep || this.pointStep;

        this.slider.range = $R(this.minPoints, this.maxPoints);
        this.slider.allowedValues = this.availableValue();

        this.manualChange(this.slider.value);
    },
    changePointOnSlide: function (points) {
        this.pointEl.value = points;
        if (this.pointLbl) {
            this.pointLbl.innerHTML = points;
        }
    },
    changePoint: function (points) {
        this.pointEl.value = points;
        if (this.pointLbl) {
            this.pointLbl.innerHTML = points;
        }
        if (typeof this.changePointCallback == 'function') {
            this.changePointCallback(points);
        }
    },
    zoomOut: function () {
        var curVal = this.slider.value - this.pointStep;
        if (curVal >= this.minPoints) {
            this.slider.value = curVal;
            this.slider.setValue(curVal);
            this.changePoint(curVal);
        }
    },
    zoomIn: function () {
        var curVal = this.slider.value + this.pointStep;
        if (curVal <= this.maxPoints) {
            this.slider.value = curVal;
            this.slider.setValue(curVal);
            this.changePoint(curVal);
        }
    },
    manualChange: function (points) {
        points = this.slider.getNearestValue(parseInt(points));
        this.slider.value = points;
        this.slider.setValue(points);
        this.changePoint(points);
    },
    changeUseMaxpoint: function (event) {
        var checkEl = event.element();
        if (checkEl.checked) {
            this.manualChange(this.maxPoints);
        } else {
            this.manualChange(0);
        }
    },
    changeUseMaxpointEvent: function (checkEl) {
        Event.observe($(checkEl), 'click', this.changeUseMaxpoint.bind(this));
    },
    manualChangePoint: function (event) {
        var changeEl = event.element();
        this.manualChange(changeEl.value);
    },
    manualChangePointEvent: function (changeEl) {
        Event.observe($(changeEl), 'change', this.manualChangePoint.bind(this));
    }
}

/********** Reward Points Unique Ajax Request **********/
var RewardPointsAjax = Class.create();
RewardPointsAjax.prototype = {
    initialize: function (url) {
        this.addUrl(url);
        this.isRunningRequest = false;
        this.hasWaitingRequest = false;
    },
    addUrl: function (url) {
        if (window.location.href.match('https://') && !url.match('https://')) {
            url = url.replace('http://', 'https://');
        }
        if (!window.location.href.match('https://') && url.match('https://')) {
            url = url.replace('https://', 'http://');
        }
        this.url = url;
    },
    Request: function (url, config) {
        this.addUrl(url);
        this.config = config;
        this.ReRequest();
    },
    NewRequest: function (config) {
        this.config = config;
        this.ReRequest();
    },
    ReRequest: function () {
        if (this.isRunningRequest) {
            this.hasWaitingRequest = true;
            return;
        }
        this.isRunningRequest = true;
        config = this.config;
        if (typeof this.config.beforeRequest == "function") {
            this.config.beforeRequest();
        }
        if (typeof this.config.onComplete == "function") {
            this.orgComplete = this.config.onComplete;
        } else {
            this.orgComplete = function (response) {
            };
        }
        config.onComplete = this.onComplete.bind(this);
        new Ajax.Request(this.url, config);
    },
    onComplete: function (response) {
        // this.orgComplete(response);
        this.isRunningRequest = false;
        if (this.hasWaitingRequest) {
            this.hasWaitingRequest = false;
            this.ReRequest();
        } else {
            this.orgComplete(response);
        }
    }
}

function checkOutLoadRewardpoints(json) {
    var eladd = getElementRewardpoints();
    eladd.insert({
        top: json.html
    });
    if ($('p_method_free')) {
        $('p_method_free').checked = true;
    }
}
function getElementRewardpoints() {
    //amasty checkout
    if($('co-payment-form-update'))
        return $('co-payment-form-update');
    if ($('checkout-payment-method-load').down('#checkout-payment-method-load') == undefined) {
        return $('checkout-payment-method-load');
    } else {
        return $('checkout-payment-method-load').down('#checkout-payment-method-load');
    }
}
function rewardpointsShowPaymentMethod(isShow) {
    var element = getElementRewardpoints();
    element.childElements().each(function (el) {
        if (!el.hasClassName('rewardpoints-sp-methods')) {
            if (isShow) {
                el.show();
                $('rewardpoints-mask').hide();
            } else {
                el.hide();
                $('rewardpoints-mask').show();
            }
        }
    });
}
function changeUsePointAjax(el) {
    var params = '';
    if (el.checked)
        params = 'use_point=1';
    changeUsePointAjaxRequest.NewRequest({
        method: 'post',
        postBody: params,
        parameters: params,
        beforeRequest: function () {
            $('cart-rewards-form').hide();
            rewardpointsShowPaymentMethod(false);
        },
        onException: function () {
            window.location.reload();
        },
        onComplete: function (xhr) {
            if (xhr.responseText.isJSON()) {
                var response = xhr.responseText.evalJSON();
                if (response.updatepayment) {
                    rewardpointsLoadTotal(1);
                } else {
                    if ($('rewardpoints_payment_method').checked)
                        $('cart-rewards-form').show();
                    rewardpointsShowPaymentMethod(true);
                    rewardpointsLoadTotal();
                }
            }
        }
    });
}
function changeUsePointAjaxOnepage(el) {
    var params = '';
    if (el.checked)
        params = 'use_point=1';
    changeUsePointAjaxRequest.NewRequest({
        method: 'post',
        postBody: params,
        parameters: params,
        beforeRequest: function () {
            $('cart-rewards-form').hide();
            rewardpointsShowPaymentMethod(false);
        },
        onException: function () {
            window.location.reload();
        },
        onComplete: function (xhr) {
            if (xhr.responseText.isJSON()) {
                var response = xhr.responseText.evalJSON();
                if (response.updatepayment) {
                    if (typeof shippingMethod != 'undefined' && typeof shippingMethod.save == 'function') {
                        shippingMethod.save();
                    } else if (typeof billing != 'undefined' && typeof billing.save == 'function') {
                        billing.save();
                    }
                } else {
                    if ($('rewardpoints_payment_method').checked)
                        $('cart-rewards-form').show();
                    rewardpointsShowPaymentMethod(true);
                }
            }
        }
    });
}
function checkUseSalesRule(el, url) {
    var ruleId = el.value;
    var params = 'rule_id=' + ruleId + '&is_used=';
    if (el.checked) {
        params += '1';
    } else {
        params += '0';
    }
    el.disabled = true;
    var label = $('reward_rule_label_' + ruleId);
    label.innerHTML = $('rule_refreshing_label').innerHTML;
    if (window.location.href.match('https://') && !url.match('https://')) {
        url = url.replace('http://', 'https://');
    }
    if (!window.location.href.match('https://') && url.match('https://')) {
        url = url.replace('https://', 'http://');
    }
    new Ajax.Request(url, {
        method: 'post',
        postBody: params,
        parameters: params,
        onException: function () {
            window.location.reload();
        },
        onComplete: function () {
            rewardpointsLoadTotal(1);
        }
    });
}
function rewardpointsLoadTotal(enable_update_payment) {
    if ($$('body.checkout-onepage-index')[0] && typeof updateReview != 'undefined' && typeof $('co-payment-form-update') != 'undefined') {
        //amasty checkout
        updateReview();
    } else if ($$('body.checkout-onepage-index')[0] && typeof shippingMethod != 'undefined' && typeof shippingMethod.save == 'function') {
        shippingMethod.save();
    } else if ($$('body.checkout-onepage-index')[0] && typeof billing != 'undefined' && typeof billing.save == 'function') {
        billing.save();
    } else if ($$('body.onestepcheckout-index-index')[0] && typeof save_shipping_method == 'function') {
        //Magestore Onestepcheckout
        save_shipping_method(shipping_method_url, enable_update_payment, true);
    } else if (typeof updateShippingMethod == 'function') {
        updateShippingMethod();
    } else if ($$('body.onepagecheckout-index-index')[0] && typeof checkout != 'undefined' && typeof checkout.update == 'function') {
        //DeivisonArthur Onepage checkout - Magento Brazil free
        checkout.update({
            'payment-method': enable_update_payment,
            'shipping-method': 0,
            'review': 1
        });
    } else if($$('body.gomage-checkout-onepage-index')[0] && typeof checkout != 'undefined' && typeof checkout.submit == 'function'){
        //Gomage light checkout
        checkout.submit({}, 'get_totals');
    } else if($$('body.opc-index-index')[0] && typeof IWD.OPC.savePayment == 'function'){
        //IWD onepage checkout
        if(!enable_update_payment) IWD.OPC.savePayment();
        else IWD.OPC.Shipping.save();
    } else if($$('body.firecheckout-index-index')[0] && typeof checkout != 'undefined' && typeof checkout.update == 'function'){
        //TM_FireCheckout
        checkout.update(checkout.urls.paymentdata, {
            'review': 1, 
            'payment-method': enable_update_payment
        });
    } else if($$('body.webpos-index-index')[0] && typeof reloadAllBlock != 'undefined'){
        //Magestore WebPOS
        reloadAllBlock();
    } else if(typeof get_separate_save_methods_function != 'undefined'){
        //Idev_OneStepCheckout
        get_separate_save_methods_function(url)();
    } else {
        window.location.reload();
    }
}
