<?php
namespace MkDoctrineSpringData\Factory;

use Doctrine\ORM\Repository\RepositoryFactory as DoctrineRepositoryFactoryInterface;
use MkDoctrineSpringData\Resolver\NamingResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use MkDoctrineSpringData\Repository\BaseRepositoryImpl;

class RepositoryFactory implements DoctrineRepositoryFactoryInterface
{
    
    private $namingResolver;
    
    public function __construct(NamingResolverInterface $namingResolver){
        $this->namingResolver = $namingResolver;
    }
    
    public function getRepository(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityName)
    {
        /* @var $entityManager EntityManagerInterface */
        $clazz = $this->namingResolver->resolveRepositoryImplementationName($this->namingResolver->resolveRepositoryInterfaceName($entityName));
        if (class_exists($clazz)){
            $clazz = '\\'.$clazz;
            return new $clazz($entityManager, $entityManager->getClassMetadata($entityName));
        }else{
            return new BaseRepositoryImpl($entityManager, $entityManager->getClassMetadata($entityName));
        }
        
    }
    
}
?>