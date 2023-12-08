<?php
namespace Citrus;

use Bitrix\Main\EventManager;



class Events
{
    public static function registerEvents() {

        $handler = EventManager::getInstance()->addEventHandler(
            "iblock",
            "OnBeforeIBlockElementUpdate",
            array(self::class, "checkShowCount")
        );
    }

    public static function checkShowCount(&$arFields) {
        global $APPLICATION;
        $result = \CIBlockElement::GetList([], ["ID" => $arFields["ID"]])->Fetch();
        if ($arFields["ACTIVE"] !== "N" || $arFields["ACTIVE"] === $result["ACTIVE"]) return true;
        if (
            isset($result["SHOW_COUNTER"]) &&
            !empty($result["SHOW_COUNTER"]) &&
            $result["SHOW_COUNTER"] > 2
        ) {
            $APPLICATION->ThrowException("Товар невозможно деактивировать, у него " . $result["SHOW_COUNTER"] . " просмотров");
            return false;
        }

    }
}
