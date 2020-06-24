2020-06-24 01:40

# running php upgrade upgrade see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_alsorecommended
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code upgrade /var/www/upgrades/ecommerce_alsorecommended/ecommerce_alsorecommended  --root-dir=/var/www/upgrades/ecommerce_alsorecommended --write -vvv
Writing changes for 5 files
Running upgrades on "/var/www/upgrades/ecommerce_alsorecommended/ecommerce_alsorecommended"
[2020-06-24 13:40:04] Applying RenameClasses to EcommerceAlsorecommendedTest.php...
[2020-06-24 13:40:04] Applying ClassToTraitRule to EcommerceAlsorecommendedTest.php...
[2020-06-24 13:40:04] Applying UpdateConfigClasses to config.yml...
[2020-06-24 13:40:04] Applying RenameClasses to RecommendedProductsModifier_Form.php...
[2020-06-24 13:40:04] Applying ClassToTraitRule to RecommendedProductsModifier_Form.php...
[2020-06-24 13:40:04] Applying RenameClasses to RecommendedProductsModifier.php...
[2020-06-24 13:40:04] Applying ClassToTraitRule to RecommendedProductsModifier.php...
[2020-06-24 13:40:04] Applying RenameClasses to EcommerceAlsoRecommendedDOD.php...
[2020-06-24 13:40:04] Applying ClassToTraitRule to EcommerceAlsoRecommendedDOD.php...
[2020-06-24 13:40:04] Applying RenameClasses to _config.php...
[2020-06-24 13:40:04] Applying ClassToTraitRule to _config.php...
modified:	tests/EcommerceAlsorecommendedTest.php
@@ -1,4 +1,6 @@
 <?php
