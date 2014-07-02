<?php



class EcommerceAlsoRecommendedDOD extends DataExtension {

	private static $many_many = array(
		'EcommerceRecommendedProducts' => 'Product'
	);

	private static $belongs_many_many = array(
		'RecommendedFor' => 'Product'
	);

	function updateCMSFields(FieldList $fields) {
		if($this->owner instanceOf Product) {
			if(!$this->owner->EcommerceRecommendedProducts()->count()) {
				$fields->addFieldToTab('Root.Links', $recProGrid = new GridField('RecommendedProducts', 'Recommended Products', $this->owner->RecommendedFor(), GridFieldConfig_RelationEditor::create()));
			}
			if(!$this->owner->RecommendedFor()->count()) {
				$fields->addFieldToTab('Root.Links', $recProGrid = new GridField('RecommendedProducts', 'Recommended Products', $this->owner->EcommerceRecommendedProducts(), GridFieldConfig_RelationEditor::create()));
			}
		}
	}

	/**
	 *
	 * small cleanup
	 */
	function onAfterWrite(){
		$products = $this->owner->EcommerceRecommendedProducts();
		if($products->count()) {
			foreach($products as $product) {
				if(!$product instanceOf Product) {
					$products->remove($product);
				}
				elseif(!$product->AllowPurchase) {
					$products->remove($product);
				}
			}
		}
		$products = $this->owner->RecommendedFor();
		if($products->count()) {
			foreach($products as $product) {
				if(!$product instanceOf Product) {
					$products->remove($product);
				}
				elseif(!$product->AllowPurchase) {
					$products->remove($product);
				}
			}
		}
	}

}
