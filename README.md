# Localisation Override Extension

![Screenshot](/images/screenshot.png)

This extension allows you to override the built-in translation files (.MOs) with your own.
The system allows you to create cascades, e.g. a small .MO file containing only your
changes, and all other strings would still be passed through the default translations.  

This extension is particularly powerful in combination with SYSTOPIA's [Profiler extension](https://github.com/systopia/de.systopia.l10nprofiler),
which helps you to capture the strings you want, and turn them into .MO files. 

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* [l10nx Extension](https://github.com/systopia/org.civicrm.l10nx)
* PHP v5.6+
* CiviCRM 5.5+
