<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: ecommerce_modifiers
 * @description: shows a list of recommended products
 * the product page / dataobject need to have a function RecommendedProductsForCart
 * which returns an array of IDs
 */
class RecommendedProductsModifier extends OrderModifier {

//--------------------------------------------------------------------  *** static variables
	protected static $image_width = 100;

	static $db = array();


	public static $singular_name = "Recommended Products";
		function i18n_singular_name() { return _t("RecommendedProductsModifier.RECOMMENDEDPRODUCTSMODIFIER", "Recommended Products");}

	public static $plural_name = "Recommended Products";
		function i18n_plural_name() { return _t("RecommendedProductsModifier.RECOMMENDEDPRODUCTSMODIFIER", "Recommended Products");}

//--------------------------------------------------------------------  *** static functions
	public function ShowForm() {
		return true;
	}

	static function get_form($controller) {
		return new RecommendedProductsModifier_Form($controller, 'RecommendedProducts');
	}

	static function set_image_width($v) {self::$image_width;}

	static function get_image_width() {return self::$image_width;}

//-------------------------------------------------------------------- *** display functions
	function ShowInTable() {
		return false;
	}

	function CanRemove() {
		return false;
	}


// -------------------------------------------------------------------- *** table values
	function LiveCalculatedTotal() {
		return 0;
	}
	function LiveTableValue() {
		return 0;
	}

//-------------------------------------------------------------------- *** table titles
	function LiveName() {
		return "Recommended Products";
	}

	function Name() {
		if(!$this->canEdit()) {
			return $this->Name;
		}
		else {
			return $this->LiveName();
		}
	}

//-------------------------------------------------------------------- ***  database functions

	public function IsNoChange() {
		return true;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
	}
}

class RecommendedProductsModifier_Form extends Form {

	protected  static $nothing_recommended_text = " ";

	protected  static $something_recommended_text = "Recommended Additions";

	protected  static $add_button_text = "Add Selected Items";

	protected static $order_item_classname = "Product_OrderItem";

	private static $site_currency = '';

	static function set_nothing_recommended_text($v) {self::$nothing_recommended_text = $v;}

	static function set_something_recommended_text($v) {self::$something_recommended_text = $v;}

	static function set_add_button_text($v) {self::$add_button_text = $v;}

	static function set_order_item_classname($v) {self::$order_item_classname = $v;}

	function __construct($controller, $name) {
		$InCartIDArray = array();
		$recommendedProductsIDArray = array();
		$fieldsArray = array();
		if($items = ShoppingCart::get_items()) {
			foreach($items as $item) {
				$id = $item->Product()->ID;
				$InCartIDArray[$id] = $id;
			}
			foreach($items as $item) {
				//get recommended products
				if($item) {
					$product = $item->Product();
					if($product) {
						unset($recommendedProducts);
						$recommendedProducts = array();
						$recommendedProducts = $product->EcommerceRecommendedProducts();
						foreach($recommendedProducts as $recommendedProduct) {
							if(!in_array($recommendedProduct->ID, $InCartIDArray)) {
								$recommendedProductsIDArray[$recommendedProduct->ID] = $recommendedProduct->ID;
							}
						}
					}
				}
			}
		}
		if(count($recommendedProductsIDArray)) {
			Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
			//Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
			//Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
			Requirements::javascript("ecommerce_alsorecommended/javascript/RecommendedProductsModifier.js");
			Requirements::themedCSS("RecommendedProductsModifier");
			$fieldsArray[] = new HeaderField(self::$something_recommended_text);
			foreach($recommendedProductsIDArray as $ID) {
				$product = DataObject::get_by_id("Product", $ID);
				//foreach product in cart get recommended products
				$imageID = $product->ImageID;
				$imagePart = '';
				if($product->ImageID > 0) {
					$resizedImage = $product->Image()->SetWidth(RecommendedProductsModifier::get_image_width());
					if(is_object($resizedImage) && $resizedImage) {
						$imageLink = $resizedImage->Filename;
						$imagePart = '<span class="secondPart"><img src="'.$imageLink.'" alt="'.Convert::raw2att($product->Title).'" /></span>';
					}
				}
				if(!$imagePart) {
					$imagePart = '<span class="secondPart noImage">[no image available for '.$product->Title.']</span>';
				}
				$pricePart = '<span class="firstPart">'.$this->getCurrency().$product->Price.'</span>';
				$title = '<a href="'.$product->Link().'">'.$product->Title.'</a>'.$pricePart.$imagePart.'';
				$newField = new CheckboxField($product->URLSegment, $title);
				$fieldsArray[] = $newField;
			}
			$actions = new FieldSet(new FormAction('processOrder', self::$add_button_text));
		}
		else {
			$fieldsArray[] = new HeaderField(self::$nothing_recommended_text);
			$actions = new FieldSet();
		}
		$requiredFields = null;
		// 3) Put all the fields in one FieldSet
		$fields = new FieldSet($fieldsArray);

		// 6) Form construction
		return parent::__construct($controller, $name, $fields, $actions, $requiredFields);
	}

	public function processOrder($data, $form) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$items = ShoppingCart::get_items();
		$URLSegments = array();
		foreach($data as $key => $value) {
			if(1 == $value) {
				$URLSegments[$key] = $key;
			}
		}
		if(is_array($URLSegments) && count($URLSegments)) {
			$itemsToAdd = DataObject::get("Product", "{$bt}URLSegment{$bt} IN ('".implode("','", $URLSegments)."')");
			if($itemsToAdd) {
				foreach($itemsToAdd as $item) {
					ShoppingCart::add_new_item(new self::$order_item_classname($item));
				}
			}
		}
		if(Director::is_ajax()) {
			return $this->controller->renderWith("AjaxCheckoutCart");
		}
		else {
			Director::redirect(CheckoutPage::find_link());
		}
		return;
	}

	private function getCurrency() {
		if(!self::$site_currency) {
			if(class_exists('Payment')) {
				self::$site_currency = Payment::site_currency();
			}
		}
		return self::$site_currency;
	}

//-------------------------------------------------------------------- *** debug

	function DebugMessage () {
		if(Director::isDev()) {return $this->debugMessage;}
	}
}
