<?php
namespace  MkDoctrineSpringData\Pagination\Sorting;
use MyCLabs\Enum\Enum;

class NullHandling extends Enum
{
    const NATIVE = 'NATIVE';

    const NULLS_FIRST = 'NULLS_FIRST';
    
    const NULLS_LAST = 'NULLS_LAST';
    
}