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
  <div class="label">{$form.domain.label}</div>
  <div class="content">{$form.domain.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.language.label}</div>
  <div class="content">{$form.language.html}</div>
  <div class="clear"></div>
</div>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
