<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

?>

<?php if (!empty($arResult['CATEGORY_TREE'])): ?>
    <ul class="category_tree-parent">
        <?php foreach ($arResult['CATEGORY_TREE'] as $category): ?>
            <li>
                <span><?= str_repeat('-', $category['DEPTH']) . $category['NAME'] ?></span>
                <?php if (!empty($category['ELEMENTS'])): ?>
                    <ul class="category_tree-element">
                        <?php foreach ($category['ELEMENTS'] as $element): ?>
                            <li>
                                <?= $element['NAME'] ?>
                                <?php if (!empty($element['TAGS']) && !empty($element['TAGS'][0])): ?>
                                    (<span class="category_tree-tags"><?= implode(', ', $element['TAGS']) ?></span>)
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>