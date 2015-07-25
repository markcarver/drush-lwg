<?php
namespace Drush\Command;

/**
 * Class CommandBase
 * @package Drush\Command
 */
class CommandBase implements CommandInterface {

  protected $color;

  /**
   * The command information from composer.json.
   *
   * @var array
   */
  protected $info;

  /**
   * The path to the command.
   *
   * @var string
   */
  protected $path;

  /**
   * The path to the command templates.
   *
   * @var string
   */
  protected $templatePath;

  /**
   * @var \Twig_Environment
   */
  protected $twig;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->color = new \Console_Color2();
    $this->path = DRUSH_LWG_DIR;
    $this->templatePath = file_exists("$this->path/templates") ? "$this->path/templates" : $this->path;
    $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem($this->templatePath));
  }

  public function clearScreen() {
    if (drush_verify_cli()) {
      drush_print("\033c");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {}

  public function ascii($string, $color = NULL, $style = NULL, $background = NULL) {
    if (drush_get_context('DRUSH_NOCOLOR')) {
      return $string;
    }
    return $this->color->color($color, $style, $background) . $string . $this->color->color('reset');
  }

  /**
   * @todo document
   */
  public function getInfo($property = NULL) {
    if (!isset($this->info)) {
      $this->info = array();
      if (file_exists("$this->path/composer.json")) {
        $this->info = drush_json_decode(file_get_contents("$this->path/composer.json"));
        if (!$this->info) {
          $this->info = array();
        }
      }
    }
    $this->info['generation_date'] = date('c');
    if (isset($property)) {
      return isset($this->info[$property]) ? $this->info[$property] : FALSE;
    }
    return $this->info;
  }

  /**
   * {@inheritdoc}
   */
  static function help() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function init() {}

  /**
   * {@inheritdoc}
   */
  public function validate() {}

  /**
   * @todo document
   */
  public function render($file, $data = array()) {
    $template = $this->twig->loadTemplate($file);
    return $template->render($data ?: $this->getInfo());
  }

}
