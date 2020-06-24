<?php




/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD:  extends DataExtension (ignore case)
  * NEW:  extends DataExtension (COMPLEX)
  * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
class EcommerceAlsoRecommendedDOD extends DataExtension
{

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * OLD: private static $many_many = (case sensitive)
  * NEW: 
    private static $table_name = '[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]';

    private static $many_many = (COMPLEX)
  * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    
    private static $table_name = 'EcommerceAlsoRecommendedDOD';

    private static $many_many = array(
        'EcommerceRecommendedProducts' => 'Product'
    );

    private static $belongs_many_many = array(
        'RecommendedFor' => 'Product'
    );

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner instanceof Product) {
            $fields->addFieldToTab(
                'Root.Links',
                GridField::create(
                    'EcommerceRecommendedProducts',
                    'Also Recommended Products',
                    $this->owner->EcommerceRecommendedProducts(),
                    $config = GridFieldBasicPageRelationConfig::create()
                )
            );
            $component = $config->getComponentByType('GridFieldAddExistingAutocompleter');
            $component->setSearchFields(array("InternalItemID", "Title"));

            $fields->addFieldToTab(
                'Root.Links',
                GridField::create(
                    'RecommendedFor',
                    'Recommended For',
                    $this->owner->RecommendedFor(),
                    $config = GridFieldBasicPageRelationConfig::create()
                )
            );
            $component = $config->getComponentByType('GridFieldAddExistingAutocompleter');
            $component->setSearchFields(array("InternalItemID", "Title"));
        }
    }

    /**
     *
     * small cleanup
     */
    public function onAfterWrite()
    {
        $products = $this->owner->EcommerceRecommendedProducts();
        if ($products->count()) {
            foreach ($products as $product) {
                if (!$product instanceof Product) {
                    $products->remove($product);
                } elseif (!$product->AllowPurchase) {
                    $products->remove($product);
                }
            }
        }
        $products = $this->owner->RecommendedFor();
        if ($products->count()) {
            foreach ($products as $product) {
                if (!$product instanceof Product) {
                    $products->remove($product);
                } elseif (!$product->AllowPurchase) {
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
    public function EcommerceRecommendedProductsForSale()
    {
        if ($this->owner->EcomConfig()->OnlyShowProductsThatCanBePurchased) {
            return $this->owner->EcommerceRecommendedProducts()->filter(array("AllowPurchase" => 1));
        } else {
            return $this->owner->EcommerceRecommendedProducts();
        }
    }

    /**
     * only returns the products that are for sale
     * if only those need to be showing.
     * @return DataList
     */
    public function RecommendedForForSale()
    {
        if ($this->owner->EcomConfig()->OnlyShowProductsThatCanBePurchased) {
            return $this->owner->RecommendedFor()->filter(array("AllowPurchase" => 1));
        } else {
            return $this->owner->RecommendedFor();
        }
    }
}

