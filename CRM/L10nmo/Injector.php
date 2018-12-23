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
  public function amend_mo_files($locale, $domain, $context, $mo_files) {
    // TODO: inject
    return (array) $mo_files;
    // return (array) $mo_files;
  }

}
