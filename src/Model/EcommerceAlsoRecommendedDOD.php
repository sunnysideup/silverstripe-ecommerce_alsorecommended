<?php

namespace Sunnysideup\EcommerceAlsoRecommended\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\Versioned\GridFieldArchiveAction;
use Sunnysideup\Ecommerce\Api\ArrayMethods;
use Sunnysideup\Ecommerce\Config\EcommerceConfig;
use Sunnysideup\Ecommerce\Forms\Gridfield\Configs\GridFieldConfigForProducts;
use Sunnysideup\Ecommerce\Pages\Product;

/**
 * Class \Sunnysideup\EcommerceAlsoRecommended\Model\EcommerceAlsoRecommendedDOD
 *
 * @property \Sunnysideup\Ecommerce\Pages\Product|\Sunnysideup\EcommerceAlsoRecommended\Model\EcommerceAlsoRecommendedDOD $owner
 * @method \SilverStripe\ORM\ManyManyList|\Sunnysideup\Ecommerce\Pages\Product[] EcommerceRecommendedProducts()
 * @method \SilverStripe\ORM\ManyManyList|\Sunnysideup\Ecommerce\Pages\Product[] RecommendedFor()
 */
class EcommerceAlsoRecommendedDOD extends DataExtension
{
    private static $many_many = [
        'EcommerceRecommendedProducts' => Product::class,
    ];

    private static $belongs_many_many = [
        'RecommendedFor' => Product::class,
    ];

    private static $max_number_of_recommended_products = 20;

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->getOwner();
        if ($owner instanceof Product && $owner->isInDB()) {
            $fields->addFieldsToTab(
                'Root.Recommend',
                [
                    GridField::create(
                        'EcommerceRecommendedProducts',
                        'Also Recommended Products',
                        $owner->EcommerceRecommendedProducts(),
                        GridFieldConfigForProducts::create()
                            ->removeComponentsByType(GridFieldArchiveAction::class)
                    ),
                    GridField::create(
                        'RecommendedFor',
                        'Recommended For',
                        $owner->RecommendedFor(),
                        GridFieldConfigForProducts::create()
                            ->removeComponentsByType(GridFieldArchiveAction::class)
                    ),
                ]
            );
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
        $owner = $this->getOwner();
        $list = $owner->EcommerceRecommendedProducts()
            ->sort(['PopularityRank' => 'ASC'])
            ->limit($this->owner->config()->get('max_number_of_recommended_products'));

        return $this->addAllowPurchaseFilter($list);
    }

    /**
     * only returns the products that are for sale
     * if only those need to be showing.
     *
     * @return \SilverStripe\ORM\DataList
     */
    public function RecommendedForForSale()
    {
        $owner = $this->getOwner();
        $list = $owner->RecommendedFor()
            ->sort(['PopularityRank' => 'ASC'])
            ->exclude(['ID' => ArrayMethods::filter_array($owner->EcommerceRecommendedProducts()->columnUnique())])
            ->limit($this->owner->config()->get('max_number_of_recommended_products'));

        return $this->addAllowPurchaseFilter($list);
    }

    protected function addAllowPurchaseFilter(DataList $list)
    {
        if (EcommerceConfig::inst()->OnlyShowProductsThatCanBePurchased) {
            $list = $list->filter(['AllowPurchase' => 1]);
        }

        return $list;
    }
}
