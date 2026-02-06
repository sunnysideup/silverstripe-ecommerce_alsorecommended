<?php

namespace Sunnysideup\EcommerceAlsoRecommended\Modifiers;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ArrayList;
use Sunnysideup\Ecommerce\Model\OrderModifier;
use Sunnysideup\Ecommerce\Pages\Product;
use Sunnysideup\EcommerceAlsoRecommended\Forms\RecommendedProductsModifierForm;

/**
 * Class \Sunnysideup\EcommerceAlsoRecommended\Modifiers\RecommendedProductsModifier
 *
 */
class RecommendedProductsModifier extends OrderModifier
{
    //--------------------------------------------------------------------  *** static functions
    // ######################################## *** form functions (e. g. Showform and getform)

    protected $recommendedBuyables;

    //--------------------------------------------------------------------  *** static variables

    private static $singular_name = 'Recommended Product';

    private static $plural_name = 'Recommended Products';

    public function i18n_singular_name()
    {
        return _t('RecommendedProductsModifier.SINGULAR_NAME', 'Recommended Product');
    }

    public function i18n_plural_name()
    {
        return _t('RecommendedProductsModifier.PLURAL_NAME', 'Recommended Products');
    }

    /**
     * standard Modifier Method.
     */
    public function ShowForm(): bool
    {
        if (! $this->recommendedBuyables) {
            $this->recommendedBuyables = new ArrayList();
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
                                if (!in_array($codeOfRecommendedProduct, $inCartIDArray, true) && ($recommendedProduct->canPurchase() && $recommendedProduct->AllowPurchase)) {
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
    public function ShowFormInEditableOrderTable(): bool
    {
        return false;
    }

    public function getModifierForm(Controller $optionalController = null, Validator $optionalValidator = null): ?RecommendedProductsModifierForm
    {
        if ($this->ShowForm()) {
            return new RecommendedProductsModifierForm(
                $optionalController,
                'RecommendedProducts',
                FieldList::create(),
                FieldList::create(),
                $optionalValidator,
                $this->recommendedBuyables
            );
        }

        return null;
    }

    //-------------------------------------------------------------------- *** display functions
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
    protected function LiveCalculatedTotal()
    {
        return 0;
    }

    protected function LiveTableValue()
    {
        return 0;
    }

    //-------------------------------------------------------------------- *** table titles
    protected function LiveName()
    {
        return $this->i18n_singular_name();
    }

    //-------------------------------------------------------------------- ***  database functions
}
