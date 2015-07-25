<?php

namespace Drush\LWG;

/**
 * Class Cache
 * @package Drush\LWG
 */
class Cache extends \ArrayObject {

  /**
   * The cache ID.
   *
   * @var string
   */
  protected $cid;

  protected $exists = FALSE;

  /**
   * Class constructor.
   *
   * @param string $cid
   *   The cache ID.
   * @param array $input
   *   The input parameter accepts an array.
   * @param int $flags
   *   Flags to control the behaviour of the ArrayObject object.
   * @param string $iterator_class
   *   Specify the class that will be used for iteration of the ArrayObject
   *   object. ArrayIterator is the default class used.
   */
  public function __construct($cid, array $input = NULL, $flags = 0, $iterator_class = "ArrayIterator") {
    $this->cid($cid);
    if (isset($input)) {
      $this->set($input);
      $this->exists = TRUE;
    }
    elseif (($cache = drush_cache_get($this->cid(), 'lwg')) && isset($cache->data)) {
      $this->exchangeArray($cache->data);
      $this->exists = TRUE;
    }
  }

  /**
   * @todo document
   */
  public function cid($cid = NULL) {
    if ($cid) {
      $this->cid = $cid;
    }
    return $this->cid;
  }

  /**
   * @todo document
   */
  public function compare(array $array = array()) {
    return array_intersect_assoc($this->get(), $array);
  }

  /**
   * @todo document
   */
  public function exists() {
    return $this->exists;
  }

  /**
   * @todo document
   */
  public function get() {
    return $this->getArrayCopy();
  }

  /**
   * @todo document
   */
  public function set(array $data = NULL, $save = TRUE) {
    if (isset($data)) {
      $this->exchangeArray($data);
      if ($save) {
        $this->save();
      }
    }
    return $data;
  }

  /**
   * @todo document
   */
  public function save() {
    drush_cache_set($this->cid(), $this->getArrayCopy(), 'lwg');
  }

}
