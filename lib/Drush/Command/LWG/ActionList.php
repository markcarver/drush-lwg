<?php

namespace Drush\Command\LWG;

use Drush\Command\LWG;
use Drush\LWG\Asset;

/**
 * Class ActionList
 * @package Drush\Command\LWG
 */
class ActionList extends LWG {

  const ALL_LIST_LIMIT = 2;

  protected $assets = array();
  protected $count = 0;

  /**
   * {@inheritdoc}
   */
  public function execute($name = NULL) {
    if (empty($this->assets)) {
      return;
    }

    $options = array_keys($this->assets);
    $options[] = dt('All');
    $choice = 'all';

    if ($this->count > 2 && !drush_get_option('all')) {
      $choice = drush_choice($options, dt('Choose which asset from @name to view:', array(
        '@name' => $this->project->getInfo('name'),
      )));
    }
    else {
      drush_print(dt("Number of @name asset(s): @count\n", array(
        '@name' => $this->project->getInfo('name'),
        '@count' => $this->count,
      )));

    }
    if ($choice === FALSE) {
      return;
    }
    elseif ($choice === $this->count) {
      $this->clearScreen();
      $choice = 'all';
    }
    if ($choice !== FALSE && $choice !== 'all') {
      $this->clearScreen();
      $this->printAsset($options[$choice]);
      $this->execute($name);
    }
    elseif ($this->count || $choice === 'all') {
      foreach ($this->notice as $key => $asset) {
        $this->printAsset($key);
      }
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    call_user_func_array(array(get_parent_class(), 'init'), func_get_args());
    $this->assets = $this->notice->getAssets(FALSE, TRUE);
    $this->count = count($this->assets);
    if ($this->count > self::ALL_LIST_LIMIT) {
      $this->clearScreen();
    }
  }

  /**
   * @todo document
   */
  public function printAsset($asset) {
    if (is_string($asset)) {
      $asset = isset($this->assets[$asset]) ? $this->assets[$asset] : FALSE;
    }
    if ($asset instanceof Asset) {
      $width = drush_get_context('DRUSH_COLUMNS', 80);
      if ($width > 80) {
        $width = 80;
      }
      drush_print(dt("@name\n!separator\n!table", array(
        '@name' => $asset->getName(),
        '!separator' => str_repeat('_', $width),
        '!table' => $asset->table(),
      )));
    }

  }

}
