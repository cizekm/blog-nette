<?php

namespace App\FrontModule\Presenters;

use App\Managers\ArticlesManager;
use App\Models\Article;
use Doctrine\ORM\EntityManager;
use IPub\VisualPaginator\Components\Control;
use Nette\DI\Container;
use Nette\Utils\Paginator;
use Nette\Utils\Strings;

class HomepagePresenter extends BaseArticlesPresenter
{
	/** @var int */
	protected $itemsPerPage = null;

	public function __construct(Container $container, ArticlesManager $articlesManager)
	{
		parent::__construct($container, $articlesManager);

		$this->itemsPerPage = $container->getParameters()['articles']['itemsPerPage'] ?? 2; // convention over configuration :-)
	}

	public function renderDefault(): void
	{
		$paginator = $this->getPaginator();
		$paginator->setItemsPerPage($this->itemsPerPage);
		$paginator->setItemCount($this->articlesManager->getPublishedArticlesCount());

		$this->template->page = $paginator->getPage();
		$this->template->articles = $this->getArticles();

		return;
	}

	protected function getPaginator(): Paginator
	{
		/** @var Control $paginatorControl */
		$paginatorControl = $this['paginator'];

		return $paginatorControl->getPaginator();
	}

	/**
	 * @param $name
	 * @return Control
	 */
	protected function createComponentPaginator($name): Control
	{
		$paginator = new Control();

		$paginator->setTemplateFile('default.latte');
		$paginator->disableAjax();

		return $paginator;
	}

	/**
	 * @return array|Article[]
	 */
	protected function getArticles(): array
	{
		$paginator = $this->getPaginator();

		return $this->articlesManager->getPublishedArticlesByDateDesc(
			$paginator->getItemsPerPage(),
			$paginator->getOffset()
		);
	}
}
