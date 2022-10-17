<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

/**
 * Allows using string constants in place of class names.
 */
class TypeMapper implements TypeMapperInterface
{
    /**
     * @var array<class-string, string>
     */
    private $classToType;

    /**
     * @var array<string, class-string>
     */
    private $typeToClass;

    /**
     * @param array<class-string, string> $classToType
     */
    public function __construct(array $classToType)
    {
        $this->classToType = $classToType;
        $this->typeToClass = array_flip($classToType);
    }

    /**
     * Falls back to class name itself.
     *
     * @param class-string $class
     */
    public function getTypeByClass(string $class): string
    {
        return $this->typeToClass[$class] ?? $class;
    }

    /**
     * Falls back to type name itself – it might as well be a class.
     *
     * @return class-string
     */
    public function getClassByType(string $type): string
    {
        return $this->classToType[$type] ?? $type;
    }
}
