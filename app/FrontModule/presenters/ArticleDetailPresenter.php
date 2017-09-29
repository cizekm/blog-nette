<?php

namespace App\FrontModule\Presenters;

class ArticleDetailPresenter extends BaseArticlesPresenter
{
	public function renderDefault(string $articleUrl = null): void
	{
		$article = $this->articlesManager->getPublishedArticleByUrl($articleUrl);

		if ($article === null) {
			$this->throw404();
		}

		$this->template->article = $article;

		$backUrl = trim($this->getParameter('back'));
		if ($backUrl === '') {
			$backUrl = $this->link('Homepage:default');
		}
		$this->template->backUrl = $backUrl;

		$this->articlesManager->logArticleView($article);
	}
}
