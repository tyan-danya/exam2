<?
$result = CIBlockElement::GetList([],[
    "IBLOCK_ID" => $arParams["CANONICAL_ID"],
    "PROPERTY_CANONICAL_VALUE" => $arResult["ID"]
]);
while($el = $result->Fetch()) {
    $APPLICATION->SetPageProperty("canonical", "<link rel=\"canonical\" href=\"" . $el["NAME"] . "\"> ");
}
