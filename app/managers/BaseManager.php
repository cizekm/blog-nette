<?php

namespace App\Managers;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;

abstract class BaseManager
{
	/** @var EntityManager */
	protected $entityManager = null;

	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	abstract protected function getRepository(): EntityRepository;
}
