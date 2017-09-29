<?php

namespace App\Managers;

use App\Models\Tag;
use Kdyby\Doctrine\EntityRepository;
use Nette\Utils\Strings;

class TagsManager extends BaseManager
{
	protected function getRepository(): EntityRepository
	{
		return $this->entityManager->getRepository(Tag::class);
	}

	public function getUidFromTitle(string $title): string
	{
		return trim(Strings::webalize($title));
	}

	/**
	 * @param string $title
	 * @return Tag
	 */
	public function getTagByTitle(string $title): ?Tag
	{
		$tags = $this->getRepository()->findBy(['title' => $title]);

		if (count($tags) === 1) {
			return reset($tags);
		}

		$tagUid = $this->getUidFromTitle($title);

		return $this->getRepository()->find($tagUid) ?: null;
	}

	/**
	 * @param Tag $tag
	 * @param bool $flushImmediately
	 * @return $this
	 */
	public function saveTag(Tag $tag, bool $flushImmediately = true): self
	{
		$this->entityManager->persist($tag);
		if ($flushImmediately) {
			$this->entityManager->flush();
		}

		return $this;
	}
}
