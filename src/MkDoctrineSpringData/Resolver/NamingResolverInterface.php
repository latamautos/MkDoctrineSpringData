<?php
namespace MkDoctrineSpringData\Resolver;

interface NamingResolverInterface
{
    function resolveEntityName($repositoryInterfaceName);
    function resolveRepositoryImplementationName($repositoryInterfaceName);
    function resolveRepositoryInterfaceName($entityName);
}

?>