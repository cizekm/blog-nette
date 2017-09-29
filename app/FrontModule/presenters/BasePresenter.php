<?php

namespace App\FrontModule\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

abstract class BasePresenter extends Presenter
{
	/** @var string */
	protected $baseUrl = null;

	public function __construct(Container $container)
	{
		parent::__construct();

		$this->baseUrl = $container->getParameters()['baseUrl'] ?? '/';
	}

	protected function throw404()
	{
		throw new BadRequestException();
	}

	protected function beforeRender(): void
	{
		parent::beforeRender();

		$this->template->baseUrl = $this->baseUrl;
	}
}
