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
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/********** Reward Points Price Product **********/
var RewardPointsPrice = Class.create();
RewardPointsPrice.prototype = {
    initialize: function (templateEl, listPrices, finalPrice, priceFormat) {
        this.templateEl = $(templateEl);
        this.listPrices = listPrices;
        
        this.generatedOldPrice = false;
        this.oldPrices = [];
        
        this.finalPrice = parseFloat(finalPrice);
        this.priceFormat = priceFormat;
        
        this.isShowed = false;
    },
    showPointPrices: function(points, ruleOption) {
        this.generateOldPrice();
        this.templateEl.down('.points').innerHTML = points;
        var discount = 0;
        if (typeof ruleOption.sliderOption == 'undefined') {
            return false;
        }
        var pointStep = parseInt(ruleOption.sliderOption.pointStep);
        if (pointStep < 1) {
            discount = parseFloat(ruleOption.stepDiscount);
        } else {
            var timesDiscount = Math.floor(points / pointStep);
            discount = parseFloat(ruleOption.stepDiscount) * timesDiscount;
        }
        //Hai.Tran 13/11/2013
        maxDiscount = parseFloat(ruleOption.maxDiscount);
        if(maxDiscount > 0 && discount > maxDiscount){
            discount = maxDiscount;
        }
        //End Hai.Tran 13/11/2013
        var price = this.finalPrice - discount;
        if (this.finalPrice < discount) {
            price = 0;
        }
        this.templateEl.down('.price .price').innerHTML = formatCurrency(price, this.priceFormat);
        for (var i = 0; i < this.listPrices.length; i++) {
            var priceEl = this.listPrices[i];
            var oldPrice = this.oldPrices[i];
            priceEl.innerHTML = this.templateEl.innerHTML;
            if (priceEl.className == 'regular-price'
                || priceEl.className == 'full-product-price'
            ) {
                oldPrice.show();
            }
        }
        this.isShowed = true;
        return true;
    },
    clearPrices: function () {
        if (this.isShowed == false) {
            return false;
        }
        if (this.generatedOldPrice == false) {
            return false;
        }
        for (var i = 0; i < this.listPrices.length; i++) {
            var priceEl = this.listPrices[i];
            var oldPrice = this.oldPrices[i];
            priceEl.innerHTML = oldPrice.innerHTML;
            oldPrice.hide();
        }
        this.isShowed = false;
        return true;
    },
    generateOldPrice: function() {
        if (this.generatedOldPrice) {
            return false;
        }
        var oldPrices = [];
        for (var i = 0; i < this.listPrices.length; i++) {
            var priceEl = this.listPrices[i];
            var parentEl = Element.extend(priceEl.parentNode);
            var oldPrice = Element.clone(priceEl, 1);
            oldPrice.addClassName('old-price');
            parentEl.insertBefore(oldPrice, priceEl);
            oldPrice.hide();
            oldPrices.push(oldPrice);
        }
        this.oldPrices = oldPrices;
        this.generatedOldPrice = true;
        return true;
    }
}
/********** Reward Points Slider **********/
var RewardPointsRuleSlider = Class.create();
RewardPointsRuleSlider.prototype = {
    initialize: function(pointEl, trackEl, handleEl, zoomOutEl, zoomInEl, pointLbl, itemId) {
        this.pointEl = $(pointEl);
        this.trackEl = $(trackEl);
        this.handleEl = $(handleEl);
        this.pointLbl = $(pointLbl);
        this.itemId = itemId;
        
        this.minPoints = 0;
        this.maxPoints = 1;
        this.pointStep = 1;
        
        this.slider = new Control.Slider(this.handleEl, this.trackEl, {
            axis:'horizontal',
            range: $R(this.minPoints, this.maxPoints),
            values: this.availableValue(),
            onSlide: this.changePoint.bind(this),
            onChange: this.changePoint.bind(this)
        });
        this.changePointCallback = function(v, id){};
        
        Event.observe($(zoomOutEl), 'click', this.zoomOut.bind(this));
        Event.observe($(zoomInEl), 'click', this.zoomIn.bind(this));
    },
    availableValue: function() {
        var values = [];
        for (var i = this.minPoints; i <= this.maxPoints; i += this.pointStep) {
            values.push(i);
        }
        return values;
    },
    applyOptions: function(options) {
        this.minPoints = options.minPoints || this.minPoints;
        this.maxPoints = options.maxPoints || this.maxPoints;
        this.pointStep = options.pointStep || this.pointStep;
        
        this.slider.range = $R(this.minPoints, this.maxPoints);
        this.slider.allowedValues = this.availableValue();
        
        this.manualChange(this.slider.value);
    },
    changePoint: function(points) {
        this.pointEl.value = points;
        if (this.pointLbl) {
            this.pointLbl.innerHTML = points;
        }
        if (typeof this.changePointCallback == 'function') {
            this.changePointCallback(points, this.itemId);
        }
    },
    zoomOut: function() {
        var curVal = this.slider.value - this.pointStep;
        if (curVal >= this.minPoints) {
            this.slider.value = curVal;
            this.slider.setValue(curVal);
            this.changePoint(curVal);
        }
    },
    zoomIn: function() {
        var curVal = this.slider.value + this.pointStep;
        if (curVal <= this.maxPoints) {
            this.slider.value = curVal;
            this.slider.setValue(curVal);
            this.changePoint(curVal);
        }
    },
    manualChange: function(points) {
        points = this.slider.getNearestValue(parseInt(points));
        this.slider.value = points;
        this.slider.setValue(points);
        this.changePoint(points);
    },
    changeUseMaxpoint: function(event) {
        var checkEl = event.element();
        if (checkEl.checked) {
            this.manualChange(this.maxPoints);
        } else {
            this.manualChange(0);
        }
    },
    changeUseMaxpointEvent: function(checkEl) {
        Event.observe($(checkEl), 'click', this.changeUseMaxpoint.bind(this));
    },
    manualChangePoint: function(event) {
        var changeEl = event.element();
        this.manualChange(changeEl.value);
    },
    manualChangePointEvent: function(changeEl) {
        Event.observe($(changeEl), 'change', this.manualChangePoint.bind(this));
    }
}
/********** Reward Points Product **********/
var WebposRewardPointsItem = Class.create();
WebposRewardPointsItem.prototype = {
    initialize: function(itemId, rewardProductRules, convertPrice, jsonEncode) {      
        this.currentRuleOptions = null;        
        
        this.itemId = itemId;
        this.spendPopup = new Window({windowClassName:'webpos-dialog-item-rule', title:'Spend Points',zIndex:100, width:404, height:229, minimizable:false,maximizable:false,showEffectOptions:{duration:0.4},hideEffectOptions:{duration:0.4}, resizable:false, destroyOnClose: true});
        this.spendPopupId = this.spendPopup.getId();
        
        $('webpos-spend-points'+this.itemId).show();
        $('rewardpoints-slider-container'+this.itemId).show();
        this.rewardSlider = new RewardPointsRuleSlider(
                                'reward_product_point'+this.itemId,
                                'rewardpoints-track'+this.itemId,
                                'rewardpoints-handle'+this.itemId,
                                'rewardpoints-slider-zoom-out'+this.itemId,
                                'rewardpoints-slider-zoom-in'+this.itemId,
                                'rewardpoints-slider-label'+this.itemId,
                                this.itemId
                            );
        this.rewardSlider.changePointCallback = changePointCallback;
        $('webpos-spend-points'+this.itemId).hide();
        $('rewardpoints-slider-container'+this.itemId).hide();
        
        this.setPrice(convertPrice, jsonEncode);
        
        this.rewardProductRules = rewardProductRules;
        this.changeRewardProductRule($('reward_product_rule'+this.itemId));
    },
    setPrice: function(convertPrice, jsonEncode){
        if(this.rewardPrice) this.rewardPrice.clearPrices();
        else this.rewardPrice = new RewardPointsPrice(
                'rewardpoints-price-template'+this.itemId, 
                $$('.webpos-price-box'+this.itemId+' .regular-price'), 
                convertPrice, 
                jsonEncode
            );
    },
    changeRewardProductRule: function(el) {
        var ruleId = el.value;       
        this.rewardPrice.clearPrices();
        if (ruleId) {
            this.currentRuleOptions = this.rewardProductRules[ruleId];
            switch (this.currentRuleOptions.optionType) {
                case 'login':
                    this.showRewardInfo('rewardpoints-login-msg'+this.itemId);
                    break;
                case 'needPoint':
                    this.showRewardInfo('rewardpoints-needmore-msg'+this.itemId);
                    $('rewardpoints-needmore-points'+this.itemId).innerHTML = this.currentRuleOptions.needPoint;
                    break;
                case 'slider':
                    this.showRewardInfo('rewardpoints-slider-container'+this.itemId);
                    this.rewardSlider.applyOptions(this.currentRuleOptions.sliderOption);
                    break;
                case 'static':
                    $('reward_product_point'+this.itemId).value = this.currentRuleOptions.sliderOption.minPoints;
                    this.rewardPrice.showPointPrices(this.currentRuleOptions.sliderOption.pointStep, this.currentRuleOptions);
                    this.showRewardInfo('');
                    break;
            }
        } else {
            this.showRewardInfo('');
        }
    },
    changePointCallback: function(points) {
        this.rewardPrice.showPointPrices(points, this.currentRuleOptions);
    },
    showRewardInfo: function(elId) {
        var elIds = ['rewardpoints-login-msg'+this.itemId, 'rewardpoints-needmore-msg'+this.itemId, 'rewardpoints-slider-container'+this.itemId];
        for (var i = 0; i < 3; i++){
            if (elIds[i] == elId) {
                $(elId).show();
            } else {
                $(elIds[i]).hide();
            }
        }
    },
    getSpendBox: function(content){ 
        this.spendPopup.setContent(content);
        this.spendPopup.showCenter(true);
    }
}



