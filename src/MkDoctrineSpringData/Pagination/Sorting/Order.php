<?php

namespace  MkDoctrineSpringData\Pagination\Sorting;

use MkDoctrineSpringData\Pagination\Sort;
use PhpCommonUtil\Util\StringUtils;
use PhpCommonUtil\Util\Assert;
class Order
{
    
    const DEFAULT_DIRECTION = Direction::ASC;
    
    private static $DEFAULT_IGNORE_CASE = false;
    /**
     * 
     * @var Direction
     */
    private $direction;
    
    /**
     * 
     * @var string
     */
    private $property;
    
    /**
     * 
     * @var bool
     */
    private $ignoreCase;
    
    /**
     * 
     * @var NullHandling
     */
    private $nullHandling;
    
    /**
     * Creates a new {@link Order} instance. if order is {@literal null} then order defaults to
     * {@link Sort#DEFAULT_DIRECTION}
     *
     * @param Direction direction can be {@literal null}, will default to {@link Sort#DEFAULT_DIRECTION}
     * @param string property must not be {@literal null} or empty.
     */
    public function  __construct(Direction $direction = null, $property = 'id', $ignoreCase = null, NullHandling $nullHandling = null) {
        
        
        if (!StringUtils::hasText($property)){
            throw new \InvalidArgumentException("Property must not null or empty!");
        }
        
        $this->direction = $direction == null ? Direction::search(self::DEFAULT_DIRECTION) : ($direction instanceof Direction ? $direction : Direction::$direction() ) ;
        $this->property = $property;
        $this->ignoreCase = $ignoreCase == null ? self::$DEFAULT_IGNORE_CASE : (bool) $ignoreCase;
        $this->nullHandling = $nullHandling == null ? NullHandling::NATIVE() : ($nullHandling instanceof NullHandling ? $nullHandling : NullHandling::$nullHandling() );
    
        Assert::notNull($this->direction);
        Assert::notNull($this->ignoreCase);
        Assert::notNull($this->nullHandling);
    }
    
    
    /**
     * Returns the order the property shall be sorted for.
     *
     * @return Direction
     */
    public function  getDirection() {
        return $this->direction;
    }
    
    /**
     * Returns the property to order for.
     *
     * @return string
     */
    public function  getProperty() {
        return $this->property;
    }
    
    /**
     * Returns whether sorting for this property shall be ascending.
     *
     * @return boolean
     */
    public function isAscending() {
        return $this->direction->getValue() === Direction::ASC;
    }
    
    /**
     * Returns whether or not the sort will be case sensitive.
     *
     * @return boolean
     */
    public function isIgnoreCase() {
        return $this->ignoreCase;
    }
    
    /**
     * 
     * @param Direction|NullHandling  $directionOrNullHandling
     */
    public function with($directionOrNullHandling){
        if( $directionOrNullHandling instanceof Direction ){
            return $this->withDirection($directionOrNullHandling);
        }else if($directionOrNullHandling instanceof NullHandling){
            return $this->withNullHandling($directionOrNullHandling);
        }else{
            $given = is_object($directionOrNullHandling) ? get_class($directionOrNullHandling) : ( is_array($directionOrNullHandling) ? 'array' : 'mixed[value="'.$directionOrNullHandling.'"]');
            throw new \UnexpectedValueException('Expect $directionOrNullHandling to be Direction or NullHandling but ' . $given . ' given.');
        }
    }
    
    /**
     * Returns a new {@link Order} with the given {@link Order}.
     *
     * @param order
     * @return Order
     */
    private function withDirection(Direction $direction) {
        return new Order($direction, $this->property, $this->nullHandling);
    }
    
    /**
     * Returns a new {@link Sort} instance for the given properties.
     *
     * @param properties
     * @return Sort
     */
    public function  withProperties(...$properties) {
        return new Sort($this->direction, $properties);
    }
    
    /**
     * Returns a new {@link Order} with case insensitive sorting enabled.
     *
     * @return Order
     */
    public function ignoreCase() {
        return new Order($this->direction, $this->property, true, $this->nullHandling);
    }
    
    /**
     * Returns a {@link Order} with the given {@link NullHandling}.
     *
     * @param nullHandling can be {@literal null}.
     * @return Order
     * @since 1.8
     */
    private function  withNullHandling(NullHandling $nullHandling) {
        return new Order($this->direction, $this->property, $this->ignoreCase, $nullHandling);
    }
    
    /**
     * Returns a {@link Order} with {@link NullHandling#NULLS_FIRST} as null handling hint.
     *
     * @return Order
     * @since 1.8
     */
    public function  nullsFirst() {
        return $this->with(NullHandling::NULLS_FIRST());
    }
    
    /**
     * Returns a {@link Order} with {@link NullHandling#NULLS_LAST} as null handling hint.
     *
     * @return Order
     * @since 1.7
     */
    public function  nullsLast() {
        return with(NullHandling::NULLS_LAST());
    }
    
    /**
     * Returns a {@link Order} with {@link NullHandling#NATIVE} as null handling hint.
     *
     * @return Order
     * @since 1.7
     */
    public function  nullsNative() {
        return with(NullHandling::NATIVE());
    }
    
    /**
     * Returns the used {@link NullHandling} hint, which can but may not be respected by the used datastore.
     *
     * @return NullHandling
     * @since 1.7
     */
    public function getNullHandling() {
        return $this->nullHandling;
    }
    
    public function  __toString(){
        
        $result = printf("%s: %s", $this->property, $this->direction);
        
        if(NullHandling::NATIVE === $this->nullHandling->getValue()){
            $result .= ', ' . $this->nullHandling;
        }
    
        if ($this->ignoreCase) {
            $result .= ", ignoring case";
        }
    
        return $result;
    }
}