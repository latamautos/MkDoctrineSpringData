<?php
namespace MkDoctrineSpringData\Pagination;

interface PageInterface extends SliceInterface
{
    function getTotalPages();
    function getTotalElements();
}