+
+use SilverStripe\Dev\SapphireTest;
 class EcommerceAlsorecommendedTest extends SapphireTest
 {
     protected $usesDatabase = false;

modified:	_config/config.yml
@@ -3,31 +3,16 @@
 Before: 'app/*'
 After: 'framework/*','cms/*','ecommerce/*'
 ---
-StoreAdmin:
+Sunnysideup\Ecommerce\Cms\StoreAdmin:
   managed_models:
     - PickUpOrDeliveryModifierOptions
-
-OrderModifierFormController:
+Sunnysideup\Ecommerce\Control\OrderModifierFormController:
   allowed_actions:
     - PickUpOrDeliveryModifier
-
-
 PickUpOrDeliveryModifierOptions:
   extensions:
     - DataObjectSorterDOD
+Sunnysideup\Ecommerce\Pages\Product:
+  extensions:
+    - Sunnysideup\EcommerceAlsoRecommended\Model\EcommerceAlsoRecommendedDOD

-
-Product:
-  extensions:
-    - EcommerceAlsoRecommendedDOD
-
-OrderModifierFormController:
-  allowed_actions:
-    - RecommendedProducts
-
-
-
-
-# do not forget to add the RecommendedProductsModifier to the array of modifers, in case you want to use it.
-
-

modified:	src/Forms/RecommendedProductsModifier_Form.php
@@ -2,20 +2,37 @@

 namespace Sunnysideup\EcommerceAlsoRecommended\Forms;

-use OrderModifierForm;
-use FieldList;
-use HeaderField;
-use Config;
-use ArrayData;
-use CheckboxField;
-use LiteralField;
-use Convert;
-use EcommerceCurrency;
-use CompositeField;
-use FormAction;
-use Requirements;
-use ShoppingCart;
-use Controller;
+
+
+
+
+
+
+
+
+
+
+
+
+
+
+use Sunnysideup\Ecommerce\Model\ProductOrderItem;
+use SilverStripe\Forms\FieldList;
+use SilverStripe\Forms\HeaderField;
+use SilverStripe\Core\Config\Config;
+use Sunnysideup\EcommerceAlsoRecommended\Forms\RecommendedProductsModifier_Form;
+use SilverStripe\Forms\CheckboxField;
+use SilverStripe\View\ArrayData;
+use SilverStripe\Forms\LiteralField;
+use SilverStripe\Core\Convert;
+use Sunnysideup\Ecommerce\Model\Money\EcommerceCurrency;
+use SilverStripe\Forms\CompositeField;
+use SilverStripe\Forms\FormAction;
+use SilverStripe\View\Requirements;
+use Sunnysideup\Ecommerce\Api\ShoppingCart;
+use SilverStripe\Control\Controller;
+use Sunnysideup\Ecommerce\Forms\OrderModifierForm;
+


 /**
@@ -36,7 +53,7 @@

     private static $add_button_text = "Add Selected Items";

-    private static $order_item_classname = "ProductOrderItem";
+    private static $order_item_classname = ProductOrderItem::class;

     private static $product_template = "";

@@ -48,7 +65,7 @@
         $fields->push(HeaderField::create($this->config()->get("something_recommended_text")));
         $productFieldList = new FieldList();
         foreach ($recommendedBuyables as $buyable) {
-            $template = Config::inst()->get("RecommendedProductsModifier_Form", "product_template");
+            $template = Config::inst()->get(RecommendedProductsModifier_Form::class, "product_template");
             if ($template) {
                 $checkboxID = $buyable->ClassName."|".$buyable->ID;
                 $arrayData = new ArrayData(

Warnings for src/Forms/RecommendedProductsModifier_Form.php:
 - src/Forms/RecommendedProductsModifier_Form.php:150 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 150

modified:	src/Modifiers/RecommendedProductsModifier.php
@@ -2,13 +2,21 @@

 namespace Sunnysideup\EcommerceAlsoRecommended\Modifiers;

-use OrderModifier;
-use ArrayList;
-use Product;
-use Controller;
-use Validator;
-use RecommendedProductsModifier_Form;
-use FieldList;
+
+
+
+
+
+
+
+use SilverStripe\ORM\ArrayList;
+use Sunnysideup\Ecommerce\Pages\Product;
+use SilverStripe\Control\Controller;
+use SilverStripe\Forms\Validator;
+use SilverStripe\Forms\FieldList;
+use Sunnysideup\EcommerceAlsoRecommended\Forms\RecommendedProductsModifier_Form;
+use Sunnysideup\Ecommerce\Model\OrderModifier;
+


 /**

modified:	src/Model/EcommerceAlsoRecommendedDOD.php
@@ -2,11 +2,18 @@

 namespace Sunnysideup\EcommerceAlsoRecommended\Model;

-use DataExtension;
-use FieldList;
-use Product;
-use GridField;
-use GridFieldBasicPageRelationConfig;
+
+
+
+
+
+use Sunnysideup\Ecommerce\Pages\Product;
+use SilverStripe\Forms\FieldList;
+use Sunnysideup\Ecommerce\Forms\Gridfield\Configs\GridFieldBasicPageRelationConfig;
+use SilverStripe\Forms\GridField\GridField;
+use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
+use SilverStripe\ORM\DataExtension;
+



@@ -37,11 +44,11 @@
     private static $table_name = 'EcommerceAlsoRecommendedDOD';

     private static $many_many = array(
-        'EcommerceRecommendedProducts' => 'Product'
+        'EcommerceRecommendedProducts' => Product::class
     );

     private static $belongs_many_many = array(
-        'RecommendedFor' => 'Product'
+        'RecommendedFor' => Product::class
     );

     public function updateCMSFields(FieldList $fields)
@@ -56,7 +63,7 @@
                     $config = GridFieldBasicPageRelationConfig::create()
                 )
             );
-            $component = $config->getComponentByType('GridFieldAddExistingAutocompleter');
+            $component = $config->getComponentByType(GridFieldAddExistingAutocompleter::class);
             $component->setSearchFields(array("InternalItemID", "Title"));

             $fields->addFieldToTab(
@@ -68,7 +75,7 @@
                     $config = GridFieldBasicPageRelationConfig::create()
                 )
             );
-            $component = $config->getComponentByType('GridFieldAddExistingAutocompleter');
+            $component = $config->getComponentByType(GridFieldAddExistingAutocompleter::class);
             $component->setSearchFields(array("InternalItemID", "Title"));
         }
     }

Writing changes for 5 files
✔✔✔