<?php
namespace MkDoctrineSpringData\Repository;

use Doctrine\ORM\QueryBuilder;
use MkDoctrineSpringData\Pagination\PageableInterface;
use MkDoctrineSpringData\Pagination\Sorting\Order;
use MkDoctrineSpringData\Pagination\Sort;
use MkDoctrineSpringData\Pagination\Sorting\NullHandling;
use MkDoctrineSpringData\Pagination\PageImpl;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\UnitOfWork;
use PhpCommonUtil\Util\Assert;
use MkDoctrineSpringData\Pagination\PageInterface;
use MkDoctrineSpringData\Aggregator\PageableOrSort;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use MkDoctrineSpringData\Resolver\NamingResolverInterface;

/**
 * 
 * @author Map
 * @
 * IF YOU OVERIDE CONSTRUCTOR METHOD IN THE EXTENDED CLASS, DO NOT FORGET TO CALL SUPER CONSTRUCTOR METHOD
 */
abstract class BaseRepositoryImpl extends EntityRepository implements  BaseRepositoryInterface
{
    
    
    public function __construct($em, ClassMetadata $class, NamingResolverInterface $namingResolver)
    {
        
        parent::__construct($em, $class);
    }
	
	/**
	 * @see \MkDoctrineSpringData\Repository\BaseRepositoryInterface::save()
	 */
	public final function save($entity, $flush = false){		

	    if (UnitOfWork::STATE_NEW === $this->_em->getUnitOfWork()->getEntityState($entity) ){
	        $this->_em->persist($entity);
	    }
	    else {
	        $entity = $this->_em->merge($entity);
	    }
	    
		if($flush){
			$this->_em->flush($entity);
		}	
		return $entity;
	}	
	
	/**
	 * @
	 * @see \MkDoctrineSpringData\Repository\BaseRepositoryInterface::delete()
	 */
	public final function delete($entity, $flush=false){
	    
	    if(!$this->_em->contains($entity)){
	        $entity = $this->_em->merge($entity);
	    }
	    
	    $this->_em->remove($entity);
	    
	    if($flush){
	        $this->_em->flush();
	    }
	    return $entity;
	}		
	
	/**
	 * @see \MkDoctrineSpringData\Repository\BaseRepositoryInterface::clear()
	 */
	public final function clear(){
	    $this->_em->clear($this->_class->rootEntityName);
	}
	
	/**
	 * @see \MkDoctrineSpringData\Repository\BaseRepositoryInterface::findOne()
	 */
	public final function findOne($id, $lockMode = null, $lockVersion = null){
		return $this->_em->find($this->_entityName, $id, $lockMode, $lockVersion);
	}	
	
	/**
	 * @see \Doctrine\Orm\EntityRepository::findBy()
	 */
	public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
	{
	    $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);
	
