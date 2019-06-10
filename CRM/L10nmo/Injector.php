<?php
/*-------------------------------------------------------+
| L10n Profiling Extension                               |
| Copyright (C) 2018 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use Civi\API\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Civi\Core\Event\GenericHookEvent;

use CRM_L10nmo_ExtensionUtil as E;

/**
 * Injects custom MO files according to the configuration
 */
class CRM_L10nmo_Injector implements EventSubscriberInterface {

  /**
   * @var array [locale][domain][context] cache of the mo-files array
   */
  protected $cache = [];

  /**
   * @var array holds the configuration as specified in the form
   */
  protected $config = NULL;

  public function __construct() {
    $this->cache = [];
    $this->config = CRM_L10nmo_Form_Configuration::getConfiguration();
    if (!is_array($this->config)) {
      $this->config = [];
    }
  }

  /**
   * Define which events we subscribe to
   * @return array
   */
  public static function getSubscribedEvents() {
    return array(
        'civi.l10n.custom_mo' => array(
            array('injectMO', Events::W_MIDDLE),
        ),
    );
  }

  /**
   * Inject custom MO files according to the configuration
   *
   * @param GenericHookEvent $ts_event mo event
   */
  public function injectMO(GenericHookEvent $ts_event) {
    $locale  = $ts_event->locale;
    $context = empty($ts_event->context) ? 'None' : $ts_event->context;
    $domain  = $ts_event->domain;

    // postprocess domain
    if (is_array($domain)) {
      $domain = reset($domain);
    }
    if (empty($domain)) {
      $domain = 'civicrm';
    }

    // fill cache
    if (!isset($this->cache[$locale][$domain][$context])) {
      $this->cache[$locale][$domain][$context] = $this->amend_mo_files($locale, $domain, $context, $ts_event->mo_file_paths);
    }

    $ts_event->mo_file_paths = $this->cache[$locale][$domain][$context];
  }

  /**
   * @param $locale
   * @param $domain
   * @param $context
   * @param $mo_files
   * @return mixed
   */
  public function amend_mo_files($locale, $domain, $context, $given_mo_files) {
    $my_mo_files = [];

    // iterate through our items
    foreach ($this->config as $config) {
      // check of config active
      if (empty($config['active'])) {
        // config disabled
        continue;
      }

      // check the domain
      if (!empty($config['domains']) && !in_array($domain, $config['domains'])) {
        // no match
        continue;
      }

      // check the locale
      if (!empty($config['locales']) && !in_array($locale, $config['locales'])) {
        // no match
        continue;
      }


      if ($config['type'] == 'f') {
        if (!empty($config['path'])) {
          $my_mo_files[] = $config['path'];
        }

      } else {
        // this is a "pack" configuration:
        if (!empty($config['path'])) {
          $file = $config['path'] . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . 'civicrm.mo';
          if (file_exists($file)) {
            $my_mo_files[] = $file;
          }
        }
      }
    }

    // add the existing ones
    foreach ($given_mo_files as $given_mo_file) {
      $my_mo_files[] = $given_mo_file;
    }

    return $my_mo_files;
  }

}
