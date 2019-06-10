<?php
/*-------------------------------------------------------+
| L10n Profiling Extension                               |
| Copyright (C) 2019 SYSTOPIA                            |
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
 * L10mn Configuration
 */
class CRM_L10nmo_Configuration {
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
          $path = CRM_L10nmo_Configuration::getCustomTranslationFolder(FALSE) . DIRECTORY_SEPARATOR . $name;
        } elseif (substr($file['uri'], 0, 10) == 'l10nxpack:') {
          $type = 'p';
          $name = substr($file['uri'], 10);
          $path = CRM_L10nmo_Configuration::getCustomTranslationFolder(TRUE) . DIRECTORY_SEPARATOR . $name;
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
            'description' => CRM_Utils_Array::value('description', $file, ''),
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
   * Count the number of languages in the path
   * @param $path string path of the base folder
   */
  public static function getPackLanguageCount($path) {
    $file_count = 0;
    $folders = scandir($path);
    foreach ($folders as $folder) {
      if (preg_match('/^[a-z][a-z]_[A-Z][A-Z]$/', $folder)) {
        $potential_mo_file = $path . DIRECTORY_SEPARATOR . $folder . '/LC_MESSAGES/civicrm.mo';
        if (file_exists($potential_mo_file)) {
          $file_count += 1;
        }
      }
    }
    return $file_count;
  }

  /**
   * Recursively deletes a directory
   * Copied from StackExchange
   *
   * @see https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
   */
  public static function rrmdir($dir) {
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


}
