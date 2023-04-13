<?php
/*-------------------------------------------------------+
| L10n Custom Translation (MO-File) Injection            |
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

require_once 'l10nmo.civix.php';
use CRM_L10nmo_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function l10nmo_civicrm_config(&$config) {
  _l10nmo_civix_civicrm_config($config);

  // enable injection
  require_once 'CRM/L10nmo/Injector.php';
  \Civi::dispatcher()->addSubscriber(new CRM_L10nmo_Injector());
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function l10nmo_civicrm_install() {
  _l10nmo_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function l10nmo_civicrm_enable() {
  _l10nmo_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function l10nmo_civicrm_navigationMenu(&$menu) {
  _l10nmo_civix_insert_navigation_menu($menu, 'Administer/Localization', array(
    'label' => E::ts('Custom Translation Files'),
    'name' => 'l10nmo_config',
    'url' => 'civicrm/l10nx/custom',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _l10nmo_civix_navigationMenu($menu);
}
