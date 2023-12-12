<?php
namespace Citrus;

use Bitrix\Main\EventManager;

class Events
{
    const AUTHOR_IS_NOT_AUTHORIZED = "Пользователь не авторизован, данные из формы: #FORM_NAME#";
    const AUTHOR_IS_AUTHORIZED = "Пользователь авторизован: #ID# (#LOGIN#) #NAME#, данные из формы: #FORM_NAME#";
    const FEEDBACK_EVENT_NAME = "FEEDBACK_FORM";
    const CONTENT_EDITORS_GROUP = "content_editor";
    const NEWS_IBLOCK_CODE = "furniture_news_s1";

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
        EventManager::getInstance()->addEventHandler(
            "main",
            "OnBeforeEventAdd",
            array(self::class, "checkFeedBackFormSend")
        );
        EventManager::getInstance()->addEventHandler(
            "main",
            "OnBuildGlobalMenu",
            array(self::class, "MyOnBuildGlobalMenu")
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

    public static function checkFeedBackFormSend(&$event, &$lid, &$arFields) {
        global $USER;
        if ($event !== self::FEEDBACK_EVENT_NAME) return;
        $params = array(
            'FORM_NAME' => $arFields['AUTHOR']
        );
        if ($USER->IsAuthorized()) {
            $arFields['AUTHOR'] = self::AUTHOR_IS_AUTHORIZED;
            $params["NAME"] = $USER->GetFirstName();
            $params["LOGIN"] = $USER->GetLogin();
            $params["ID"] = $USER->GetID();
        } else {
            $arFields['AUTHOR'] = self::AUTHOR_IS_NOT_AUTHORIZED;
        }
        foreach($params as $key => $param) {
            $arFields['AUTHOR'] = str_replace('#' . $key . '#', $param, $arFields['AUTHOR']);
        }
        \CEventLog::Add(array(
            'SEVERITY' => 'INFO',
            'AUDIT_TYPE_ID' => 'FEEDBACK_FORM',
            'MODULE_ID' => 'main',
            'DESCRIPTION' => 'Замена данных в отсылаемом письме – ' . $arFields['AUTHOR'],
        ));
    }


    function MyOnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
    {
        global $USER;
        $groups = \CUser::GetUserGroup($USER->GetID());
        $rsGroups = \CGroup::GetList(Array(), Array(), Array ("STRING_ID" => self::CONTENT_EDITORS_GROUP));
        if (!($contentGroup = $rsGroups->Fetch()) || !array_search($contentGroup["ID"], $groups)) {
            return;
        }

        foreach($aModuleMenu as $menuItem) {
            if ($menuItem["items_id"] === 'menu_iblock_/news') {
                $aModuleMenu = [$menuItem];
                $iblockRes = \CIBlock::GetList(Array(), Array("CODE" => self::NEWS_IBLOCK_CODE));
                if (!$newsIblock = $iblockRes->Fetch()) {
                    return;
                }
                foreach($menuItem["items"] as $childItem) {
                    if ($childItem["items_id"] === 'menu_iblock_/news/' . $newsIblock["ID"]) {
                        $aModuleMenu[0]["items"] = [$childItem];
                        break;
                    }
                }
                break;
            }
        }
        $aGlobalMenu = ["global_menu_content" => $aGlobalMenu["global_menu_content"]];
    }
}
