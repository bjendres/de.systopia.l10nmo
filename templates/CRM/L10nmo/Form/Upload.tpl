{*-------------------------------------------------------+
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
+-------------------------------------------------------*}


<div class="crm-section">
  <div class="label">{$form.type.label}</div>
  <div class="content">{$form.type.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.upload_file.label}</div>
  <div class="content">{$form.upload_file.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.file_name.label}</div>
  <div class="content">{$form.file_name.html}<code class="l10nmo-filename">.mo</code></div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.description.label}</div>
  <div class="content">{$form.description.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>


{literal}
  <script type="application/javascript">
    function l10nxShowSuffix() {
      let type = cj("#type").val();
      if (type == 'f') {
        cj("code.l10nmo-filename").show(100);
      } else {
        cj("code.l10nmo-filename").hide(100);
      }
    }
    l10nxShowSuffix();
    cj("#type").change(l10nxShowSuffix);

  </script>
{/literal}