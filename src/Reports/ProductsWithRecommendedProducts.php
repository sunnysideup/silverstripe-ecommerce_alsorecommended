<?php

namespace Sunnysideup\Ecommerce\Reports;

use SilverStripe\Reports\Report;
use Sunnysideup\Ecommerce\Pages\Product;

/**
 * Selects all products without an image.
 *
 * @author: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: reports
 */
class ProductsWithRecommendedProducts extends Report
{
    use EcommerceProductReportTrait;

    protected $dataClass = Product::class;

    /**
     * @return int - for sorting reports
     */
    public function sort()
    {
        return 7001;
    }

    /**
     * @return string
     */
    public function title()
    {
        return 'E-commerce: Products: products with recommended products';
    }

    /**
     * @param mixed $list
     */
    protected function updateEcommerceList($list)
    {
        return $list
            ->where('Product_EcommerceRecommendedProducts.ID IS NOT NULL')
            ->sort('Title', 'ASC')
            ->leftJoin(
                'Product_EcommerceRecommendedProducts',
                '"Product"."ID" = Product_EcommerceRecommendedProducts.ProductID'
            )
        ;
    }
}
