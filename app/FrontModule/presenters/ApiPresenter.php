<?php

namespace App\FrontModule\Presenters;

use App\Managers\ArticlesManager;
use App\Models\Article;
use App\Models\Tag;
use Nette\Application\Responses\JsonResponse;
use Nette\DI\Container;

class ApiPresenter extends BasePresenter
{
	/** @var ArticlesManager */
	protected $articlesManager = null;

	public function __construct(Container $container, ArticlesManager $articlesManager)
	{
		parent::__construct($container);

		$this->articlesManager = $articlesManager;
	}

	public function actionDefault(): void
	{
		$this->throw404();
	}

	public function actionArticles(): void
	{
		$data = $this->getArticlesListData();

		$this->sendResponse(new JsonResponse($data));
	}

	public function actionArticle(int $id): void
	{
		$data = $this->getArticleDetailData($id);

		$this->sendResponse(new JsonResponse($data));
	}

	protected function getArticlesListData(): array
	{
		$data = [];

		foreach ($this->articlesManager->getPublishedArticlesByDateDesc() as $article) {
			$data[] = [
				'id' => $article->getId(),
				'title' => $article->getTitle(),
				'url' => $this->getArticleUrl($article),
				'publishedTimestamp' => $article->getPublishedTimestampString('Y-m-d H:i:s'),
				'visible' => $article->isVisible(), // always true, API provides only visible articles
				'viewsCnt' => $article->getViewsCnt()
			];
		}

		return $data;
	}

	protected function getArticleDetailData(int $articleId): array
	{
		if ($article = $this->articlesManager->getArticleById($articleId)) {

			if ($article->isPublic()) {
				$data = [
					'id' => $article->getId(),
					'title' => $article->getTitle(),
					'url' => $this->getArticleUrl($article),
					'content' => $article->getContent(),
					'publishedTimestamp' => $article->getPublishedTimestampString('Y-m-d H:i:s'),
					'visible' => $article->isVisible(),
					'viewsCnt' => $article->getViewsCnt(),
					'tags' => $article->getTags()->map(
						function (Tag $tag) {
							return $tag->getTitle();
						}
					)->toArray()
				];

				$this->articlesManager->logArticleView($article);

				return $data;
			} else {
				return [
					'error' => 'Article is not public'
				];
			}
		} else {
			return [
				'error' => 'Article not found'
			];
		}
	}

	protected function getArticleUrl(Article $article): string
	{
		return rtrim($this->baseUrl, ' /').'/'.ltrim($article->getUrl(), ' /');
	}
}


