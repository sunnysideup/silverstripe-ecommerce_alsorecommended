<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: ecommerce_modifiers
 * @description: shows a list of recommended products
 * the product page / dataobject need to have a function RecommendedProductsForCart
 * which returns an array of IDs
 */
class RecommendedProductsModifier extends OrderModifier
{

//--------------------------------------------------------------------  *** static variables

    private static $singular_name = "Recommended Product";
    public function i18n_singular_name()
    {
        return _t("RecommendedProductsModifier.SINGULAR_NAME", "Recommended Product");
    }

    private static $plural_name = "Recommended Products";
    public function i18n_plural_name()
    {
        return _t("RecommendedProductsModifier.PLURAL_NAME", "Recommended Products");
    }

//--------------------------------------------------------------------  *** static functions
// ######################################## *** form functions (e. g. Showform and getform)


    protected $recommendedBuyables = null;

    /**
     * standard Modifier Method
     * @return Boolean
     */
    public function ShowForm()
    {
        if (!$this->recommendedBuyables) {
            $this->recommendedBuyables = new ArrayList();
            $inCartIDArray = array();
            if ($items = $this->Order()->Items()) {
                foreach ($items as $item) {
                    $buyable = $item->Buyable();
                    if ($buyable instanceof Product) {
                        $codeOfBuyable = $buyable->ClassName.".".$buyable->ID;
                        $inCartIDArray[$codeOfBuyable] = $codeOfBuyable;
                    }
                }
                foreach ($items as $item) {
                    //get recommended products
                    if ($item) {
                        $buyable = $item->Buyable();
                        if ($buyable instanceof Product) {
                            unset($recommendedProducts);
                            $recommendedProducts = $buyable->EcommerceRecommendedProducts();
                            foreach ($recommendedProducts as $recommendedProduct) {
                                $codeOfRecommendedProduct = $recommendedProduct->ClassName.".".$recommendedProduct->ID;
                                if (!in_array($codeOfRecommendedProduct, $inCartIDArray)) {
                                    $this->recommendedBuyables->push($recommendedProduct);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->recommendedBuyables->count();
    }

    /**
     * Should the form be included in the editable form
     * on the checkout page?
     * @return Boolean
     */
    public function ShowFormInEditableOrderTable()
    {
        return false;
    }

    /**
     *
     * @return Form
     */
    public function getModifierForm(Controller $optionalController = null, Validator $optionalValidator = null)
    {
        if ($this->ShowForm()) {
            return new RecommendedProductsModifier_Form(
                $optionalController,
                'RecommendedProducts',
                FieldList::create(),
                FieldList::create(),
                $optionalValidator,
                $this->recommendedBuyables
            );
        }
    }

//-------------------------------------------------------------------- *** display functions
    public function ShowInTable()
    {
        return false;
    }

    public function CanRemove()
    {
        return false;
    }


// -------------------------------------------------------------------- *** table values
    public function LiveCalculatedTotal()
    {
        return 0;
    }
    public function LiveTableValue()
    {
        return 0;
    }

//-------------------------------------------------------------------- *** table titles
    public function LiveName()
    {
        return $this->i18n_singular_name();
    }

    public function Name()
    {
        if (!$this->canEdit()) {
            return $this->Name;
        } else {
            return $this->LiveName();
        }
    }

//-------------------------------------------------------------------- ***  database functions
}
