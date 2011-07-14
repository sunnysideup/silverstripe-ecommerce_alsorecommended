<?php



class EcommerceAlsoRecommendedDOD extends DataObjectDecorator {

	public function extraStatics() {
		return array (
			'many_many' => array(
				'EcommerceRecommendedProducts' => 'Product'
			),
			'belongs_many_many' => array(
				'RecommendedFor' => 'Product'
			),
			
		);
	}

	function updateCMSFields(FieldSet &$fields) {
		if($this->owner instanceOf Product) {
			$fields->addFieldToTab('Root.Content.RecommendedProducts', new TreeMultiselectField ("EcommerceRecommendedProducts", "Recommended Products", $sourceObject = "SiteTree", $keyField = "ID", $labelField = "Title"));
		}
	}


}
