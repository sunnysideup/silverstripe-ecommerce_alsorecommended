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
			$fields->addFieldToTab('Root.Links', $recProGrid1 = new GridField('EcommerceRecommendedProducts', 'Also Recommended Products', $this->owner->EcommerceRecommendedProducts(), GridFieldConfig_RelationEditor::create()));
			$fields->addFieldToTab('Root.Links', $recProGrid2 = new GridField('RecommendedFor', 'Recommended For', $this->owner->RecommendedFor(), GridFieldConfig_RelationEditor::create()));
			$recProGrid1->getConfig()
				->removeComponentsByType("GridFieldEditButton")
				->removeComponentsByType("GridFieldAddNewButton")
				->addComponent(new GridFieldEditButtonOriginalPage());
			$recProGrid2->getConfig()
				->removeComponentsByType("GridFieldEditButton")
				->removeComponentsByType("GridFieldAddNewButton")
				->addComponent(new GridFieldEditButtonOriginalPage());
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
