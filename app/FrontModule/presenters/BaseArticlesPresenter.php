<?php

namespace App\FrontModule\Presenters;

use App\Managers\ArticlesManager;
use Nette\DI\Container;

abstract class BaseArticlesPresenter extends BasePresenter
{
	/** @var ArticlesManager */
	protected $articlesManager = null;

	/** @var string */
	protected $timestampFormat = null;

	public function __construct(Container $container, ArticlesManager $articlesManager)
	{
		parent::__construct($container);

		$this->articlesManager = $articlesManager;
		$this->timestampFormat = $container->getParameters()['articles']['timestampFormat'] ?? 'j.n.Y';
	}

	protected function beforeRender(): void
	{
		parent::beforeRender();

		$this->template->timestampFormat = $this->timestampFormat;
	}
}
