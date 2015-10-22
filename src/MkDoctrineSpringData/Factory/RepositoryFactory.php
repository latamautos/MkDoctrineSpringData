<?php
namespace MkDoctrineSpringData\Factory;

use Doctrine\ORM\Repository\RepositoryFactory as DoctrineRepositoryFactoryInterface;
use MkDoctrineSpringData\Resolver\NamingResolverInterface;

class RepositoryFactory implements DoctrineRepositoryFactoryInterface
{
    
    private $namingResolver;
    
    public function __construct(NamingResolverInterface $namingResolver){
        $this->namingResolver = $namingResolver;
    }
    
    public function getRepository(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityName)
    {
        $clazz = $this->namingResolver->resolveRepositoryImplementationName($this->namingResolver->resolveRepositoryInterfaceName($entityName));
        return new $clazz($entityManager, $entityName);
    }
    
}
?>