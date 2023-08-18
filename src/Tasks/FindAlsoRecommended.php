<?php

namespace Sunnysideup\EcommerceAlsoRecommended\Tasks;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Member;
use Sunnysideup\Ecommerce\Config\EcommerceConfig;
use Sunnysideup\Ecommerce\Model\Address\BillingAddress;
use Sunnysideup\Ecommerce\Model\Address\ShippingAddress;
use Sunnysideup\Ecommerce\Model\Order;
use Sunnysideup\Ecommerce\Model\OrderAttribute;
use Sunnysideup\Ecommerce\Model\Process\OrderEmailRecord;
use Sunnysideup\Ecommerce\Model\Process\OrderStatusLog;
use Sunnysideup\Ecommerce\Model\Process\OrderStep;
use Sunnysideup\Ecommerce\Pages\Product;

/**
 * @description: cleans up old (abandonned) carts...
 *
 * @author: Nicolaas [at] Sunny Side Up .co.nz
 * @package: ecommerce
 * @sub-package: tasks
 */
class FindAlsoRecommended extends BuildTask
{
    protected $verbose = false;
    protected $verboseMainDetailsOnly = true;
    protected $dryRun = true;

    protected $title = 'Find also recommended products';

    protected $description = 'Uses the order history to find products that are often bought together.';

    private static $segment = 'FindAlsoRecommended';

    private static $min_percentage_to_be_included = 0.1;
    private static $min_absolute_count_to_be_included = 2;

    /**
     * run in verbose mode.
     */
    public static function run_on_demand()
    {
        $obj = new self();
        $obj->verbose = true;
        $obj->run(null);
    }

    /**
     * runs the task without output.
     */
    public function runSilently()
    {
        $this->verbose = false;

        $this->run(null);
    }

    public function run($request)
    {
        if($this->verbose) {
            $this->verboseMainDetailsOnly = true;
        }
        $products = Product::get();
        $report = [];
        if(! $this->dryRun) {
            DB::query('DELETE FROM Product_EcommerceRecommendedProducts WHERE AutomaticallyAdded = 1 ');
        }
        foreach($products as $product) {
            $links = $this->findAlsoRecommended($product);
            foreach($links as $name => $productArray) {
                if($this->verbose) {
                    DB::alteration_message('DOING '.$product->Title, 'created');
                }
                foreach(array_keys($productArray) as $productId) {
                    if(! $this->dryRun) {
                        $product->$name()->add($productId, ['AutomaticallyAdded' => 1]);
                    }
                    $title = DB::query('SELECT Title FROM SiteTree WHERE ID = '.$productId)->value();
                    $internalItemID = DB::query('SELECT InternalItemID FROM Product WHERE ID = '.$productId)->value();
                    if($this->verboseMainDetailsOnly) {
                        if($name === 'EcommerceRecommendedProducts') {
                            $accessory = $product->Title . '('.$product->InternalItemID.')';
                            $mainProduct = $title . '('.$internalItemID.')';
                        } else {
                            $mainProduct = $product->Title . '('.$product->InternalItemID.')';
                            $accessory = $title . '('.$internalItemID.')';
                        }
                        DB::alteration_message('... Adding '.$accessory.') as accessory to '.$mainProduct, 'created');
                    }
                    if(! isset($report[$mainProduct])) {
                        $report[$mainProduct] = [];
                    }
                    $report[$mainProduct][] = $accessory;
                }
            }

        }
        if($this->verboseMainDetailsOnly) {
            foreach($report as $mainProduct => $accessories) {
                DB::alteration_message($mainProduct);
                $accessories = array_unique($accessories);
                foreach($accessories as $accessory) {
                    DB::alteration_message('...'.$accessory);
                }
            }
        }
    }

    protected function findAlsoRecommended($product): array
    {
        $links = [];
        $orderIds = $this->getOrderIds($product);
        if(count($orderIds)) {
            $orders = Order::get()->filter(['ID' => $orderIds]);
            $totalCount = [];
            if($this->verbose) {
                DB::alteration_message('... ... Orders Found '.$orders->count());
            }
            foreach($orders as $order) {
                $orderItems = $order->Items();
                foreach($orderItems as $orderItem) {
                    $otherProduct = $orderItem->Product();
                    if($otherProduct && $otherProduct->exists() && $otherProduct->AllowPurchase) {
                        if($otherProduct->ID !== $product->ID) {
                            if($otherProduct->Price < $product->Price) {
                                $name = 'RecommendedFor';
                            } else {
                                $name = 'EcommerceRecommendedProducts';
                            }
                            if(! isset($links[$name][$otherProduct->ID])) {
                                $links[$name][$otherProduct->ID] = 0;
                            }
                            $links[$name][$otherProduct->ID]++;
                            if(! isset($totalCount[$name])) {
                                $totalCount[$name] = 0;
                            }
                            $totalCount[$name]++;
                        }
                    }
                }
            }
            if($this->verbose) {
                DB::alteration_message('... ... Total count of other products: '.array_sum($totalCount));
            }
            foreach($links as $name => $productArray) {
                foreach($productArray as $productId => $count) {
                    $totalCount = $totalCount[$name] ?? 1;
                    $percentage = $count / $totalCount;
                    $out1 = $percentage < Config::inst()->get(FindAlsoRecommended::class, 'min_percentage_to_be_included');
                    $out2 = $count < Config::inst()->get(FindAlsoRecommended::class, 'min_absolute_count_to_be_included');
                    if($out1 || $out2) {
                        unset($links[$name][$productId]);
                    }
                }
            }
        }
        return $links;
    }

    protected function getOrderIds($product): array
    {
        $orderIds = [];
        $orderItems = $product->SalesOrderItems();
        foreach($orderItems as $orderItem) {
            $orderIds[$orderItem->OrderID] = $orderItem->OrderID;
        }
        return $orderIds;
    }
}
