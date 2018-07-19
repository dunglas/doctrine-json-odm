<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class Attribute
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string|Attribute[][]
     */
    public $value;
}
