define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils'
    ],
    function ($, Component, quote, totals, priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Avinash_FreeGift/checkout/summary/freegift_discount'
            },
            totals: quote.getTotals(),
            isDisplayedDiscountTotal: function () {
                return totals.getSegment('freegift_discount') && totals.getSegment('freegift_discount').value;
            },
            getDiscountTotal: function () {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('freegift_discount').value;
                }
                return this.getFormattedPrice(price);
            }
        });
    }
);