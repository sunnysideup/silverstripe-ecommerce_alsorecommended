<?php

namespace Sunnysideup\EcommerceAlsoRecommended\Modifiers;

use Override;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Forms\Validation\Validator;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use Sunnysideup\Ecommerce\Model\OrderModifier;
use Sunnysideup\Ecommerce\Pages\Product;
use Sunnysideup\EcommerceAlsoRecommended\Forms\RecommendedProductsModifierForm;

/**
 * Class \Sunnysideup\EcommerceAlsoRecommended\Modifiers\RecommendedProductsModifier
 */
class RecommendedProductsModifier extends OrderModifier
{
    private static $table_name = 'RecommendedProductsModifier';

    //--------------------------------------------------------------------  *** static functions
    // ######################################## *** form functions (e. g. Showform and getform)

    protected $recommendedBuyables;

    //--------------------------------------------------------------------  *** static variables

    private static $singular_name = 'Recommended Product';

    private static $plural_name = 'Recommended Products';

    #[Override]
    public function i18n_singular_name()
    {
        return _t('RecommendedProductsModifier.SINGULAR_NAME', 'Recommended Product');
    }

    #[Override]
    public function plural_name()
    {
        return _t('RecommendedProductsModifier.PLURAL_NAME', 'Recommended Products');
    }

    /**
     * standard Modifier Method.
     */
    #[Override]
    public function ShowForm(): bool
    {
        if (! $this->recommendedBuyables) {
            $this->recommendedBuyables = ArrayList::create();
            $inCartIDArray = [];
            $order = $this->getOrderCached();
            if ($order && $order->getTotalItems()) {
                $items = $order->Items();
                foreach ($items as $item) {
                    $buyable = $item->getBuyableCached();
                    if ($buyable instanceof Product) {
                        $codeOfBuyable = $buyable->ClassName . '.' . $buyable->ID;
                        $inCartIDArray[$codeOfBuyable] = $codeOfBuyable;
                    }
                }

                foreach ($items as $item) {
                    //get recommended products
                    if ($item) {
                        $buyable = $item->getBuyableCached();
                        if ($buyable instanceof Product) {
                            unset($recommendedProducts);
                            $recommendedProducts = $buyable->EcommerceRecommendedProducts();
                            foreach ($recommendedProducts as $recommendedProduct) {
                                $codeOfRecommendedProduct = $recommendedProduct->ClassName . '.' . $recommendedProduct->ID;
                                if (! in_array($codeOfRecommendedProduct, $inCartIDArray, true) && ($recommendedProduct->canPurchase() && $recommendedProduct->AllowPurchase)) {
                                    $this->recommendedBuyables->push($recommendedProduct);
                                }
                            }
                        }
                    }
                }
            }
        }

        return (bool) $this->recommendedBuyables->exists();
    }

    /**
     * Should the form be included in the editable form
     * on the checkout page?
     */
    #[Override]
    public function ShowFormInEditableOrderTable(): bool
    {
        return false;
    }

    #[Override]
    public function getModifierForm(Controller $optionalController = null, Validator $optionalValidator = null): ?RecommendedProductsModifierForm
    {
        if ($this->ShowForm()) {
            return RecommendedProductsModifierForm::create($optionalController, 'RecommendedProducts', FieldList::create(), FieldList::create(), $optionalValidator, $this->recommendedBuyables);
        }

        return null;
    }

    //-------------------------------------------------------------------- *** display functions
    #[Override]
    public function ShowInTable(): bool
    {
        return false;
    }

    public function CanRemove(): bool
    {
        return false;
    }

    public function Name()
    {
        if (! $this->canEdit()) {
            return $this->Name;
        }

        return $this->LiveName();
    }

    // -------------------------------------------------------------------- *** table values
    #[Override]
    protected function LiveCalculatedTotal()
    {
        return 0;
    }

    #[Override]
    protected function LiveTableValue()
    {
        return 0;
    }

    //-------------------------------------------------------------------- *** table titles
    #[Override]
    protected function LiveName()
    {
        return $this->i18n_singular_name();
    }

    //-------------------------------------------------------------------- ***  database functions
}
