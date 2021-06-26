<?php

namespace Sunnysideup\EcommerceAlsoRecommended\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use Sunnysideup\Ecommerce\Api\ShoppingCart;
use Sunnysideup\Ecommerce\Forms\OrderModifierForm;
use Sunnysideup\Ecommerce\Model\Money\EcommerceCurrency;
use Sunnysideup\Ecommerce\Model\ProductOrderItem;

/**
 * you can set
 * RecommendedProductsModifierForm:
 *   product_template: "bla".
 *
 * in your configs to have a customised product display.
 */
class RecommendedProductsModifierForm extends OrderModifierForm
{
    private static $image_width = 100;

    private static $something_recommended_text = 'Recommended Additions';

    private static $add_button_text = 'Add Selected Items';

    private static $order_item_classname = ProductOrderItem::class;

    private static $product_template = '';

    public function __construct($optionalController, string $name, FieldList $fields, FieldList $actions, $optionalValidator = null, $recommendedBuyables = null)
    {
        if (! ($fields instanceof FieldList)) {
            $fields = FieldList::create();
        }
        $productFieldList = new FieldList();
        $recommendedBuyables = $recommendedBuyables->filter(['AllowPurchase' => 1]);
        if ($recommendedBuyables && $recommendedBuyables->count()) {
            $fields->push(HeaderField::create($this->config()->get('something_recommended_text')));
            foreach ($recommendedBuyables as $buyable) {
                $template = Config::inst()->get(RecommendedProductsModifierForm::class, 'product_template');
                if ($template) {
                    $checkboxID = $buyable->ClassName . '|' . $buyable->ID;
                    $arrayData = new ArrayData(
                        [
                            'Buyable' => $buyable,
                            'CheckboxID' => $checkboxID,
                            'Checkbox' => new CheckboxField($checkboxID, _t('RecommendedProductsModifierForm.ADD', 'add')),
                        ]
                    );
                    $productFieldList->push(new LiteralField('Buyable_' . $buyable->ID, $arrayData->RenderWith($template)));
                } else {
                    //foreach product in cart get recommended products
                    $imagePart = '';
                    if ($buyable && $buyable->ImageID > 0) {
                        $resizedImage = $buyable->Image()->ScaleWidth($this->Config()->get('image_width'));
                        if (is_object($resizedImage) && $resizedImage) {
                            $imageLink = $resizedImage->Filename;
                            $imagePart = '<span class="secondPart"><img src="' . $imageLink . '" alt="' . Convert::raw2att($buyable->Title) . '" /></span>';
                        }
                    }
                    if (! $imagePart) {
                        $imagePart = '<span class="secondPart noImage">[no image available for ' . $buyable->Title . ']</span>';
                    }
                    $priceAsMoney = EcommerceCurrency::get_money_object_from_order_currency($buyable->calculatedPrice());
                    $pricePart = '<span class="firstPart">' . $priceAsMoney->NiceLongSymbol() . '</span>';
                    $title = '<a href="' . $buyable->Link() . '">' . $buyable->Title . '</a>' . $pricePart . $imagePart . '';
                    $newField = new CheckboxField(
                        $buyable->ClassName . '|' . $buyable->ID,
                        DBField::create_field(
                            'HTMLText',
                            $title
                        )
                    );
                    $fields->push($newField);
                }
            }
        }
        $fields->push(CompositeField::create($productFieldList)->setName('Products'));
        if (! $actions instanceof FieldList) {
            $actions = FieldList::create();
        }
        $actions->push(FormAction::create('processOrderModifier', $this->config()->get('add_button_text')));
        // 6) Form construction
        parent::__construct($optionalController, $name, $fields, $actions, $optionalValidator);
        Requirements::javascript('silverstripe/admin: thirdparty/jquery/jquery.js');
        //Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
        //Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
        Requirements::javascript('sunnysideup/ecommerce_alsorecommended: client/javascript/RecommendedProductsModifier.js');
        Requirements::themedCSS('client/css/RecommendedProductsModifier');
    }

    public function processOrderModifier($data, $form)
    {
        $count = 0;
        $error = 0;
        foreach ($data as $key => $value) {
            if (1 === $value) {
                list($className, $id) = explode('|', $key);

                if (class_exists($className) && (int) $id === $id) {
                    $buyable = $className::get()->byID($id);
                    if ($buyable && $buyable->canPurchase()) {
                        ++$count;
                        ShoppingCart::singleton()->addBuyable($buyable);
                    } else {
                        ++$error;
                    }
                } else {
                    ++$error;
                }
            }
        }
        if ($error) {
            ShoppingCart::singleton()->addMessage(_t('RecommendedProductsModifierForm.ERROR_UPDATING', 'There was an error updating the cart', 'bad'));
        } elseif ($count) {
            ShoppingCart::singleton()->addMessage(_t('RecommendedProductsModifierForm.CART_UPDATED', 'Cart updated (' . $count . ')', 'good'));
        } else {
            ShoppingCart::singleton()->addMessage(_t('RecommendedProductsModifierForm.NOTHING_TO_ADD', 'Nothing to add', 'warning'));
        }
        Controller::curr()->redirectBack();
    }
}
