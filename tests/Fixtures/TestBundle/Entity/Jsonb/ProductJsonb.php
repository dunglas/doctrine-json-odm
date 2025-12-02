<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Jsonb;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity using jsonb_document type.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
#[ORM\Entity]
class ProductJsonb
{
    #[
        ORM\Column(type: 'integer'),
        ORM\Id,
        ORM\GeneratedValue(strategy: 'AUTO'),
    ]
    public $id;

    #[ORM\Column(type: 'string')]
    public $name;

    #[ORM\Column(type: 'jsonb_document', nullable: true)]
    public $attributes;
}
