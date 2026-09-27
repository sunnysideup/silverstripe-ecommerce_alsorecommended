<?php

namespace Sunnysideup\EcommerceAlsoRecommended\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\GridFieldArchiveAction;
use Sunnysideup\Ecommerce\Api\ArrayMethods;
use Sunnysideup\Ecommerce\Config\EcommerceConfig;
use Sunnysideup\Ecommerce\Forms\Gridfield\Configs\GridFieldConfigForProducts;
use Sunnysideup\Ecommerce\Pages\Product;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

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

    private static $many_many_extraFields = [
        'EcommerceRecommendedProducts' => [
            'SortOrder' => 'Int',
        ],
    ];

    public function augmentDatabase(): void
    {

        // Default join table format is {OwnerClass}_{RelationName}
        DB::require_index(
            'Product_EcommerceRecommendedProducts',
            'SortOrder',
            [
                'type' => 'index',
                'columns' => ['SortOrder'],
            ]
        );
    }

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
                        'Also Recommended Products (if you buy this, also get ...)',
                        $owner->EcommerceRecommendedProducts(),
                        GridFieldConfigForProducts::create()
                            ->addComponent(new GridFieldSortableRows('SortOrder'))
                    ),
                    GridField::create(
                        'RecommendedFor',
                        'Recommended For (if you bought something else, we would recommend this)',
                        $owner->RecommendedFor(),
                        GridFieldConfigForProducts::create()
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

    public function EcommerceRecommendedProducts()
    {
        $owner = $this->getOwner();
        return $owner->getManyManyComponents('EcommerceRecommendedProducts')->sort('SortOrder');
    }

}
