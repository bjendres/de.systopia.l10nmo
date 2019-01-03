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

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts("Configure Custom Translation (.mo) Files"));

    for ($i = 1; $i <= L10NMO_CONFIG_COUNT; $i++) {
      $this->add(
          'select',
          "domain_{$i}",
          E::ts("Domain"),
          $this->getDomains()
      );

      $this->add(
          'select',
          "type_{$i}",
          E::ts("Type"),
          ['p' => E::ts('Pack'), 'f' => E::ts('File')],
          TRUE,
          ['class' => 'l10nmo-type']
      );

      $this->add(
          'select',
          "pack_{$i}",
          E::ts("Pack"),
          $this->getPacks()
      );

      $this->add(
          'select',
          "file_{$i}",
          E::ts("File"),
          $this->getFiles()
      );

      $this->add(
          'select',
          "locale_{$i}",
          E::ts("Locale"),
          $this->getLocales()
      );
    }
    $this->assign('lines', range(1, L10NMO_CONFIG_COUNT + 1));

    // load and set default config
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

    $this->addButtons(array(
      array(
          'type'      => 'submit',
          'name'      => E::ts('Save'),
          'isDefault' => TRUE,
      ),
    ));

    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

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
      $this->domains = ['' => E::ts("Any")];
      $this->domains['civicrm'] = E::ts("civicrm: Main CiviCRM");

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
   * Get available packs
   *
   * @return array|null
   */
  protected function getPacks() {
    if ($this->packs === NULL) {
      $this->packs = ['' => E::ts("None")];
      $folder = E::path('mo_store/mopacks');
      $files = scandir($folder);
      foreach ($files as $file) {
        $filename = $folder . DIRECTORY_SEPARATOR . $file;
        if (is_dir($filename) && $file[0] != '.') {
          $this->packs[$filename] = $file;
        }
      }
    }
    return $this->packs;
  }

  /**
   * Get available individual files
   *
   * @return array|null
   */
  protected function getFiles() {
    if ($this->files === NULL) {
      $this->files = ['' => E::ts("None")];
      $folder = E::path('mo_store/mofiles');
      $files = scandir($folder);
      foreach ($files as $file) {
        $suffix = mb_substr($file, mb_strlen($file) - 3);
        if ($suffix == '.mo') {
          $this->files[$folder . DIRECTORY_SEPARATOR . $file] = mb_substr($file, 0, mb_strlen($file) - 3);
        }
      }
    }
    return $this->files;
  }

  /**
   * Get available locales
   *
   * @return array|null
   */
  protected function getLocales() {
    if ($this->locales === NULL) {
      $this->locales = ['' => E::ts("Any")] + CRM_Core_I18n::languages(FALSE);
    }
    return $this->locales;
  }
}
