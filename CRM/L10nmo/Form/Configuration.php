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

    $configuration = self::getConfiguration(TRUE);
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
        self::deleteData($configuration[$index]);
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
          $config['description'] = CRM_Utils_Array::value('description', $file, '');
          $config['upload_date'] = $file['upload_date'];
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
            'description' => $file['description'],
            'upload_date' => $file['upload_date'],
            'file_id'     => $file['id'],
        ];
      }
    }

    return $configuration;
  }

  /**
   * Delete the file entity and the connected files of the given config entry
   *
   * @param $config array configuration
   */
  public static function deleteData($config) {
    // delete the files
    if ($config['type'] == 'f') {
      unlink($config['path']);
    } else {
      self::rrmdir($config['path']);
    }

    // delete the entity
    // broken: civicrm_api3('File', 'delete', ['id' => $config['file_id']]);
    $file_id = (int) $config['file_id'];
    if ($file_id) {
      CRM_Core_DAO::executeQuery("DELETE FROM civicrm_file WHERE id = {$file_id};");
    }
  }


  /**
   * Recursively deletes a directory
   * Copied from StackExchange
   *
   * @see https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
   */
  private static function rrmdir($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (is_dir($dir."/".$object))
            self::rrmdir($dir."/".$object);
          else
            unlink($dir."/".$object);
        }
      }
      rmdir($dir);
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
