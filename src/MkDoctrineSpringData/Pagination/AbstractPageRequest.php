<?php
namespace MkDoctrineSpringData\Pagination;

abstract class AbstractPageRequest implements  PageableInterface, Multisite
{
    
    private $page;
    private $size;
		private $site;
    
    /**
     * Creates a new {@link AbstractPageRequest}. Pages are zero indexed, thus providing 0 for {@code page} will return
     * the first page.
     *
     * @param int page must not be less than zero.
     * @param int size must not be less than one.
     */
    public function __construct($page, $size) {
    
        if ($page < 0) {
            throw new \InvalidArgumentException('Page index must not be less than zero!');
        }
    
        if ($size < 1) {
            throw new \InvalidArgumentException('Page size must not be less than one!');
        }
    
        $this->page = $page;
        $this->size = $size;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\PageableInterface::getPageSize()
     */
    public function  getPageSize() {
        return $this->size;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\PageableInterface::getPageNumber()
     */
    public function  getPageNumber() {
        return $this->page;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\PageableInterface::getOffset()
     */
    public function getOffset() {
        return $this->page * $this->size;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\PageableInterface::hasPrevious()
     */
    public function  hasPrevious() {
        return $this->page > 0;
    }
    
    /**
     * @see \MkDoctrineSpringData\Pagination\PageableInterface::previousOrFirst()
     */
    public function  previousOrFirst() {
        return $this->hasPrevious() ? $this->previous() : $this->first();
    }

		public function getSite() {
			return $this->site;
		}

		public function setSite($site) {
			$this->site = $site;
		}

    /**
     * @see \MkDoctrineSpringData\Pagination\PageableInterface::next()
     */
    public abstract function next();
    
    /**
     * @return PageableInterface
    */
    public abstract function previous();
    
    /**
     * @return PageableInterface
     */
    public abstract function first();
    
}