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

use CRM_L10nmo_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_L10nmo_Form_Configuration extends CRM_Core_Form {

  protected $domains = NULL;
  protected $locales = NULL;


  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts("Configure Custom Translation Files"));

    $configuration = CRM_L10nmo_Configuration::getConfiguration(TRUE);
    $domains = $this->getDomains();
    $locales = $this->getLocales();
    foreach ($configuration as $i => $config) {
      $this->add('hidden', "info_{$i}", json_encode($config));

      $this->add(
          'checkbox',
          "active_{$i}",
          E::ts("Active?")
      );

      $this->add(
          'select',
          "domain_{$i}",
          E::ts("Domain"),
          $domains,
          FALSE,
          ['class' => 'crm-select2', 'multiple' => 'multiple', 'placeholder' => E::ts("all domains")]
      );

      $this->add(
          'select',
          "locale_{$i}",
          E::ts("Locales"),
          $locales,
          FALSE,
          ['class' => 'crm-select2', 'multiple' => 'multiple', 'placeholder' => E::ts("all locales")]
      );

      // set default values
      $this->setDefaults([
          "active_{$i}" => $config['active'],
          "domain_{$i}" => $config['domains'],
          "locale_{$i}" => $config['locales'],
      ]);
    }

    // infrastructure
    $this->assign('lines', $configuration);
    $this->add('hidden', 'l10nx_command', '');

    $this->addButtons([
      [
          'type'      => 'submit',
          'name'      => E::ts('Save'),
          'isDefault' => TRUE,
      ],
      [
          'type'      => 'upload',
          'icon'      => 'fa-plus-circle',
          'name'      => E::ts('Upload More'),
          'isDefault' => FALSE,
      ],
    ]);

    parent::buildQuickForm();
  }


  public function postProcess() {
    $values = $this->exportValues();

    if (isset($values['_qf_Configuration_upload'])) {
      // this is the upload button
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/l10nx/custom_upload', 'reset=1'));
    }

    // compile configuration
    $configuration = [];
    $i = 0;
    while (isset($values["info_{$i}"])) {
      $info            = json_decode($values["info_{$i}"], TRUE);
      $configuration[] = [
          'path'    => $info['path'],
          'type'    => $info['type'],
          'name'    => $info['name'],
          'file_id' => $info['file_id'],
          'active'  => empty($values["active_{$i}"]) ? '0' : '1',
          'domains' => $values["domain_{$i}"],
          'locales' => $values["locale_{$i}"],
      ];
      $i += 1;
    }

    // exec command
    $redirect_url = $this->executeCommand($values['l10nx_command'], $configuration);

    // store settings
    CRM_Core_BAO_Setting::setItem($configuration, 'de.systopia.l10nmo', 'l10nmo_config');

    // redirect if requested
    if ($redirect_url) {
      CRM_Utils_System::redirect($redirect_url);
    }

    parent::postProcess();
  }




  //  HELPERS

  /**
   * Execute any command passed to with the submission
   *
   * @param $action        string action command
   * @param $configuration array current configuration before saving
   * @return string URL for redirect, if the command asks for it
   */
  protected function executeCommand($action, &$configuration) {
    if (empty($action)) {
      return NULL;
    }

    // split command
    list($command, $index) = explode(':', $action, 2);
    $new_index = NULL;
    switch ($command) {
      case 'first':
        $new_index = 0;
        break;

      case 'up':
        $new_index = max(0, $index-1);
        break;

      case 'down':
        $new_index = min(count($configuration)-1, $index+1);
        break;

      case 'last':
        $new_index = count($configuration)-1;
        break;

      case 'delete':
        CRM_L10nmo_Configuration::deleteData($configuration[$index]);
        array_splice($configuration, $index, 1);
        CRM_Core_Session::setStatus(E::ts("Translation file(s) deleted."), E::ts("Success"), 'info');
        return CRM_Utils_System::url('civicrm/l10nx/custom', 'reset=1');
        break;

      case 'update':
        return CRM_Utils_System::url('civicrm/l10nx/custom_upload', 'reset=1&update=' . $configuration[$index]['file_id']);
        break;

      default:
        // Unknown command
        return NULL;
    }

    // process order changes
    if ($new_index !== NULL) {
      // copied from https://stackoverflow.com/questions/12624153/move-an-array-element-to-a-new-index-in-php
      $out = array_splice($configuration, $index, 1);
      array_splice($configuration, $new_index, 0, $out);
      return CRM_Utils_System::url('civicrm/l10nx/custom', 'reset=1');
    }
  }



  /**
   * Get available domains
   *
   * @return array|null
   */
  protected function getDomains() {
    if ($this->domains === NULL) {
      $this->domains = [];
      $this->domains['civicrm'] = E::ts("civicrm: Main CiviCRM");
      $this->domains['civicrm-options'] = E::ts("civicrm: Options");
      $this->domains['civicrm-data'] = E::ts("civicrm: User Data");

      // add extensions
      try {
        $mapper = CRM_Extension_System::singleton()->getMapper();
        $modules = $mapper->getAllInfos();
        foreach ($modules as $minfo) {
          $this->domains[$minfo->file]  = "{$minfo->file}: {$minfo->label}";
        }
      } catch (Exception $ex) {
        // TODO: error handling
      }
    }
    return $this->domains;
  }

  /**
   * Get available locales
   *
   * @return array|null
   */
  protected function getLocales() {
    if ($this->locales === NULL) {
      $this->locales = CRM_Core_I18n::languages(FALSE);
    }
    return $this->locales;
  }
}
