<?php

namespace MkDoctrineSpringData\Pagination;

use PhpCommonUtil\Util\Assert;

abstract  class Chunk implements  SliceInterface
{
    
    /**
     * 
     * @var array
     */
    private $content = array();
    
    /**
     * @var PageableInterface
     */
    private $pageable;
    
    /**
     * Creates a new {@link Chunk} with the given content and the given governing {@link Pageable}.
     *
     * @param array content must not be {@literal null}.
     * @param PageableInterface pageable can be {@literal null}.
     */
    public function  __construct(array $content, PageableInterface $pageable) {
        Assert::notNull($content, "Content must not be null!");
        $this->content = array_merge($this->content, $content);
        $this->pageable = $pageable;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::getNumber()
     */
    public function  getNumber() {
        return $this->pageable == null ? 0 : $this->pageable->getPageNumber();
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::getSize()
     */
    public function getSize() {
        return $this->pageable == null ? 0 : $this->pageable->getPageSize();
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::getNumberOfElements()
     */
    public function  getNumberOfElements() {
        return count($this->content);
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::hasPrevious()
     */
    public function hasPrevious() {
        return $this->getNumber() > 0;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::isFirst()
     */
    public function isFirst() {
        return ! $this->hasPrevious();
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::isLast()
     */
    public function isLast() {
        return ! $this->hasNext();
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::nextPageable()
     */
    public function nextPageable() {
        return  $this->hasNext() ? $this->pageable->next() : null;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::previousPageable()
     */
    public function previousPageable() {
        if( $this->hasPrevious()){
            return $this->pageable->previousOrFirst();
        }
        return null;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::hasContent()
     */
    public function hasContent() {
        return !empty($this->content);
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::getContent()
     */
    public function getContent() {
        $arrayObject = new \ArrayObject($this->content);
        return $arrayObject->getArrayCopy();
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::getSort()
     */
    public function getSort() {
        return $this->pageable == null ? null : $this->pageable->getSort();
    }
    
    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator() {
        return new \ArrayObject($this->content);
    }
    
}