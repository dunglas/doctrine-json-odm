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
final class TypeMapper implements TypeMapperInterface
{
    /**
     * @var array<string, class-string>
     */
    private $typeToClass;

    /**
     * @var array<class-string, string>
     */
    private $classToType;

    /**
     * @param array<class-string, string> $typeToClass
     */
    public function __construct(array $typeToClass)
    {
        $this->typeToClass = $typeToClass;
        $this->classToType = array_flip($typeToClass);
    }

    /**
     * Falls back to class name itself.
     *
     * @param class-string $class
     */
    public function getTypeByClass(string $class): string
    {
        return $this->classToType[$class] ?? $class;
    }

    /**
     * Falls back to type name itself – it might as well be a class.
     *
     * @return class-string
     */
    public function getClassByType(string $type): string
    {
        return $this->typeToClass[$type] ?? $type;
    }
}
