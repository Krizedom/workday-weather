<?php

namespace App\Presenters;

use Nette;


class HomepagePresenter extends Nette\Application\UI\Presenter
{

    /** @var \App\Model\WeatherManager @inject */
    public $weatherManager;

    /**
     * Search bar component
     *
     * @return Nette\Application\UI\Form
     */
    protected function createComponentLocationSearchBar()
    {
        $form = new Nette\Application\UI\Form;
        $form->setMethod('GET');
        $form->addText('location', 'Lokace:')
            ->setRequired()
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('send', 'Vyhledat')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'locationSearchBarSucceeded'];
        return $form;
    }

    /**
     * Search bar success action
     *
     * @param Nette\Application\UI\Form $form
     * @param $values form values
     * @throws Nette\Application\AbortException
     */
    public function locationSearchBarSucceeded($form, $values)
    {
        $query = $values->location;

        $this->redirect('Homepage:detail', $query);
    }

    /**
     * Default page render
     */
    public function renderDefault()
    {
        $locationSearchBar = $this->createComponentLocationSearchBar();

        $this->template->locationSearchBar = $locationSearchBar;
        $this->template->title = 'Počasí';
    }

    /**
     * Detail page render
     *
     * @param string $query query for location to be found
     * @throws Nette\Application\AbortException
     * @throws \Throwable
     */
    public function renderDetail($query)
    {
        $locationSearchBar = $this->createComponentLocationSearchBar();

        $location = NULL;
        $weather = NULL;

        try {
            $location = $this->weatherManager->getLocation($query);
            $weather = $this->weatherManager->getWeather($location);
        } catch (\Exception $e) {
            $this->flashMessage($e->getMessage());
            $this->redirect('Homepage:default');
        }

        $this->template->query = $query;
        $this->template->locationSearchBar = $locationSearchBar;
        $this->template->weather = $weather;
        $this->template->title = 'Počasí - ' . $query;
    }
}
