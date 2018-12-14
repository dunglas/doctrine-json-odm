<?php

namespace Dunglas\DoctrineJsonOdm\Normalizer;


use Doctrine\ORM\EntityManagerInterface;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Product;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerTest implements DenormalizerInterface, NormalizerInterface
{
	const ID_FIELD = 'id';

	private $em;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->em = $entityManager;
	}

	/**
	 * @param mixed|Product $object
	 * @param null          $format
	 * @param array         $context
	 *
	 * @return array|bool|float|int|string
	 */
	public function normalize($object, $format = null, array $context = array())
	{
		if (!$object instanceof Product) {
			return $object;
		}

		return [
			ObjectNormalizer::TYPE_FIELD => Product::class,
			self::ID_FIELD               => $object->id,
		];
	}

	public function supportsNormalization($data, $format = null)
	{
		return $data instanceof Product;
	}

	public function denormalize($data, $class, $format = null, array $context = array())
	{
		return !empty($id = $data[self::ID_FIELD])
			? $this->em->find(Product::class, $id)
			: $data;
	}

	public function supportsDenormalization($data, $type, $format = null)
	{
		return Product::class === $type;
	}
}