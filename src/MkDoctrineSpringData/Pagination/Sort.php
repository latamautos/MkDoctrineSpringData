<?php
namespace MkDoctrineSpringData\Pagination;
use MkDoctrineSpringData\Pagination\Sorting\Order;
use MkDoctrineSpringData\Pagination\Sorting\Direction;
use PhpCommonUtil\Util\Assert;

class Sort implements \IteratorAggregate
{
    
    const DEFAULT_DIRECTION = Direction::ASC;
    
    /**
     * 
     * @var Order[]
     */
    private $orders;
    
    
    /**
     * Creates a new {@link Sort} instance using the given {@link Order}s.
     *
     * @param orders must not be {@literal null}.
     */
    public function __construct(Direction $direction = null, array $ordersOrProperties = array()) {
        
        $direction = null==$direction ? self::DEFAULT_DIRECTION : $direction;
        $direction = $direction instanceof  Direction ? $direction : Direction::$direction();
        Assert::notEmpty($ordersOrProperties, "You have to provide at least one sort property to sort by!");
        
        $this->orders = array();
        foreach($ordersOrProperties as $orderOrProp){
            if($orderOrProp instanceof  Order){
                $this->orders[] = $orderOrProp;
            }else{
                $this->orders[] = new Order($direction, $orderOrProp);
            }
        }
    }
    
    
    /**
     * Returns a new {@link Sort} consisting of the {@link Order}s of the current {@link Sort} combined with the given
     * ones.
     *
     * @param Sort sort can be {@literal null}.
     * @return Sort
     */
    public function andSort (Sort $sort) {
    
        if ($sort == null) {
            return this;
        }
    
        $these = new \ArrayObject($this->orders);   
        $these = $these->getArrayCopy();
    
        foreach ($sort as $order){
            $these[] =  $order;
        }
        return new Sort($these);
    }
    
    /**
     * Returns the order registered for the given property.
     *
     * @param property
     * @return Order
     */
    public function  getOrderFor($property) {
        
        foreach ($this->orders as $order){
            if($order->getProperty() == $property){
                return $order;
            }
        }
        return null;
    }
    
    
    public function getIterator() {
        return new \ArrayIterator($this->orders);
    }
    
    
}