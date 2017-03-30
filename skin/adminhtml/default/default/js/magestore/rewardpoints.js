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

/********** Backend - JS using for checkout **********/
var rewardSliderRules;
var currentRuleOptions = {};
var currentPointUsed = 0;
var currentRuleUsed = '';
var disableRewardAjax = true;
var rewardSlider;
var uniqueAjax;

function changeRewardSalesRule(el) {
    var ruleId = el.value;
    if (ruleId) {
        currentRuleOptions = rewardSliderRules[ruleId];
        switch (currentRuleOptions.optionType) {
            case 'needPoint':
                showRewardInfo('rewardpoints-needmore-msg');
                break;
            case 'slider':
                showRewardInfo('rewardpoints-slider-container');
                rewardSlider.applyOptions(currentRuleOptions.sliderOption);
                break;
        }
    } else {
        showRewardInfo('');
    }
}

function changePointCallback(points) {
    if (points == rewardSlider.maxPoints) {
        $('reward_max_points_used').checked = true;
    } else {
        $('reward_max_points_used').checked = false;
    }
    if (currentPointUsed == rewardSlider.slider.value
        && currentRuleUsed == $('reward_sales_rule').value
    ) {
        return false;
    }
    currentPointUsed = rewardSlider.slider.value;
    currentRuleUsed = $('reward_sales_rule').value;
    if (disableRewardAjax) {
        disableRewardAjax = false;
        return false;
    }
    var elements = $('cart-rewards-form').select('input, select, textarea');
    elements.push($$('[name="form_key"]')[0]);
    var params = Form.serializeElements(elements);
    uniqueAjax.NewRequest({
        method: 'post',
        postBody: params,
        parameters: params,
        onException: function() {
            window.location.reload();
        },
        onComplete: function(xhr) {
            if (xhr.responseText.isJSON()) {
                if (order) {
                    order.loadArea(['shipping_method', 'totals', 'billing_method'], true, {});
                }
            }
        }
    });
}

function showRewardInfo(elId) {
    var elIds = ['rewardpoints-needmore-msg', 'rewardpoints-slider-container'];
    for (var i = 0; i < 2; i++){
        if (elIds[i] == elId) {
            $(elId).show();
        } else {
            $(elIds[i]).hide();
        }
    }
}

function checkUseSalesRule(el, url) {
    var ruleId = el.value;
    var params = 'rule_id=' + ruleId + '&is_used=';
    if (el.checked) {
        params += '1&form_key=';
    } else {
        params += '0&form_key=';
    }
    params += $$('[name="form_key"]')[0].value;
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
        onException: function() {
            window.location.reload();
        },
        onComplete: function() {
            if (order) {
                order.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, {});
            }
        }
    });
}
