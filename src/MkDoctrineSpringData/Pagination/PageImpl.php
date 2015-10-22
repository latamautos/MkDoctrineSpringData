<?php
namespace MkDoctrineSpringData\Pagination;

class PageImpl extends Chunk implements PageInterface
{
    
    private $total;
    
    /**
     * Constructor of {@code PageImpl}.
     *
     * @param content the content of this page, must not be {@literal null}.
     * @param pageable the paging information, can be {@literal null}.
     * @param total the total amount of items available
     */
    public function __construct(array $content, PageableInterface $pageable = NULL, $total = NULL) {
        $total = null===$total ? (null===$content ? 0 : count($content) ) :$total;
        parent::__construct($content, $pageable);
        $this->total = $total;
    }
    
    
    /**
     * @see \MkDoctrineSpringData\Pagination\PageInterface::getTotalPages()
     */
    public function  getTotalPages() {
        return $this->getSize() == 0 ? 1 : ceil($this->total/$this->getSize());
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\PageInterface::getTotalElements()
     */
    public function getTotalElements() {
        return $this->total;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\SliceInterface::hasNext()
     */
    public function  hasNext() {
        return ( $this->getNumber() + 1 ) < $this->getTotalPages();
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\Chunk::isLast()
     */
    public function isLast() {
        return ! $this->hasNext();
    }
    
    
    public function  __toString(){
        $contentType = 'UNKNOWN';
        if( count($this->content) > 0 ){
            $contentType = get_class( $this->content[0] );
        }
        return printf("Page %s of %d containing %s instances", $this->getNumber(), $this->getTotalPages(), $contentType);
    }

}