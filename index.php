<?php

namespace MISHANIN;
require_once $_SERVER["DOCUMENT_ROOT"] . "/Main.php";
$obMain = new Main();
$arResponse = $obMain->getListLeads();
?>
<table style="collapse: collapse; border-spacing: 10px; border: 2px #ccc solid;">
  <tr>
    <th>Название сделки</th>
    <th>Название Компании</th>
    <th>Контакты</th>
  </tr>
  <?php
  foreach ($arResponse as $arItem) { ?>
    <tr>
      <td><?= $arItem["name"] ?></td>
      <td><?= $arItem["companyName"] ?></td>
      <td><?= !empty($arItem["contactsName"]) ? implode(", ", $arItem["contactsName"]) : "" ?></td>
    </tr>
    <?php
  }
  ?>
</table>