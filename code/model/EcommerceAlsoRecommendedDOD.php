<?php



class EcommerceAlsoRecommendedDOD extends DataExtension {

	private static $many_many = array('EcommerceRecommendedProducts' => 'Product');

	private static $belongs_many_many = array('RecommendedFor' => 'Product');

	function updateCMSFields(FieldList $fields) {
		if($this->owner instanceOf Product) {
			$field = new TreeMultiselectField ("EcommerceRecommendedProducts", "Recommended Products", $sourceObject = "SiteTree", $keyField = "ID", $labelField = "Title");
			$filter = create_function('$obj', 'return ( ( $obj InstanceOf Product || $obj InstanceOf ProductGroup) && ($obj->ID != '.$this->owner->ID.'));');
			$field->setFilterFunction($filter);
			$fields->addFieldToTab('Root.RecommendedProducts', $field);
		}
	}

	/**
	 *
	 * small cleanup
	 */
	function onAfterWrite(){
		$products = $this->owner->EcommerceRecommendedProducts();
		if($products && $products->count()) {
			foreach($products as $product) {
				if(!$product instanceOf Product) {
					$products->remove($product);
				}
			}
		}
	}

}
