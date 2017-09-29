<?php

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\AuthenticationException;

class LoginPresenter extends BasePresenter
{
	public function __construct(Container $container)
	{
		parent::__construct($container);
	}

	protected function authCheck(): BasePresenter
	{
		if ($this->getAction() !== 'logout' && $this->getUser()->isLoggedIn()) {
			// when user is logged in, proceed to articles management
			$this->redirect('Articles:default');
		}

		return $this;
	}

	public function actionLogout(): void
	{
		$this->getUser()->logout(true);

		$this->redirect('default');

		return;
	}

	protected function createComponentLoginForm(string $name): Form
	{
		$form = new Form($this, $name);

		$form->addHidden('back', trim($this->getParameter('back')));

		$loginInput = $form->addText('login', 'Login');
		$loginInput->addRule(Form::FILLED, 'Zadejte login!');

		$passwordInput = $form->addPassword('password', 'Heslo');
		$passwordInput->addRule(Form::FILLED, 'Zadejte heslo!');

		$okButton = $form->addSubmit('ok', 'Přihlásit');
		$okButton->onClick[] = [$this, 'loginOkClicked'];
		$okButton->onInvalidClick[] = [$this, 'loginOkClickedInvalid'];

		return $form;
	}

	public function loginOkClicked(SubmitButton $button): void
	{
		$values = $button->getForm()->getValues();

		try {
			$this->getUser()->login($values['login'], $values['password']);

			// @TODO: login expiration

			if (trim($values['back']) !== '') {
				$this->redirectUrl($values['back']);
			} else {
				$this->redirect('Articles:default');
			}
		} catch (AuthenticationException $ex) {
			$this->flashMessage('Přihlášení se nezdařilo', 'warning');
			$this->redrawControl('snLoginForm');
		}

		return;
	}

	public function loginOkClickedInvalid(): void
	{
		$this->redrawControl('snLoginForm');

		return;
	}
}
