<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arCurrentValues */

$arTemplateParameters = array(
	"DISPLAY_SPECIALDATE" => Array(
		"NAME" => GetMessage("T_IBLOCK_DISP_SPDATE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
    "CANONICAL_ID" => Array(
		"NAME" => GetMessage("T_IBLOCK_IB_CANONICAL_ID"),
		"TYPE" => "STRING",
		"DEFAULT" => "5",
	),
);
