2020-06-24 01:40

# running php upgrade inspect see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_alsorecommended
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code inspect /var/www/upgrades/ecommerce_alsorecommended/ecommerce_alsorecommended/src  --root-dir=/var/www/upgrades/ecommerce_alsorecommended --write -vvv
Writing changes for 1 files
Running post-upgrade on "/var/www/upgrades/ecommerce_alsorecommended/ecommerce_alsorecommended/src"
[2020-06-24 13:40:27] Applying ApiChangeWarningsRule to RecommendedProductsModifier_Form.php...
[2020-06-24 13:40:27] Applying UpdateVisibilityRule to RecommendedProductsModifier_Form.php...
[2020-06-24 13:40:27] Applying ApiChangeWarningsRule to RecommendedProductsModifier.php...
[2020-06-24 13:40:28] Applying UpdateVisibilityRule to RecommendedProductsModifier.php...
[2020-06-24 13:40:28] Applying ApiChangeWarningsRule to EcommerceAlsoRecommendedDOD.php...
[2020-06-24 13:40:28] Applying UpdateVisibilityRule to EcommerceAlsoRecommendedDOD.php...
modified:	Forms/RecommendedProductsModifier_Form.php
@@ -101,7 +101,7 @@
                 }
                 $priceAsMoney = EcommerceCurrency::get_money_object_from_order_currency($buyable->calculatedPrice());
                 $pricePart = '<span class="firstPart">'.$priceAsMoney->NiceLongSymbol().'</span>';
-                $title = '<a href="'.$buyable->Link().'">'.$buyable->Title.'</a>'.$pricePart.$imagePart.'';
+                $title = '<a href="'.$buyable->getRequestHandler()->Link().'">'.$buyable->Title.'</a>'.$pricePart.$imagePart.'';
                 $newField = new CheckboxField($buyable->ClassName."|".$buyable->ID, $title);
                 $fields->push($newField);
             }

Warnings for Forms/RecommendedProductsModifier_Form.php:
 - Forms/RecommendedProductsModifier_Form.php:65 SilverStripe\Forms\HeaderField: Requires an explicit $name constructor argument (in addition to $title)
 - Forms/RecommendedProductsModifier_Form.php:104 SilverStripe\Forms\Form->Link(): Moved to FormRequestHandler
Writing changes for 1 files
✔✔✔