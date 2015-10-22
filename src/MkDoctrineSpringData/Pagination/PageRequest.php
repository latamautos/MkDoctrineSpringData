<?php

namespace MkDoctrineSpringData\Pagination;


use PhpCommonUtil\Util\Assert;
use MkDoctrineSpringData\Aggregator\DirectionOrSort;
use MkDoctrineSpringData\Pagination\Sorting\Direction;
class PageRequest extends AbstractPageRequest
{
    
    private $sort;
    
    /**
     * Creates a new {@link PageRequest}. Pages are zero indexed, thus providing 0 for {@code page} will return the first
     * page.
     *
     * @param int page zero-based page index.
     * @param int size the size of the page to be returned.
     */
    public function  __construct($page, $size, DirectionOrSort $directionOrSort = null, ...$properties) 
    {
        parent::__construct($page, $size);
        if ( null === $directionOrSort){
            return;
        }else if ($directionOrSort instanceof Sort){
            $this->sort = $directionOrSort;
        }else if ($directionOrSort instanceof Direction){
            $clazz = new \ReflectionClass(Sort::class);
            $this->sort = $clazz->newInstanceArgs( array_merge([$directionOrSort], $properties));
        }else{
            throw new \InvalidArgumentException('Unimplemented for class: ' . get_class($directionOrSort));
        }
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\PageableInterface::getSort()
     */
    public function  getSort() {
        return $this->sort;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\AbstractPageRequest::next()
     */
    public function  next() {
        return new PageRequest($this->getPageNumber() + 1, $this->getPageSize(), $this->getSort());
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\AbstractPageRequest::previous()
     */
    public function previous() {
        return $this->getPageNumber()==0 ? $this : new PageRequest($this->getPageNumber()-1, $this->getPageSize(), $this->getSort());
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\AbstractPageRequest::first()
     */
    public function first() {
        return new PageRequest(0, $this->getPageSize(), $this->getSort());
    }
    
    
    public function  __toString(){
        return printf('Page request [number: %d, size %d, sort: %s]', $this->getPageNumber(), $this->getPageSize(), $this->sort==null ? null : $this->sort);
    }
    
}
