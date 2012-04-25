<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*processing arParams*/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
if(!$arParams["IBLOCK_ID"] || $arParams["IBLOCK_ID"] < 1) return false;
$arParams["INCLUDE_SECTIONS"] = $arParams["INCLUDE_SECTIONS"] === "Y" ? true : false;
$arParams["INCLUDE_ELEMENTS"] = $arParams["INCLUDE_ELEMENTS"] === "Y" ? true : false;
$arParams["MAX_DEPTH"] = intval($arParams["MAX_DEPTH"]);
if($arParams["MAX_DEPTH"]<=0) $arParams["MAX_DEPTH"]=1;

$arResult["ITEMS"] = array();

if($this->StartResultCache())
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
	}
	else
	{

        $dbIBlock = CIBlock::GetByID($arParams["IBLOCK_ID"]);
        if($arIBlock = $dbIBlock->Fetch()) {
            if(strlen($arParams["SECTION_URL"]) > 0) {
                $arIBlock["SECTION_PAGE_URL"] = $arParams["SECTION_URL"];
            }
            if(strlen($arParams["ELEMENT_URL"]) > 0) {
                $arIBlock["DETAIL_PAGE_URL"] = $arParams["ELEMENT_URL"];
            }

            if($arParams["INCLUDE_SECTIONS"]) {
                $dbSections = CIBlockSection::GetList(array("left_margin"=>"asc"), array("ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"]), true, array("NAME", "SECTION_PAGE_URL", "DEPTH_LEVEL"));
                $dbSections->SetUrlTemplates("", $arIBlock["SECTION_PAGE_URL"]);
                while($arSection = $dbSections->GetNext()) {
                    $current_depth = $arSection["DEPTH_LEVEL"];
                    if($current_depth > $arParams["MAX_DEPTH"]) continue;
                    $arResult["ITEMS"][] = Array(
                        $arSection["NAME"],
                        $arSection["SECTION_PAGE_URL"],
                        Array(),
                        Array("FROM_IBLOCK" => 1, "DEPTH_LEVEL"=>$current_depth, "IS_PARENT" => ($arSection["ELEMENT_CNT"] > 0 && $arParams["INCLUDE_ELEMENTS"] ? 1 : 0)),
                    );

                    if($arParams["INCLUDE_ELEMENTS"]) {
                        $dbElements = CIBlockElement::GetList(array("SORT" => "DESC"), array("ACTIVE" => "Y", "SECTION_GLOBAL_ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"], "SECTION_ID" => $arSection["ID"]), false, false, array("NAME", "DETAIL_PAGE_URL"));
                        $dbElements->SetUrlTemplates($arIBlock["DETAIL_PAGE_URL"]);
                        while($arElement = $dbElements->GetNext()) {
                            $current_depth = $arSection["DEPTH_LEVEL"]+1;
                            if($current_depth > $arParams["MAX_DEPTH"]) continue;
                            $arResult["ITEMS"][] = Array(
                                $arElement["NAME"],
                                $arElement["DETAIL_PAGE_URL"],
                                Array(),
                                Array("FROM_IBLOCK" => 1, "DEPTH_LEVEL"=>$current_depth, "IS_PARENT" => 0),
                            );
                        }
                    }
                }
            } elseif($arParams["INCLUDE_ELEMENTS"]) {

                $dbElements = CIBlockElement::GetList(array("SORT" => "DESC"), array("IBLOCK_ID" => $arParams["IBLOCK_ID"]), false, false, array("NAME", "DETAIL_PAGE_URL"));
                $dbElements->SetUrlTemplates($arIBlock["DETAIL_PAGE_UR"]);
                while($arElement = $dbElements->GetNext()) {
                    $current_depth = 0+1;
                    if($current_depth > $arParams["MAX_DEPTH"]) continue;
                    $arResult["ITEMS"][] = Array(
                        $arElement["NAME"],
                        $arElement["DETAIL_PAGE_URL"],
                        Array(),
                        Array("FROM_IBLOCK" => 1, "DEPTH_LEVEL"=>$current_depth, "IS_PARENT" => 0),
                    );
                }
            }
        }

		$this->EndResultCache();
	}
}

/*foreach($arResult["ITEMS"] as $arItem)
{
	if ($menuIndex > 0)
		$aMenuLinksNew[$menuIndex - 1][3]["IS_PARENT"] = $arItem["DEPTH_LEVEL"] > $previousDepthLevel;
	$previousDepthLevel = $arItem["DEPTH_LEVEL"];

	$aMenuLinksNew[$menuIndex++] = array(
		htmlspecialchars($arItem["~NAME"]),
        $arItem["~SECTION_PAGE_URL"],
		$arResult["ELEMENT_LINKS"][$arItem["ID"]],
		array(
			"FROM_IBLOCK" => true,
			"IS_PARENT" => false,
			"DEPTH_LEVEL" => $arItem["DEPTH_LEVEL"],
		),
	);
}*/
//$aMenuLinksNew = $arResult["ITEMS"];
//echo("<pre>");print_r($arResult["ITEMS"]);echo("</pre>");

return $arResult["ITEMS"];
?>
