<?php

namespace Sunnysideup\EcommerceAlsoRecommended\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\ORM\DataExtension;
use Sunnysideup\Ecommerce\Forms\Gridfield\Configs\GridFieldBasicPageRelationConfig;
use Sunnysideup\Ecommerce\Pages\Product;

class EcommerceAlsoRecommendedDOD extends DataExtension
{
    private static $many_many = [
        'EcommerceRecommendedProducts' => Product::class,
    ];

    private static $belongs_many_many = [
        'RecommendedFor' => Product::class,
    ];

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
            $component = $config->getComponentByType(GridFieldAddExistingAutocompleter::class);
            $component->setSearchFields(['InternalItemID', 'Title']);

            $fields->addFieldToTab(
                'Root.Links',
                GridField::create(
                    'RecommendedFor',
                    'Recommended For',
                    $this->owner->RecommendedFor(),
                    $config = GridFieldBasicPageRelationConfig::create()
                )
            );
            $component = $config->getComponentByType(GridFieldAddExistingAutocompleter::class);
            $component->setSearchFields(['InternalItemID', 'Title']);
        }
    }

    /**
     * small cleanup
     */
    public function onAfterWrite()
    {
        $products = $this->owner->EcommerceRecommendedProducts();
        if ($products->count()) {
            foreach ($products as $product) {
                if (! $product instanceof Product) {
                    $products->remove($product);
                } elseif (! $product->AllowPurchase) {
                    $products->remove($product);
                }
            }
        }
        $products = $this->owner->RecommendedFor();
        if ($products->count()) {
            foreach ($products as $product) {
                if (! $product instanceof Product) {
                    $products->remove($product);
                } elseif (! $product->AllowPurchase) {
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
            return $this->owner->EcommerceRecommendedProducts()->filter(['AllowPurchase' => 1]);
        }
        return $this->owner->EcommerceRecommendedProducts();
    }

    /**
     * only returns the products that are for sale
     * if only those need to be showing.
     * @return DataList
     */
    public function RecommendedForForSale()
    {
        if ($this->owner->EcomConfig()->OnlyShowProductsThatCanBePurchased) {
            return $this->owner->RecommendedFor()->filter(['AllowPurchase' => 1]);
        }
        return $this->owner->RecommendedFor();
    }
}
