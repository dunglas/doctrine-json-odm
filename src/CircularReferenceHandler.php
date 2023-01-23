<?php

namespace Dunglas\DoctrineJsonOdm;

class CircularReferenceHandler
{
    public function __invoke($object, $format, $context): array
    {
        return [];
    }
}
