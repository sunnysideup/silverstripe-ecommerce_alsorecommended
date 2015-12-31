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
            return new RecommendedProductsModifier_Form($optionalController, 'RecommendedProducts', null, null, $optionalValidator, $this->recommendedBuyables);
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


/**
 * 
 * you can set
 * RecommendedProductsModifier_Form:
 *   product_template: "bla"
 * 
 * in your configs to have a customised product display.
 * 
 * 
 */
class RecommendedProductsModifier_Form extends OrderModifierForm
{

    private static $image_width = 100;

    private static $something_recommended_text = "Recommended Additions";

    private static $add_button_text = "Add Selected Items";

    private static $order_item_classname = "Product_OrderItem";

    private static $product_template = "";

    public function __construct($optionalController = null, $name, FieldList $fields, FieldList $actions, $optionalValidator = null, $recommendedBuyables)
    {
        $fieldsArray = new FieldList(array(new HeaderField($this->config()->get("something_recommended_text"))));
        $productFieldList = new FieldList();
        foreach ($recommendedBuyables as $buyable) {
            $template = Config::inst()->get("RecommendedProductsModifier_Form", "product_template");
            if ($template) {
                $checkboxID = $buyable->ClassName."|".$buyable->ID;
                $arrayData = new ArrayData(
                    array(
                        "Buyable" => $buyable,
                        "CheckboxID" => $checkboxID,
                        "Checkbox" => new CheckboxField($checkboxID, _t("RecommendedProductsModifier_Form.ADD", "add"))
                    )
                );
                $productFieldList->push(new LiteralField("Buyable_".$buyable->ID, $arrayData->renderWith($template)));
            } else {
                //foreach product in cart get recommended products
                $imageID = $buyable->ImageID;
                $imagePart = '';
                if ($buyable && $buyable->ImageID > 0) {
                    $resizedImage = $buyable->Image()->SetWidth($this->Config()->get("image_width"));
                    if (is_object($resizedImage) && $resizedImage) {
                        $imageLink = $resizedImage->Filename;
                        $imagePart = '<span class="secondPart"><img src="'.$imageLink.'" alt="'.Convert::raw2att($buyable->Title).'" /></span>';
                    }
                }
                if (!$imagePart) {
                    $imagePart = '<span class="secondPart noImage">[no image available for '.$buyable->Title.']</span>';
                }
                $priceAsMoney = EcommerceCurrency::get_money_object_from_order_currency($buyable->calculatedPrice());
                $pricePart = '<span class="firstPart">'.$priceAsMoney->NiceLongSymbol().'</span>';
                $title = '<a href="'.$buyable->Link().'">'.$buyable->Title.'</a>'.$pricePart.$imagePart.'';
                $newField = new CheckboxField($buyable->ClassName."|".$buyable->ID, $title);
                $fieldsArray->push($newField);
            }
        }
        $fieldsArray->push(new CompositeField($productFieldList));
        $actions = new FieldList(new FormAction('processOrderModifier', $this->config()->get("add_button_text")));
        // 6) Form construction
        parent::__construct($optionalController, $name, $fieldsArray, $actions, $optionalValidator);
        Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
        //Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
        //Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
        Requirements::javascript("ecommerce_alsorecommended/javascript/RecommendedProductsModifier.js");
        Requirements::themedCSS("RecommendedProductsModifier", "ecommerce_alsrecommended");
    }

    public function processOrderModifier($data, $form)
    {
        $count = 0;
        $error = 0;
        foreach ($data as $key => $value) {
            if ($value == 1) {
                list($className, $id) = explode("|", $key);
                if (class_exists($className) && intval($id) == $id) {
                    $buyable = $className::get()->byID($id);
                    if ($buyable && $buyable->canPurchase()) {
                        $count++;
                        ShoppingCart::singleton()->addBuyable($buyable);
                    } else {
                        $error++;
                    }
                } else {
                    $error++;
                }
            }
        }
        if ($error) {
            ShoppingCart::singleton()->addMessage(_t("RecommendedProductsModifier_Form.ERROR_UPDATING", "There was an error updating the cart", "bad"));
        } elseif ($count) {
            ShoppingCart::singleton()->addMessage(_t("RecommendedProductsModifier_Form.CART_UPDATED", "Cart updated (".$count.")", "good"));
        } else {
            ShoppingCart::singleton()->addMessage(_t("RecommendedProductsModifier_Form.NOTHING_TO_ADD", "Nothing to add", "warning"));
        }
        Controller::curr()->redirectBack();
    }


//-------------------------------------------------------------------- *** debug
}
