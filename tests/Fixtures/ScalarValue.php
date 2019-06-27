<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures;

use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Fixture class to test object normalized as scalar values
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class ScalarValue implements NormalizableInterface, DenormalizableInterface
{
    private $value;

    public function __construct($value = '')
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }

    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        return $this->value;
    }

    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = [])
    {
        $this->value = $data;

        return $this;
    }
}
