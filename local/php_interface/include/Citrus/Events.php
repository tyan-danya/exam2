<?php
namespace Citrus;

use Bitrix\Main\EventManager;



class Events
{
    public static function registerEvents() {

        EventManager::getInstance()->addEventHandler(
            "iblock",
            "OnBeforeIBlockElementUpdate",
            array(self::class, "checkShowCount")
        );
        EventManager::getInstance()->addEventHandler(
            "main",
            "OnEpilog",
            array(self::class, "check404")
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

    public static function check404() {
        if (defined('ERROR_404') && ERROR_404 == "Y") {
            \CEventLog::Add(array(
                'SEVERITY' => 'INFO',
                'AUDIT_TYPE_ID' => 'ERROR_404',
                'MODULE_ID' => 'main',
                'DESCRIPTION' => 'url страницы',
            ));
        }
    }
}
