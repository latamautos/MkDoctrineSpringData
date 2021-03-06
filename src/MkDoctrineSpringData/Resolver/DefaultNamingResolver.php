<?php
namespace MkDoctrineSpringData\Resolver;

class DefaultNamingResolver implements NamingResolverInterface
{
    
    private $entityKeywordList; 
    private $implementationSuffix;
    private $interfaceSuffix;
    
    public function __construct($interfaceSuffix = 'Interface', $implementationSuffix = 'Impl', array $entityKeywordList = array( 'Domain', 'Entity' ) )
    {
        $this->implementationSuffix = $implementationSuffix;
        $this->entityKeywordList = $entityKeywordList;
        $this->interfaceSuffix = $interfaceSuffix;
    }
    
    public function resolveEntityName($repositoryInterfaceName)
    {
        foreach ($this->entityKeywordList as $keyword){
            $tmpResult = str_replace('\Repository', "\\{$keyword}", $repositoryInterfaceName);
            $tmpResult = str_replace('Repository', "", $tmpResult);
            $tmpResult = substr($tmpResult, 0, strpos($tmpResult, $this->interfaceSuffix));
            if(class_exists($tmpResult)){
                return $tmpResult;
            }
        }
        throw new \InvalidArgumentException( sprintf('Unable to resolve repositoryInterface[%s] to entity name', $repositoryInterfaceName));
    }

    public function resolveRepositoryImplementationName($repositoryInterfaceName)
    {
        return substr($repositoryInterfaceName, 0, strpos($repositoryInterfaceName, $this->interfaceSuffix)) . $this->implementationSuffix ;
    }
    
    public function resolveRepositoryInterfaceName($entityName)
    {
        $entityKeyword = $this->findEntityKeyword($entityName);
        $clazz = str_replace( "\\{$entityKeyword}", '\Repository', $entityName);
        $clazz = $clazz . 'Repository' . $this->interfaceSuffix ;
        return $clazz;
    }

    protected function findEntityKeyword($repositoryOrEntityName){
        foreach($this->entityKeywordList as $keyword){
            if ( false !== strpos($repositoryOrEntityName, $keyword)){
                return $keyword;
            }
        }
        throw new \InvalidArgumentException(sprintf('keywords (%s) is not found in repository name[%s]', implode(',', $this->entityKeywordList), $repositoryOrEntityName) );
    }

    
}

?>