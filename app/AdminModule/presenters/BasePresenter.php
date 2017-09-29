<?php

namespace App\AdminModule\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

abstract class BasePresenter extends Presenter
{
	/** @var string */
	protected $baseUrl = null;

	protected function startup()
	{
		parent::startup();

		$this->authCheck();
	}

	protected function authCheck(): self
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Login:default', ['back' => $this->link('this')]);
		}

		return $this;
	}

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
