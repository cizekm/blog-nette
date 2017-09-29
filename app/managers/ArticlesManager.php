<?php

namespace App\Managers;

use App\Models\Article;
use Doctrine\ORM\QueryBuilder;
use Kdyby\Doctrine\EntityRepository;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

class ArticlesManager extends BaseManager
{
	protected function getRepository(): EntityRepository
	{
		return $this->entityManager->getRepository(Article::class);
	}

	protected function getPublishedArticleCriteria(): array
	{
		return [
			'visible' => true,
			'publishedTimestamp <=' => new DateTime('now')
		];
	}

	/**
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return array|Article[]
	 */
	public function getPublishedArticlesByDateDesc(int $limit = null, int $offset = null): array
	{
		return $this->getRepository()->findBy(
			$this->getPublishedArticleCriteria(),
			['publishedTimestamp' => 'desc'],
			$limit,
			$offset
		);
	}

	/**
	 * @return int
	 */
	public function getPublishedArticlesCount(): int
	{
		return $this->getRepository()->countBy($this->getPublishedArticleCriteria());
	}

	/**
	 * @param string $articleUrl
	 * @return Article|null
	 */
	public function getPublishedArticleByUrl(string $articleUrl): ?Article
	{
		$criteria = $this->getPublishedArticleCriteria();
		$criteria['url'] = $articleUrl;

		return $this->getRepository()->findOneBy($criteria);
	}

	/**
	 * @param string $articleUrl
	 * @param int|null $excludeId
	 * @return bool
	 */
	public function urlExists(string $articleUrl, int $excludeId = null): bool
	{
		$criteria = [
			'url' => trim(Strings::webalize($articleUrl))
		];

		if ($excludeId !== null) {
			$criteria['id !='] = $excludeId;
		}

		return $this->getRepository()->countBy($criteria) > 0;
	}

	/**
	 * @param int $id
	 * @return Article|null
	 */
	public function getArticleById(int $id): ?Article
	{
		return $this->getRepository()->find($id);
	}

	/**
	 * @param Article $article
	 * @param bool $flushImmediately
	 * @return $this
	 */
	public function saveArticle(Article $article, bool $flushImmediately = true): self
	{
		$this->entityManager->persist($article);
		if ($flushImmediately) {
			$this->entityManager->flush();
		}

		return $this;
	}

	/**
	 * @param string $articleUrl
	 * @param int|null $articleId
	 * @return string
	 */
	public function getUniqueUrl(string $articleUrl, int $articleId = null): string
	{
		$articleUrlBase = $articleUrl = trim(Strings::webalize($articleUrl));

		$i = 2;
		while ($this->urlExists($articleUrl, $articleId)) {
			$articleUrl = $articleUrlBase.'-'.$i++;
		}

		return $articleUrl;
	}

	/**
	 * @param string|null $alias
	 * @param string|null $indexBy
	 * @return QueryBuilder
	 */
	public function createQueryBuilder(string $alias = null, string $indexBy = null): QueryBuilder
	{
		return $this->getRepository()->createQueryBuilder($alias, $indexBy);
	}

	/**
	 * @param Article $article
	 * @return $this
	 */
	public function logArticleView(Article $article): self
	{
		// @TODO refactor to async views counting
		$article->increaseViewsCnt();
		$this->saveArticle($article);

		return $this;
	}
}
