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

define("L10NMO_CONFIG_COUNT", 5);

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_L10nmo_Form_Configuration extends CRM_Core_Form {

  protected $domains = NULL;
  protected $files   = NULL;
  protected $packs   = NULL;
  protected $locales = NULL;

  /**
   * Get the folder where the custom translations are stored.
   *
   * @param bool $packs
   */
  public static function getCustomTranslationFolder($packs = FALSE) {
    if ($packs) {
      $folder = E::path('mo_store/mopacks');
    } else {
      $folder = E::path('mo_store/mofiles');
    }
    return $folder;
  }

  /**
   * Get the current configuration
   * Each entry in the array has the following entries:
   *  'type': 'f', 'p' (file or pack)
   *  'path': path relative to mo_store
   *  'active': true/false
   *  'domains': list of domains this applies to
   *  'locales': list of locales this applies to
   *  'file_id': ID of a civicrm_file
   *
   * @param $include_new_files boolean add new, unused files
   * @return array list of custom translations
   */
  public static function getConfiguration($include_new_files = FALSE) {
    $configuration = CRM_Core_BAO_Setting::getItem( 'de.systopia.l10nmo', 'l10nmo_config');
    if (!is_array($configuration)) {
      $configuration = [];
    }

    // TODO: testing - remove
    $configuration = [];

    if ($include_new_files) {
      $file_query = civicrm_api3('File', 'get', [
          'mime_type'    => 'application/x-gettext-translation',
          'option.limit' => 0,
          'sequential'   => 0,
      ]);
      $files = $file_query['values'];
      $missing_files = $files;

      // match files to the configuration
      foreach ($configuration as &$config) {
        $file_id = $config['file_id'];
        if (isset($files[$file_id])) {
          $file = $files[$file_id];
          $config['description'] = $file['description'];
          $config['upload_date'] = $file['upload_date'];
          $config['created_id']  = $file['created_id'];
          unset($missing_files[$file_id]);
        } else {
          // TODO: file is missing -> clean up!
        }
      }

      // add missing files
      foreach ($missing_files as $file) {
        if (substr($file['uri'], 0, 8) == 'l10nxmo:') {
          $type = 'f';
          $name = substr($file['uri'], 8);
          $path = CRM_L10nmo_Form_Configuration::getCustomTranslationFolder(FALSE) . DIRECTORY_SEPARATOR . $name;
        } elseif (substr($file['uri'], 0, 10) == 'l10nxpack:') {
          $type = 'p';
          $name = substr($file['uri'], 10);
          $path = CRM_L10nmo_Form_Configuration::getCustomTranslationFolder(TRUE) . DIRECTORY_SEPARATOR . $name;
        } else {
          // noe of ours
          continue;
        }

        // add to list
        $configuration[] = [
            'type'        => $type,
            'path'        => $path,
            'name'        => $name,
            'active'      => 0,
            'domains'     => [],
            'locales'     => [],
            'created_id'  => $file['created_id'],
            'description' => $file['description'],
            'upload_date' => $file['upload_date'],
            'file_id'     => $file['id'],
        ];
      }
    }

    return $configuration;
  }

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts("Configure Custom Translation Files"));

    $configuration = self::getConfiguration(TRUE);
    $domains = $this->getDomains();
    $locales = $this->getLocales();
    foreach ($configuration as $i => $config) {
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
    }
    $this->assign('lines', $configuration);

    // set default config
    $configuration = CRM_Core_BAO_Setting::getItem( 'de.systopia.l10nmo', 'l10nmo_config');
    if (!empty($configuration)) {
      foreach ($configuration as $line_nr => $config) {
        $i = $line_nr + 1;
        $this->setDefaults([
            "type_{$i}"   => $config['type'],
            "file_{$i}"   => $config['file'],
            "pack_{$i}"   => $config['pack'],
            "domain_{$i}" => $config['domain'],
            "locale_{$i}" => $config['locale'],
        ]);
      }
    }

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
    for ($i = 1; $i <= L10NMO_CONFIG_COUNT; $i++) {
      if (   $values["type_{$i}"] == 'p' && !empty($values["pack_{$i}"])
          || $values["type_{$i}"] == 'f' && !empty($values["file_{$i}"])) {
        // there is something configured here...
        $configuration[] = [
            'type' => $values["type_{$i}"],
            'file' => $values["file_{$i}"],
            'pack' => $values["pack_{$i}"],
            'domain' => $values["domain_{$i}"],
            'locale' => $values["locale_{$i}"]
        ];
      }
    }

    CRM_Core_BAO_Setting::setItem($configuration, 'de.systopia.l10nmo', 'l10nmo_config');
    parent::postProcess();
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
