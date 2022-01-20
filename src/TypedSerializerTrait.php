<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

/**
 * @internal
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
trait TypedSerializerTrait
{
    use SerializerTrait {
        normalize as private doNormalize;
        denormalize as private doDenormalize;
    }

    public function normalize(mixed $data, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return $this->doNormalize($data, $format, $context);
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        return $this->doDenormalize($data, $type, $format, $context);
    }
}
