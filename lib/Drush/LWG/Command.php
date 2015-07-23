<?php

namespace Drush\LWG;

use Drush\Command\CommandInterface;

/**
 * Class Command
 * @package Drush\LWG
 */
class Command implements CommandInterface {

  /**
   * The current action to perform.
   *
   * @var string
   */
  private $action = 'list';

  /**
   * The information from the command's composer.json file.
   *
   * @var array
   */
  private $info ;

  /**
   * @var Notice
   */
  private $notice = FALSE;

  /**
   * The path to the command.
   *
   * @var string
   */
  private $path;

  /**
   * @var Project
   */
  private $project = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __construct($path) {
    $this->path = $path;
  }

  /**
   * {@inheritdoc}
   */
  public function info() {
    $info['lwg'] = array(
      'bootstrap' => DRUSH_BOOTSTRAP_NONE,
      'description' => 'Manage external assets in a project for use with Drupal Licensing Working Group.',
      'aliases' => array(
        'notice',
      ),
      'arguments' => array(
        'action' => 'An action to perform: add, approve, edit, list, remove',
      ),
      'options' => array(
        'path' => 'The working directory of the project to operate from, defaults to the current working directory if not specified.',
      ),
      'topics' => array('docs-lwg-notice'),
    );
    $info['docs-lwg'] = array(
      'description' => 'README.md for the lwg command.',
      'hidden' => TRUE,
      'topic' => TRUE,
      'bootstrap' => DRUSH_BOOTSTRAP_NONE,
      'callback' => 'drush_print_file',
      'callback arguments' => array("$this->path/README.md"),
      'aliases' => array(
        'docs-notice',
      ),
    );
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public function execute($action = NULL) {
    if ($action && !$this->getAction($action)) {
      return FALSE;
    }

    // Determine the method to invoke.
    $method = "action_$this->action";
    if (method_exists($this, $method)) {
      return call_user_func_array(array($this, $method), func_get_args());
    }
    return drush_set_error('DRUSH_LWG_UNKNOWN_CLASS_METHOD', dt('Unknown class method: @class::@method', array(
      '@class' => get_called_class(),
      '@method' => $method,
    )));
  }

  /**
   * {@inheritdoc}
   */
  public function init($action = NULL) {
    // Only execute command if the installed version of Drush is supported.
    $supported_versions = array(7);
    if (!defined('DRUSH_MAJOR_VERSION') || !in_array(DRUSH_MAJOR_VERSION, $supported_versions)) {
      $info = parse_ini_file("$this->path/lwg_notice.info");
      return drush_set_error('DRUSH_VERSION_NOT_SUPPORTED', dt("The Drush LWG Notice command (!COMMAND_VERSION) only supports the following major Drush version(s): !SUPPORTED_VERSIONS\nThe currently installed Drush version is: !DRUSH_VERSION\nFor more details, please visit: !url\n", array(
        '!COMMAND_VERSION' => $info && !empty($info['version']) ? $info['version'] : 'unknown version',
        '!SUPPORTED_VERSIONS' => implode(', ', $supported_versions),
        '!DRUSH_VERSION' => DRUSH_VERSION,
        '!url' => '@todo insert URL to lwg_notice command.',
      )));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate($action = NULL) {
    // Validate the action to perform.
    if ($action && !$this->getAction($action)) {
      return FALSE;
    }
    // Validate the project.
    $project = $this->getProject();
    if (!$project->isValid()) {
      return FALSE;
    }

    // Create a new instance of Notice.
    $notice = $this->getNotice();
    if (!$notice->exists()) {
      return FALSE;
    }
  }

  /**
   * Retrieve an action to perform.
   *
   * @param string $action
   *   Optional. The action to validate or set.
   * @param bool $set
   *   If $action is provided and is valid, will store this as the new
   *   "current" action to perform.
   *
   * @return string | FALSE
   *   A valid action to perform or FALSE otherwise.
   */
  public function getAction($action = NULL, $set = TRUE) {
    if ($action) {
      if (in_array(strtolower($action), array('add', 'approve', 'edit', 'list', 'remove'))) {
        if ($set) {
          $this->action = $action;
        }
      }
      else {
        return drush_set_error('DRUSH_LWG_INVALID_ACTION', dt('Unknown action "!action". See `drush lwg-notice --help` for a list of valid actions.', array(
          '!action' => $action,
        )));
      }
    }
    return $this->action;
  }

  /**
   * @todo document
   */
  public function getBanner() {
    $content = '';
    $banner = "$this->path/banner.yml";
    if (file_exists($banner)) {
      $content = file_get_contents($banner);
      // @todo replace with twig at some point maybe?
      $content = str_replace('{{ command.date }}', date('c'), $content);
      $content = str_replace('{{ command.url }}', $this->getInfo('homepage'), $content);
    }
    return $content;
  }

  /**
   * @todo document
   */
  public function getInfo($property = NULL) {
    if (!isset($this->info)) {
      if (file_exists("$this->path/composer.json")) {
        $this->info = drush_json_decode(file_get_contents("$this->path/composer.json"));
      }
    }
    $info = is_array($this->info) ? $this->info : array();
    if (isset($property)) {
      return !empty($info) && isset($info[$property]) ? $info[$property] : FALSE;
    }
    return $info;
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

  protected function action_list() {
    drush_log($this->path, 'ok');
//      $data = Yaml::parse($this->file);
//      $contents = Yaml::dump($data, 4, 2, FALSE, TRUE);
//      file_put_contents($this->file, $contents);

  }

}
