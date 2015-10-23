<?php
namespace MkDoctrineSpringData\Pagination;
use MkDoctrineSpringData\Pagination\Sorting\Order;
use MkDoctrineSpringData\Pagination\Sorting\Direction;
use PhpCommonUtil\Util\Assert;
use MkDoctrineSpringData\Aggregator\PageableOrSort;
use MkDoctrineSpringData\Aggregator\DirectionOrSort;

class Sort implements \IteratorAggregate, PageableOrSort, DirectionOrSort
{
    
    const DEFAULT_DIRECTION = Direction::ASC;
    
    /**
     * 
     * @var Order[]
     */
    private $orders;

    /**
     * Creates a new {@link Sort} instance
     * 
     * @param Direction $defaultDirection            
     * @param Order[]|string[] ...$ordersOrProperties            
     * @param boolean $initial            
     */
    public function __construct(Direction $defaultDirection = null, ...$ordersOrProperties)
    {
        $defaultDirection = null === $defaultDirection ? Direction::ASC() : $defaultDirection;
        Assert::notEmpty($ordersOrProperties, "You have to provide at least one sort property to sort by!");
        $this->orders = array();
        foreach($ordersOrProperties as $orderOrProp){
            if($orderOrProp instanceof  Order){
                $this->orders[] = $orderOrProp;
            }else{
                $this->orders[] = new Order($defaultDirection, $orderOrProp);
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
    
        $this->orders = array_merge($this->orders , $sort->getIterator()->getArrayCopy());
        return $this;
        
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
        Assert::notEmpty($this->orders, "This Sort is not initialized yet.");
        return new \ArrayIterator($this->orders);
    }
    
    
}