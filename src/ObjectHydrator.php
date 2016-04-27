<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

use Doctrine\ORM\Internal\Hydration\ObjectHydrator as BaseObjectHydrator;

class ObjectHydrator extends BaseObjectHydrator
{
    protected function gatherRowData(array $data, array &$id, array &$nonemptyComponents)
    {
        die('hello');
    }
}
