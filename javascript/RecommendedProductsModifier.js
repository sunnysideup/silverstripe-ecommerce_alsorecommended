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

	loadingClass: "loading",
	formID: "RecommendedProductsModifier_Form_RecommendedProducts",

	formButtonID: "RecommendedProductsModifier_Form_RecommendedProducts_action_processOrder",

	classToAddIfNoBoxesTicked: "requiredTickBox",

	msgIfNoBoxIsTicked: "please select at least one product to add",

	anyBoxTicked: false,

	cartHolderSelector: "OrderInformationEditableOuter",

	itemsToBeHidden: "",

	willBeAddedToCartClass: "willBeAddedToCart",

	willNotBeAddedToCartClass: "willNotBeAddedToCart",

	init: function() {
		RecommendedProductsModifier.ajaxForm();
		jQuery("#" + RecommendedProductsModifier.formID + " .checkbox input").removeClass(RecommendedProductsModifier.classToAddIfNoBoxesTicked);
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
	},

	checkForTickedBoxes: function() {
		RecommendedProductsModifier.anyBoxTicked = false;
		RecommendedProductsModifier.itemsToBeHidden = "#NOTHINGHERE"
		jQuery("#" + RecommendedProductsModifier.formID + " .checkbox input").each(
			function() {
				if(jQuery(this).is(":checked")) {
					jQuery(this).attr("checked", "");
					RecommendedProductsModifier.anyBoxTicked = true;
					var id = jQuery(this).attr("name");
					RecommendedProductsModifier.itemsToBeHidden += ", #" + id;

				}
			}
		);
		if(true == RecommendedProductsModifier.anyBoxTicked) {
			return true;
		}
		else {
			alert(RecommendedProductsModifier.msgIfNoBoxIsTicked);
			jQuery("#" + RecommendedProductsModifier.formID + " .checkbox input").addClass(RecommendedProductsModifier.classToAddIfNoBoxesTicked);
			return false;
		}
	},


	ajaxForm: function() {
		var options = {
			beforeSubmit:  RecommendedProductsModifier.showRequest,  // pre-submit callback
			success: RecommendedProductsModifier.showResponse,  // post-submit callback
			dataType: "html",
			target: RecommendedProductsModifier.cartHolder
		};
		jQuery("#" + RecommendedProductsModifier.formID).ajaxForm(options);
	},

	// pre-submit callback
	showRequest: function (formData, jqForm, options) {
		if(RecommendedProductsModifier.checkForTickedBoxes()) {

			jQuery("#" + RecommendedProductsModifier.formID).addClass(RecommendedProductsModifier.loadingClass);
			jQuery("#" + RecommendedProductsModifier.cartHolderSelector).html("updating...");
			return true;
		}
		return false;
	},

	// post-submit callback
	showResponse: function (responseText, statusText)  {
		//redo quantity boxes
		//jQuery("#" + PickUpOrDeliveryModifier.updatedDivID).css("height", "auto");
		jQuery("#" + RecommendedProductsModifier.formID).removeClass(RecommendedProductsModifier.loadingClass);
		jQuery("#" + RecommendedProductsModifier.cartHolderSelector).html(responseText);
		//AjaxCheckout.setChanges(responseText);
		AjaxCheckout.redoCartAjax();
		AjaxCheckout.redoCountryQuery();
		jQuery(RecommendedProductsModifier.itemsToBeHidden).fadeOut();
	}


}
