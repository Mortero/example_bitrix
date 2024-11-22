<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="example_block_wrapper">
    <div class="example_block_container">
        <div class="example_block_head">
            <h4>Результаты поиска:</h4>
        </div>
        <div class="example_block_body">
            <?php if (!empty($arResult['ITEMS'])) { ?>
                <?php foreach ($arResult['ITEMS'] as $item) { ?>
                    <div class="example_block_body__item">
                        <a href="/requests/<?= $item['ID']; ?>" target="_blank">
                            <p><?= $item['NAME']; ?></p>
                            <br>
                            <p><?= $item['DESCRIPTION']; ?></p>
                        </a>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="example_block_body__item">
                    <p>Ничего не найдено</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>