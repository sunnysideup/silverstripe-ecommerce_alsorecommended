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
			$config = GridFieldConfig_RelationEditor::create();
			$config
				->removeComponentsByType("GridFieldEditButton")
				->removeComponentsByType("GridFieldAddNewButton")
				->addComponent(new GridFieldEditButtonOriginalPage());
			$fields->addFieldToTab('Root.Links', $recProGrid1 = new GridField('EcommerceRecommendedProducts', 'Also Recommended Products', $this->owner->EcommerceRecommendedProducts(), $config));
			$fields->addFieldToTab('Root.Links', $recProGrid2 = new GridField('RecommendedFor', 'Recommended For', $this->owner->RecommendedFor(), $config));
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

	/**
	 * only returns the products that are for sale
	 * if only those need to be showing.
	 * @return DataList
	 */
	public function EcommerceRecommendedProductsForSale() {
		if($this->owner->EcomConfig()->OnlyShowProductsThatCanBePurchased) {
			return $this->owner->EcommerceRecommendedProducts()->filter(array("AllowPurchase" => 1));
		}
		else {
			return $this->owner->EcommerceRecommendedProducts();
		}
	}

	/**
	 * only returns the products that are for sale
	 * if only those need to be showing.
	 * @return DataList
	 */
	public function RecommendedForForSale() {
		if($this->owner->EcomConfig()->OnlyShowProductsThatCanBePurchased) {
			return $this->owner->RecommendedFor()->filter(array("AllowPurchase" => 1));
		}
		else {
			return $this->owner->RecommendedFor();
		}
	}

}
