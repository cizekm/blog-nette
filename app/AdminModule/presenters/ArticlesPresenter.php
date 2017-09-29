<?php

namespace App\AdminModule\Presenters;

use App\Managers\ArticlesManager;
use App\Managers\TagsManager;
use App\Models\Article;
use App\Models\Tag;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\IControl;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\DataGrid;

class ArticlesPresenter extends BasePresenter
{
	/** @var ArticlesManager */
	protected $articlesManager = null;

	/** @var TagsManager */
	protected $tagsManager = null;

	public function __construct(Container $container, ArticlesManager $articlesManager, TagsManager $tagsManager)
	{
		parent::__construct($container);

		$this->articlesManager = $articlesManager;
		$this->tagsManager = $tagsManager;
	}

	public function actionEdit(int $id): void
	{
		$article = $this->articlesManager->getArticleById($id);

		if (!$article) {
			$this->flashMessage(sprintf('Článek s ID %d nebyl nalezen!', $id), 'warning');

			$back = trim($this->getParameter('back'));
			if ($back === '') {
				$this->redirect('default');
			} else {
				$this->redirectUrl($back);
			}
		}

		$this->template->article = $article;

		$defaults = [
			'id' => $article->getId(),
			'title' => $article->getTitle(),
			'url' => $article->getUrl(),
			'publishedTimestamp' => $article->getPublishedTimestamp()->format('Y-m-d H:i:s'),
			'visible' => $article->isVisible(),
			'content' => $article->getContent(),
			'tags' => implode(', ', $article->getTags()->map(function (Tag $tag) {
				return $tag->getTitle();
			})->toArray())
		];

		$this['articleForm']->setDefaults($defaults);
	}

	public function renderAdd(): void
	{

	}

	/**
	 * @param string $name
	 * @return Form
	 */
	protected function createComponentArticleForm(string $name): Form
	{
		$form = new Form($this, $name);

		$form->addHidden('back', trim($this->getParameter('back')));

		$idElement = $form->addHidden('id');

		$titleElement = $form->addText('title', 'Nadpis článku');
		$titleElement->addRule(Form::FILLED, 'Zadejte nadpis článku')
			->addRule(Form::MAX_LENGTH, 'Nadpis článku může obsahovat maximálně %d znaků!', 150);

		$urlElement = $form->addText('url', 'URL');
		$urlElement->addCondition(Form::FILLED)
			->addRule(
				function (IControl $control) use ($idElement) {
					return !$this->articlesManager->urlExists($control->getValue(), $idElement->getValue());
				},
				'Článek s URL %value již existuje!'
			);
		$urlElement->getControlPrototype()->setPlaceholder('vygenerovat automaticky');

		$publishedTimestampElement = $form->addText('publishedTimestamp', 'Datum a čas zveřejnění');
		$publishedTimestampElement->setDefaultValue(new DateTime('now'));

		$form->addCheckbox('visible', 'Zveřejnit')->setDefaultValue(true);

		$contentElement = $form->addTextArea('content');
		$contentElement->getControlPrototype()->addClass('editor');
		$contentElement->addRule(Form::FILLED, 'Zadejte text článku!');

		$form->addText('tags', 'Tagy (oddělené čárkou)');

		$okButton = $form->addSubmit('ok', 'Uložit článek');
		$okButton->onClick[] = [$this, 'okClicked'];
		$okButton->onInvalidClick[] = [$this, 'okClickedInvalid'];

		$cancelButton = $form->addSubmit('cancel', 'Zahodit změny');
		$cancelButton->setValidationScope(false);
		$cancelButton->onClick[] = [$this, 'cancelClicked'];

		return $form;
	}

	/**
	 * @param SubmitButton $button
	 */
	public function okClicked(SubmitButton $button): void
	{
		$values = $button->getForm()->getValues();

		if (trim($values['id']) === '' || !($article = $this->articlesManager->getArticleById($values['id']))) {
			$article = new Article();
		}

		$articleUrl = trim($values['url']);
		if ($articleUrl === '') {
			$articleUrl = trim(Strings::webalize($values['title']));
		}
		$articleUrl = $this->articlesManager->getUniqueUrl($articleUrl, $article->getId());

		$article->setTitle($values['title']);
		$article->setUrl($articleUrl);
		$article->setContent($values['content']);
		$article->setPublishedTimestamp(DateTime::from($values['publishedTimestamp']));
		$article->setVisible($values['visible']);

		$article->removeTags();

		foreach (explode(',', $values['tags']) as $tagTitle) {
			$tagTitle = trim($tagTitle);
			if (trim($tagTitle) !== '') {
				$tag = $this->tagsManager->getTagByTitle($tagTitle);
				if ($tag === null) {
					$tag = new Tag();
					$tag->setTitle($tagTitle);
					$tag->setUid(trim(Strings::webalize($tagTitle)));
				}

				$this->tagsManager->saveTag($tag, false);

				$article->addTag($tag);
			}
		}

		try {
			$this->articlesManager->saveArticle($article);
		} catch (\Exception $ex) {
			$this->flashMessage('Při ukládání článku došlo k chybě', 'error');
			$this->redrawControl('snArticleForm');

			return;
		}

		$this->flashMessage('Článek byl úspěšně uložen', 'success');

		if (trim($values['back']) !== '') {
			$this->redirectUrl($values['back']);
		} else {
			$this->redirect('default');
		}

		return;
	}

	public function okClickedInvalid(): void
	{
		$this->redrawControl('snArticleForm');

		return;
	}

	/**
	 * @param SubmitButton $button
	 */
	public function cancelClicked(SubmitButton $button): void
	{
		$values = $button->getForm()->getValues();

		if (trim($values['back']) !== '') {
			$this->redirectUrl($values['back']);
		} else {
			$this->redirect('default');
		}

		return;
	}

	/**
	 * @param string $name
	 * @return DataGrid
	 */
	protected function createComponentGrid(string $name): DataGrid
	{
		$grid = new DataGrid($this, $name);

		$grid->setDataSource($this->createGridDataSource());

		$grid->addColumnLink('title', 'Nadpis článku', 'edit')->addParameters(['back' => $this->link('this')])
			->setSortable(true)
			->setFilterText();

		$grid->addColumnDateTime('publishedTimestamp', 'Datum a čas zveřejnění')
			->setSortable(true);

		$grid->addColumnNumber('viewsCnt', 'Počet zobrazení')
			->setSortable(true);

		$grid->addColumnStatus('visible', 'Viditelný')
			->setOptions([1 => 'Ano', 0 => 'Ne'])
			->onChange[] = function (int $id, int $newValue) {
				if ($article = $this->articlesManager->getArticleById($id)) {
					$article->setVisible((bool)$newValue);
					$this->articlesManager->saveArticle($article);
				}
			};


		return $grid;
	}

	/**
	 * @return QueryBuilder
	 */
	protected function createGridDataSource(): QueryBuilder
	{
		return $this->articlesManager->createQueryBuilder('a');
	}
}
