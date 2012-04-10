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
			$field = new TreeMultiselectField ("EcommerceRecommendedProducts", "Recommended Products", $sourceObject = "SiteTree", $keyField = "ID", $labelField = "Title");
			$filter = create_function('$obj', 'return ( ( $obj InstanceOf Product) && ($obj->ID != '.$this->owner->ID.'));');
			$field->setFilterFunction($filter);
			$fields->addFieldToTab('Root.Content.RecommendedProducts', $field);
		}
	}


}
