<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("iblock")) {
    ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
    return;
}

use Bitrix\Main\Loader;
//для загрузки необходимых файлов, классов и модулей

use Bitrix\Main\Data\Cache;
//для кеширования PHP переменных и HTML результата выполнения скрипта

use Bitrix\Main\Localization\Loc;
//для работы с языковыми переменными

class KacIvanTestComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams) {
        $arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
        return $arParams;
    }

    protected function getCategoryTree($parentCategoryId = 0, $depth = 0) {
        $categoryTree = array();

        $showCategoriesWithItems = $this->arParams['SHOW_CATEGORIES_WITH_ITEMS'] === 'Y';

        $categories = \CIBlockSection::GetList(array('SORT' => 'ASC'), array('IBLOCK_ID' => $this->arParams['IBLOCK_ID'], 'SECTION_ID' => $parentCategoryId));
        while ($category = $categories->GetNext()) {
            $hasItems = $this->categoryHasItems($category['ID']);

            if ($showCategoriesWithItems && $hasItems || !$showCategoriesWithItems) {
                $categoryTree[] = array(
                    'ID' => $category['ID'],
                    'NAME' => $category['NAME'],
                    'DEPTH' => $depth,
                    'ELEMENTS' => $this->getCategoryElements($category['ID'])
                );

                if (!$showCategoriesWithItems) {
                    $categoryTree = array_merge($categoryTree, $this->getCategoryTree($category['ID'], $depth + 1));
                }
            }
        }

        return $categoryTree;
    }

    protected function categoryHasItems($categoryId) {
        $elements = $this->getCategoryElements($categoryId);
        return !empty($elements);
    }

    protected function getCategoryElements($categoryId) {
        $elements = array();

        $filter = array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'SECTION_ID' => $categoryId
        );

        $select = array('ID', 'NAME');
        $elementsQuery = \CIBlockElement::GetList(array('SORT' => 'ASC'), $filter, false, false, $select);

        while ($element = $elementsQuery->GetNext()) {
            $elements[] = array(
                'ID' => $element['ID'],
                'NAME' => $element['NAME'],
                'TAGS' => $this->getElementTags($element['ID'])
            );
        }

        return $elements;
    }

    protected function getElementTags($elementId) {
        $tags = array();

        $dbProperty = \CIBlockElement::getProperty(
            $this->arParams['IBLOCK_ID'],
            $elementId,
            array(),
            array('CODE' => 'TAGS')
        );

        while ($property = $dbProperty->Fetch()) {
            $tags = explode(',', $property['VALUE']);
        }

        return $tags;
    }

    public function executeComponent() {
        $this->setResultCacheKeys(array($this->arParams['IBLOCK_ID']));

        if ($this->startResultCache()) {
            $result = array(
                'CATEGORY_TREE' => $this->getCategoryTree()
            );

            $this->arResult = $result;
            $this->includeComponentTemplate();
        }

        $this->endResultCache();
    }
}