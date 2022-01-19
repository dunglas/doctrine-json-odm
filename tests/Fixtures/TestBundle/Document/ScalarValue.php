<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document;

use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

/**
 * Fixture class to test object normalized as scalar values.
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
if (method_exists(ArrayDenormalizer::class, 'setSerializer')) {
    // Symfony <=5.4
    class ScalarValue implements NormalizableInterface, DenormalizableInterface
    {
        use ScalarValueTrait;
    }
} else {
    // Symfony >=6.0
    class ScalarValue implements NormalizableInterface, DenormalizableInterface
    {
        use ScalarValueTrait, TypedScalarValueTrait {
            TypedScalarValueTrait::normalize insteadof ScalarValueTrait;
            TypedScalarValueTrait::denormalize insteadof ScalarValueTrait;
        }
    }
}
