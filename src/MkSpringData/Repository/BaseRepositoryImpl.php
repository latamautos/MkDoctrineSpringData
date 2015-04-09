<?php
namespace MkDoctrineSpringData\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use MkDoctrineSpringData\Pagination\PageableInterface;
use MkDoctrineSpringData\Pagination\Sorting\Direction;
use MkDoctrineSpringData\Pagination\Sorting\Order;
use MkDoctrineSpringData\Pagination\Sort;
use MkDoctrineSpringData\Pagination\Sorting\NullHandling;
use MkDoctrineSpringData\Pagination\PageImpl;
use Zend\Debug\Debug;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpCommonUtil\Util\Assert;

/**
 * 
 * @author Map
 * IF YOU OVERIDE CONSTRUCTOR METHOD IN THE EXTENDED CLASS, DO NOT FORGET TO CALL SUPER CONSTRUCTOR METHOD
 */
abstract class BaseRepositoryImpl implements  BaseRepositoryInterface
{
	
	/**
	 * 
	 * @var \Doctrine\ORM\EntityRepository
	 */
	protected $_repository = null;
	
	/**
	 * 
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $_em = null;	
	
	/**
	 * entity class name
	 * @var string
	 */
	protected $_entityName = null;
	
	/**
	 * 
	 * @var ClassMetadata
	 */
	protected $_classMetadata = null;
	
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
	    $this->_em->clear($this->_classMetadata->rootEntityName);
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
	public final function findAll($pagableOrSortOrIds = null, $hydrationMode = null){
	    
	    if(empty($pagableOrSortOrIds)){
	        if(null === $hydrationMode) {
	            return $this->findBy(array());
	        }else{
	            return $this->createQueryBuilder('e')->getQuery()->getResult($hydrationMode);
	        }
	    }
	    else if (is_array($pagableOrSortOrIds)){
	        $identityFields = $this->_classMetadata->getIdentifier();
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
	        $total = $this->processPagnigation($qb, $pagableOrSortOrIds);
	        $content = $qb->getQuery()->getResult($hydrationMode);
	        $page = new PageImpl($content, $pagableOrSortOrIds, $total);
	        return $page;
	    }
		
	}	
	
	
	/**
	 * Append query spec from Sort into QueryBuilder
	 * @param QueryBuilder $qb
	 * @param Sort $sort
	 * @param string $alias
	 * @return integer total elements
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
    }
	
	/**
	 * Append query spec from PagableInterfect into QueryBuilder
	 * @param QueryBuilder $qb
	 * @param PageableInterface $pagable
	 * @param string $alias
	 * @return integer total elements
	 * @throws \InvalidArgumentException
	 */
	protected final function processPagnigation(QueryBuilder $qb, PageableInterface $pagable, $alias = 'e'){
	    
	    $sort = $pagable->getSort();
	    
	    if($sort){
	        $this->processSorting($qb, $sort,$alias);
	    }
	    
	    /* @var $select Select */
	    $selectList = $qb->getDQLPart('select');
	    $qb->resetDQLPart('select');
	    $qb->select("count({$alias})");
	    $total = $qb->getQuery()->getSingleScalarResult();
	    $qb->resetDQLPart('select');
	    
	    foreach($selectList as $select){
	        $qb->addSelect($select->getParts());
	    }
	    $qb->setMaxResults($pagable->getPageSize());
	    $qb->setFirstResult($pagable->getPageSize() * $pagable->getPageNumber());
	    return $total;
	}

	public final function getClassName(){
		return $this->_entityName;
	}
	
	/**
	 * @Inject
	 */
	public function __construct(EntityManager $em, $entityName = null){
	    $this->_em = $em;
		$this->_entityName = null===$entityName ? $this->buildEntityName() : $entityName;
		$this->_classMetadata = $this->_em->getClassMetadata($this->_entityName);
	}
	
	protected function buildEntityName(){
	    $clazz = get_called_class();
	    $clazz = str_replace('\Repository', '\Entity', $clazz);
	    $clazz = str_replace('Repository', '', $clazz);
	    $clazz = str_replace("Impl", '', $clazz);
	    return $clazz;
	}
	
	
	/**
	 * @see \Doctrine\ORM\EntityRepository::createQueryBuilder()
	 */
	public function createQueryBuilder($alias, $indexBy = null)
	{
	    return $this->_em->createQueryBuilder()
	    ->select($alias)
	    ->from($this->_entityName, $alias, $indexBy);
	}
	

}