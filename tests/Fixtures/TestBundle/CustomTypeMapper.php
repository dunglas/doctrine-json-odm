<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