	    return $persister->loadAll($criteria, $orderBy, $limit, $offset);
	}
	
	/**
	 * @see \Doctrine\Orm\EntityRepository::findOneBy()
	 */
	public function findOneBy(array $criteria, array $orderBy = null)
	{
	    $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);
	
	    return $persister->load($criteria, null, null, array(), null, 1, $orderBy);
	}
	
	/**
	 * @see \MkDoctrineSpringData\Repository\BaseRepositoryInterface::getOne()
	 */
	public final function getOne($id){
		return $this->_em->getReference($this->_entityName, $id);
	}
	
	

 	/**
	 * @see \MkDoctrineSpringData\Repository\BaseRepositoryInterface::findAll()
	 */
	public function findAll(\MkDoctrineSpringData\Aggregator\PageableOrSort $pagableOrSort = null, array $ids = array(), $hydrationMode = null)
	{
	    
	    $qb = null;
	    if ( null === $pagableOrSort )
	    {
	        if( empty($ids) )
	        {
	            if(null === $hydrationMode) 
	            {
	                return $this->findBy(array());
	            }
	        }
	        else 
	        {
	            $identityFields = $this->_class->getIdentifier();
	            Assert::isTrue(count($identityFields)==1, 'DoctrineSpringData Repository only support single primary key.');
	            $idField = $identityFields[0];
	            $qb = $this->createQueryBuilder('e')->where("e.{$idField} IN (:ids)")->setParameter('ids', $ids);
	        }	        
	    }
	    
	    $qb = $this->createQueryBuilder($alias);
	    
	    
	    if(empty($pagableOrSortOrIds)){
	        if(null === $hydrationMode) {
	            return $this->findBy(array());
	        }else{
	            return $this->createQueryBuilder('e')->getQuery()->getResult($hydrationMode);
	        }
	    }
	    else if (is_array($pagableOrSortOrIds)){
	        $identityFields = $this->_class->getIdentifier();
	        Assert::isTrue(count($identityFields)==1, 'DoctrineSpringData Repository only support single primary key.');
	        $idField = $identityFields[0];
	        return $this->createQueryBuilder('e')
	                   ->where("e.{$idField} IN (:ids)")
	                   ->setParameter('ids', $pagableOrSortOrIds)
	                   ->getQuery()
	                   ->getResult();
	    }
	    else if($pagableOrSortOrIds instanceof  Sort){
	        $qb = $this->createQueryBuilder('e');
	        $this->processSorting($qb, $pagableOrSortOrIds);
	        return $qb->getQuery()->getResult($hydrationMode);
	    }
	    else{
	        $qb = $this->createQueryBuilder('e');
	        return $this->processPageable($qb, $pagableOrSortOrIds);
	    }
		
	}	
	
	/**
	 * 
	 * @param QueryBuilder $qb
	 * @param PageableOrSort $pageableOrSort
	 * @param string $alias
	 * @param int $hydrationMode
	 * @return PageInterface|array
	 */
	protected final function processPagableOrSorting(QueryBuilder $qb, PageableOrSort $pageableOrSort, $alias = 'e', $hydrationMode = null){
	    if ( $pageableOrSort instanceof Sort ) {
	        return $this->processSorting($qb, $pageableOrSort, $alias)->getQuery()->getResult($hydrationMode);
	    }else if ($pageableOrSort instanceof PageableInterface ){
	        return $this->processPageable($qb, $pageableOrSort, $alias, $hydrationMode);
	    }
	}
	
	/**
	 * Append query spec from Sort into QueryBuilder
	 * @param QueryBuilder $qb
	 * @param Sort $sort
	 * @param string $alias
	 * @return QueryBuilder
	 * @throws \InvalidArgumentException
	 */
	protected final function processSorting(QueryBuilder $qb, Sort $sort, $alias = 'e')
    {
        Assert::notNull($qb);
        Assert::notNull($sort);
        foreach ($sort as $order) {
            /* @var $order Order */
            $direction = $order->getDirection()->getValue();
            $nullHandling = $order->getNullHandling();
            $property = $order->getProperty();
            $nullHandlingString = null;
            switch ($nullHandling->getValue()) {
                case NullHandling::NATIVE:
                    $nullHandlingString = '';
                    break;
                case NullHandling::NULLS_FIRST:
                    $nullHandlingString = ' NULLS FIRST';
                    break;
                case NullHandling::NULLS_LAST:
                    $nullHandlingString = ' NULLS LAST';
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid Null Handling value which not match any switch case: ' . $nullHandling->getValue());
            }
            $qb->addOrderBy("{$alias}.{$property}", $direction . $nullHandlingString);
        }
        return $qb;
    }
	
	/**
	 * Append query spec from PagableInterfect into QueryBuilder
	 * @param QueryBuilder $qb
	 * @param PageableInterface $pagable
	 * @param string $alias
	 * @return integer total elements
	 * @throws \InvalidArgumentException
	 */
	protected final function countTotalElements(QueryBuilder $qb, PageableInterface $pagable, $alias = 'e')
	{
	    /* @var $select Select */
	    $selectList = $qb->getDQLPart('select');
	    $qb->resetDQLPart('select');
	    $qb->select("count({$alias})");
	    $total = $qb->getQuery()->getSingleScalarResult();
	    $qb->resetDQLPart('select');
	    
	    foreach($selectList as $select){
	        $qb->addSelect($select->getParts());
	    }
	    
	    return $total;
	}
	
	/**
	 * Append query spec from PagableInterfect into QueryBuilder
	 * @param QueryBuilder $qb
	 * @param PageableInterface $pagable
	 * @param string $alias
	 * @param int $hydrationMode
	 * @return PageInterface
	 * @throws \InvalidArgumentException
	 */
	protected final function processPageable(QueryBuilder $qb, PageableInterface $pagable, $alias = 'e', $hydrationMode = null)
	{
	    
	    $total = $this->countTotalElements($qb, $pagable);
	    
	    $sort = $pagable->getSort();
	    if( null !== $sort){
	        $this->processSorting($qb, $sort, $alias);
	    }
	    $qb->setMaxResults($pagable->getPageSize());
	    $qb->setFirstResult($pagable->getPageSize() * $pagable->getPageNumber());
	    
	    $content = $qb->getQuery()->getResult($hydrationMode);
	    $page = new PageImpl($content, $pagable, $total);
	    return $page;
	}

	public final function getClassName(){
		return $this->_entityName;
	}
	
	protected function buildEntityName(){
	    $clazz = get_called_class();
	    $namingStrategy = $clazz::$NAMING_STRATEGY;
	    $clazz = str_replace('\Repository', "\\{$namingStrategy}", $clazz);
	    $clazz = str_replace('Repository', '', $clazz);
	    $clazz = str_replace("Impl", '', $clazz);
	    return $clazz;
	}

}

