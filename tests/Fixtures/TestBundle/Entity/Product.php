<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity;

use Doctrine\DBAL\Types\JsonbType;
use Doctrine\ORM\Mapping as ORM;

if (!class_exists(JsonbType::class)) {
    /**
     * @ORM\Entity
     *
     * @author Kévin Dunglas <dunglas@gmail.com>
     */
    #[ORM\Entity]
    class Product
    {
        /**
         * @ORM\Column(type="integer")
         *
         * @ORM\Id
         *
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        #[
            ORM\Column(type: 'integer'),
            ORM\Id,
            ORM\GeneratedValue(strategy: 'AUTO'),
        ]
        public $id;

        /**
         * @ORM\Column(type="string")
         */
        #[ORM\Column(type: 'string')]
        public $name;

        /**
         * @ORM\Column(type="json_document", options={"jsonb": true}, nullable=true)
         */
        #[ORM\Column(type: 'json_document', nullable: true, options: ['jsonb' => true])]
        public $attributes;
    }
} else {
    /**
     * Entity using jsonb_document type.
     *
     * @author Kévin Dunglas <dunglas@gmail.com>
     */
    #[ORM\Entity]
    class Product
    {
        /**
         * @ORM\Column(type="integer")
         *
         * @ORM\Id
         *
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        #[
            ORM\Column(type: 'integer'),
            ORM\Id,
            ORM\GeneratedValue(strategy: 'AUTO'),
        ]
        public $id;

        /**
         * @ORM\Column(type="string")
         */
        #[ORM\Column(type: 'string')]
        public $name;

        /**
         * @ORM\Column(type="jsonb_document", nullable=true)
         */
        #[ORM\Column(type: 'jsonb_document', nullable: true)]
        public $attributes;
    }
}
