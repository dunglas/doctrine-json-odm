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
class Bar
{
    private mixed $title;
    private mixed $weight;

    public function getTitle(): mixed
    {
        return $this->title;
    }

    public function setTitle(mixed $title)
    {
        $this->title = $title;
    }

    public function getWeight(): mixed
    {
        return $this->weight;
    }

    public function setWeight(mixed $weight)
    {
        $this->weight = $weight;
    }
}
