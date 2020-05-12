<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 13.05.2020
 * Time: 1:56
 */

namespace MISHANIN;
class Main
{
  private $curl;
  private $iOffSet = 0;
  private $linkLeads = "https://dbistest3.amocrm.ru/api/v2/leads";
  private $linkContacts = "https://dbistest3.amocrm.ru/api/v2/contacts";
  private $conf = [
    "subDomain" => "dbistest3",
    "login" => "dbistest2@test.com",
    "hash" => "6acf309ffaa8da6e7171601a260083c672293471"
  ];
  private $arSelectedLeads = [];
  private $arContacts = [];

  function __construct()
  {
    $this->__getListContacts();
  }

  /**
   * Иницилизация curl
   * @param $sLink = ссылка
   */
  private function __init($sLink)
  {
    $this->curl = curl_init();
    if (!empty($this->curl)) {
      curl_reset($this->curl);
    }
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->curl, CURLOPT_USERAGENT, "amoCRM-API-client/1.0");
    curl_setopt($this->curl, CURLOPT_URL, $sLink);
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($this->curl, CURLOPT_HEADER, false);
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
  }

  /**
   * Запрос на выборку сделок
   */
  private function __requestListLeads()
  {
    $this->__init($this->linkLeads . "?USER_LOGIN=" . $this->conf["login"] . "&USER_HASH=" . $this->conf["hash"] . "&limit_rows=500&limit_offset=" . $this->iOffSet);
  }

  /**
   * Запрос на выборку контактов
   */
  private function __requestListContacts()
  {
    $this->__init($this->linkContacts . "?USER_LOGIN=" . $this->conf["login"] . "&USER_HASH=" . $this->conf["hash"] . "&limit_rows=500&limit_offset=" . $this->iOffSet);
  }

  /**
   * Получение списка сделок
   * @return mixed
   */
  public function getListLeads()
  {
    $this->iOffSet = 0;
    while (true) {
      $this->__requestListLeads();
      $arResponse = json_decode(curl_exec($this->curl), true);
      $arItems = $arResponse["_embedded"]["items"];

      // Выход из цикла
      if (empty($arItems)) {
        break;
      }

      foreach ($arItems as $arItem) {
        if (
          !empty($arItem["company"]) ||
          !empty($arItem["main_contact"]) ||
          !empty($arItem["contacts"])
        ) {
          $this->arSelectedLeads[$arItem["id"]] = [
            "name" => $arItem["name"],
            "companyName" => !empty($arItem["company"]) ? $arItem["company"]["name"] : "",
            "contactsName" => !empty($arItem["contacts"]) ? [] : ""
          ];

          // Добавляем названия контактов
          if (!empty($arItem["contacts"])) {
            foreach ($arItem["contacts"]["id"] as $idContact) {
              if (!empty($this->arContacts[$idContact])) {
                $this->arSelectedLeads[$arItem["id"]]["contactsName"][] = $this->arContacts[$idContact];
              }
            }
          }
        }
      }

      // Выход из цикла
      if (count($arItems) < 500) {
        break;
      }

      sleep(1);
      $this->iOffSet += 500;
    }
    return $this->arSelectedLeads;
  }

  /**
   * Получение списка контактов
   * @return mixed
   */
  private function __getListContacts()
  {
    $this->iOffSet = 0;
    while (true) {
      $this->__requestListContacts();
      $arResponse = json_decode(curl_exec($this->curl), true);
      $arItems = $arResponse["_embedded"]["items"];
      // Выход из цикла
      if (empty($arItems)) {
        break;
      }

      foreach ($arItems as $arItem) {
        $this->arContacts[$arItem["id"]] = $arItem["name"];
      }
      // Выход из цикла
      if (count($arItems) < 500) {
        break;
      }

      sleep(1);
      $this->iOffSet += 500;
    }
  }

}