<?php
namespace MkDoctrineSpringData\Repository;

use MkDoctrineSpringData\Pagination\PageInterface;
use MkDoctrineSpringData\Aggregator\PageableOrSort;
use Doctrine\Common\Persistence\ObjectRepository;

interface BaseRepositoryInterface extends ObjectRepository
{
    
    /**
     * Save target entity into db.
     * @param object $entity Entity to save
     * @param bool $flush flag to flush after save or not 
     */
    function save($entity, $flush = false);
    
    /**
     * delete target entity
     * @param object $entity 
     * @param string $flush
     * @return object return entity with state is removed.
     */
    function delete($entity, $flush=false);
    
    /**
     * @see \Doctrine\ORM\EntityRepository::clear()
     */
    function clear();
    
    /**
     * @see \Doctrine\ORM\EntityRepository::find()
     */
    function findOne($id, $lockMode = null, $lockVersion = null);
    
    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getReference()
     */
    function getOne($id);
    
    /**
     * Finds all objects in the repository.
     * @param PageableInterface|Sort|array $pagableOrSort array of Ids if you want to find all matching given ids, Sort if you wanna define sorting or PagableInterface if you wanna do pagnigation, you can also define current page, page size and sorting within PagableInterface. otherwise find all entities.
     * @param int $hydrationMode Doctrine HydrationMode.
     * @return PageInterface|array
     */
    function findAll(PageableOrSort $pageableOrSort = null, array $ids = array(), $hydrationMode = null);
    
    /**
     * @see \Doctrine\ORM\EntityRepository::getClassName()
     */
    function getClassName();
    
}

?>