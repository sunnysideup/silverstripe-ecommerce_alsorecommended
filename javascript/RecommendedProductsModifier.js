/**
* Note: can not do ajax query, because it changes the recommended products
* reload page if products have been added
*
*
**/

(function($){
	$(document).ready(
		function() {
			RecommendedProductsModifier.init();
		}
	);
})(jQuery);

var RecommendedProductsModifier = {

	formID: "RecommendedProductsModifier_Form_RecommendedProducts",

	willBeAddedToCartClass: "willBeAddedToCart",

	willNotBeAddedToCartClass: "willNotBeAddedToCart",

	init: function() {
		jQuery("#" + RecommendedProductsModifier.formID + " .checkbox input").addClass(RecommendedProductsModifier.willNotBeAddedToCartClass);
		RecommendedProductsModifier.changeOnTick();
	},

	changeOnTick: function() {
		jQuery("#" + RecommendedProductsModifier.formID + " .checkbox input").change(
			function() {
				if(jQuery(this).is(":checked")) {
					jQuery(this).parent().addClass(RecommendedProductsModifier.willBeAddedToCartClass);
					jQuery(this).parent().removeClass(RecommendedProductsModifier.willNotBeAddedToCartClass);
				}
				else {
					jQuery(this).parent().addClass(RecommendedProductsModifier.willNotBeAddedToCartClass);
					jQuery(this).parent().removeClass(RecommendedProductsModifier.willBeAddedToCartClass);
				}
			}
		);
	}

}
