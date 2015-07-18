<?php

namespace MkDoctrineSpringData\Pagination;


use PhpCommonUtil\Util\Assert;
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
    public function  __construct($page, $size, $directionOrSort = null, $properties = array()) {
        $properties = is_array($properties) ? $properties : array($properties);
        Assert::isTrue(is_null($directionOrSort) || $directionOrSort instanceof Direction || $directionOrSort instanceof Sort);
        parent::__construct($page, $size);
        $this->sort = is_null($directionOrSort) ? null : ( $directionOrSort instanceof  Sort ? $directionOrSort: new Sort($directionOrSort, $properties) );
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
