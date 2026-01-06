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
    class Foo
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
        private $id;

        /**
         * @ORM\Column(type="string")
         */
        #[ORM\Column(type: 'string')]
        private $name;

        /**
         * @ORM\Column(type="json_document", options={"jsonb": true})
         */
        #[ORM\Column(type: 'json_document', options: ['jsonb' => true])]
        private $misc;

        public function getId()
        {
            return $this->id;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setName($name): void
        {
            $this->name = $name;
        }

        public function getMisc()
        {
            return $this->misc;
        }

        public function setMisc(array $misc): void
        {
            $this->misc = $misc;
        }
    }
} else {
    /**
     * @ORM\Entity
     *
     * @author Kévin Dunglas <dunglas@gmail.com>
     */
    #[ORM\Entity]
    class Foo
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
        private $id;

        /**
         * @ORM\Column(type="string")
         */
        #[ORM\Column(type: 'string')]
        private $name;

        /**
         * @ORM\Column(type="jsonb_document")
         */
        #[ORM\Column(type: 'jsonb_document')]
        private $misc;

        public function getId()
        {
            return $this->id;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setName($name): void
        {
            $this->name = $name;
        }

        public function getMisc()
        {
            return $this->misc;
        }

        public function setMisc(array $misc): void
        {
            $this->misc = $misc;
        }
    }
}
