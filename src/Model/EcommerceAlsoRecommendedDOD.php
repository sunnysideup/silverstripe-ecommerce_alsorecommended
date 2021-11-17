<?php

namespace Sunnysideup\EcommerceAlsoRecommended\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataExtension;
use Sunnysideup\Ecommerce\Config\EcommerceConfig;
use Sunnysideup\Ecommerce\Forms\Gridfield\Configs\GridFieldBasicPageRelationConfig;
use Sunnysideup\Ecommerce\Pages\Product;

use Sunnysideup\Vardump\Vardump;

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
                    $this->getOwner()->EcommerceRecommendedProducts(),
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
                    $this->getOwner()->RecommendedFor(),
                    $config = GridFieldBasicPageRelationConfig::create()
                )
            );
            $component = $config->getComponentByType(GridFieldAddExistingAutocompleter::class);
            $component->setSearchFields(['InternalItemID', 'Title']);
        }
    }

    /**
     * small cleanup.
     */
    public function onAfterWrite()
    {
        $products = $this->getOwner()->EcommerceRecommendedProducts();
        if ($products->exists()) {
            foreach ($products as $product) {
                if (! $product instanceof Product) {
                    $products->remove($product);
                } elseif (! $product->AllowPurchase) {
                    $products->remove($product);
                }
            }
        }
        $products = $this->getOwner()->RecommendedFor();
        if ($products->exists()) {
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
     *
     * @return \SilverStripe\ORM\DataList
     */
    public function EcommerceRecommendedProductsForSale()
    {
        $list = $this->getOwner()->EcommerceRecommendedProducts();
        $list = $this->addAllowPurchaseFilter($list);

        return $list;
    }

    /**
     * only returns the products that are for sale
     * if only those need to be showing.
     *
     * @return \SilverStripe\ORM\DataList
     */
    public function RecommendedForForSale()
    {
        $list = $this->getOwner()->RecommendedFor();
        $list = $this->addAllowPurchaseFilter($list);

        return $list;
    }

    protected function addAllowPurchaseFilter(DataList $list)
    {
        if (EcommerceConfig::inst()->OnlyShowProductsThatCanBePurchased) {
            $list = $list->filter(['AllowPurchase' => 1]);
        }
        return $list;
    }

}
