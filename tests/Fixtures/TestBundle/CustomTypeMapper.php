<?php

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\WithMappedType;
use Dunglas\DoctrineJsonOdm\TypeMapperInterface;

class CustomTypeMapper implements TypeMapperInterface
{
    public function getTypeByClass(string $class): string
    {
        return 'customTypeAlias';
    }

    public function getClassByType(string $type): string
    {
        return WithMappedType::class;
    }
}
