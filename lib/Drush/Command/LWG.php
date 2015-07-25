<?php

namespace Drush\Command;

use Drush\LWG\Notice;
use Drush\LWG\Project;

/**
 * Class LWG
 * @package Drush\Command
 */
class LWG extends CommandBase {

  /**
   * @var Notice
   */
  protected $notice = FALSE;

  /**
   * @var Project
   */
  protected $project = FALSE;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->project = $this->getProject();
    $this->notice = $this->getNotice();
  }

  /**
   * @return \Drush\LWG\Notice
   */
  public function getNotice() {
    if (!$this->notice) {
      $this->notice = new Notice($this);
    }
    return $this->notice;
  }

  /**
   * @return \Drush\LWG\Project
   */
  public function getProject() {
    if (!$this->project) {
      $this->project = new Project($this);
    }
    return $this->project;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    if (!$this->project->isValid() || !$this->notice->exists()) {
      return FALSE;
    }
    return call_user_func_array(array(get_parent_class(), 'validate'), func_get_args());
  }

}
