This folder stores multilanguage .mo files in a gettext file structure, i.e.
[pack_name]
+[LOCALE]
| +LC_MESSAGES
| | +civicrm.mo

e.g.
"My_Custom_Translation"
+ de_DE
| + LC_MESSAGES
| | + civicrm.mo
+ fr_FR
| + LC_MESSAGES
| | + civicrm.mo

If you want to upload mo packs via the UI, please make sure the webserver user has write permissions here.

To successfully upload and mo pack it has to have the file structure outlined above starting with the locale (language), zipped in a single file.
