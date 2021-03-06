<?php

class CtrlAfishaDefault extends CtrlThemeFourDd {

  protected function themeFourModule() {
    return 'afisha';
  }

  protected function paramFilterN() {
    return 0;
  }

  protected function paramFilterDateField() {
    return 'eventDate';
  }

  protected function init() {
    parent::init();
    $this->d['submenu'] = $this->extendByBasePath([
      [
        'title' => 'Добавлено сегодня',
        'link' => 'd.'.date('j').';'.date('n').';'.date('Y').'.dateCreate'
      ]
    ]);
    $this->d['sectionTitle'] = 'События';
  }

  function oProcessForm(Form $form) {
    if ($this->action == 'json_new') return;
    $form->options['deleteFileUrl'] = $this->originalReq()->path().'?id='.$this->id().'&a=deleteFile';
  }

  protected function initCalendar() {
    $calendar = new AfishaCalendar($this->d['basePath']);
    $calendar->selectedMonth = $this->month;
    $calendar->selectedYear = $this->year;
    $calendar->selectedDay = $this->day;
    $this->d['calendar'] = $calendar->getMonthView($this->month ?: date('n'), $this->year ?: date('Y'));
  }

  function action_default() {
    parent::action_default();
    $this->d['blocksTpl'] = 'afisha/blocks';
    $this->d['contentTpl'] = 'afisha/list';
    $ddo = new DdoFour($this->getStrName(), 'siteItems');
    $ddo->setPagePath($this->d['basePath']);
    $ddo->disallowEmpties = ['price', 'text'];
    $ddo->groupFrom('title');
    $items = $this->items();
    $items->setN(100);
    $this->d['today'] = $this->setFilterDate(date('j;n;Y'), 'eventDate', true);
    $items->cond->setOrder('eventDate DESC, eventTime');
    $_items = $items->getItems();
    $this->d['pNums'] = $items->pNums;
    $this->d['html'] = count($_items) ? $ddo->setItems($_items)->els() : '<div class="noItems">событий нет</div>';
    $this->d['tpl'] = 'bookmarkContent';
    $this->initCalendar();
    if ($this->day) {
      $cnt = ' ('.count($_items).' шт.)';
      if (in_array('dateCreate', $this->items->cond->filterKeys) and $this->day == date('j') and $this->month == date('n') and $this->year == date('Y')) {
        $this->setPageTitle('Добавлено сегодня'.$cnt);
      }
      else {
          if ($this->d['today']) {
              $this->setPageTitle('Сегодня '.$cnt);
          } else {
              $this->setPageTitle($this->day.' '.Config::getVar('ruMonths2')[$this->month].' '.$this->year.$cnt);
          }
      }
    }
    elseif ($this->month) {
      $this->setPageTitle(Config::getVar('ruMonths')[$this->month].' '.$this->year);
    }
  }

  function action_item() {
    $this->d['layout'] = 'cols2';
    $this->d['tpl'] = 'afisha/item';
    $item = $this->items()->getItem($this->req->param(1));
    if (!$item) throw new Error404;
    $this->d['html'] = (new Ddo($this->getStrName(), 'siteItem'))->setItem($item)->els();
    list($this->d['html'], $image) = Html::removeTag($this->d['html'], 'div', ['class', 'f_image']);
    $this->d['blocksTpl'] = new TtTpl($this->tt, $this->d, ['html' => $image]);
    $this->d['item'] = $item;
    $this->setPageTitle($item['title']);
    $this->setPageHeadTitle($item['title'].' — '.Date::str($item['dateCreate_tStamp']));
  }

}