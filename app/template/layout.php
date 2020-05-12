<?php

use Teftely\Models\Story\Chapter;

/**
 * @var array     $pages
 * @var Chapter[] $chapters
 */
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru" dir="ltr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="index, follow, all"/>
    <title>Моя история</title>

    <link rel="stylesheet" href="/assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src='/assets/js/jquery-3.5.1.min.js' type="text/javascript"></script>
    <script src='/assets/js/turn/turn.min.js' type="text/javascript"></script>
    <script src='/assets/js/jquery-ui.min.js' type="text/javascript"></script>
</head>
<body>
<div id="container">
    <div id="book-hards">
        <div id="flip-book">
            <div></div>
            <div>
                <div class="page-content">
                    <h1>Моя история</h1>
                    <div class="pero"></div>
                    <div class="choose-page">
                        <label>
                            Перейти
                            <input type="text" class="page-number" value="1">
                        </label>
                        <button type="button" class="page-button">&#187;</button>
                        <br>
                        Подведите курсор к краю страницы, чтобы перелистнуть
                    </div>
                </div>
            </div>
            <?php foreach ($pages as $page => $chapters) { ?>
                <div>
                    <div class="page-content">
                        <?php foreach ($chapters as $chapter) { ?>
                            <div
                                class="chapter"
                                data-id="<?= $chapter->getId() ?>"
                                title="Автор: <?= $chapter->getAuthor() ?>"
                            >
                                <?= $chapter->getChapterHtml() ?>
                            </div>
                        <?php } ?>
                        <div class="page-counter"><?= $page ?></div>
                    </div>
                </div>
            <?php } ?>
            <div>
                <div class="page-content">
                    <h2 class="centered">Продолжение следует...</h2>
                </div>
            </div>
            <div></div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery(document).tooltip();
        jQuery("#flip-book").turn({
            autoCenter: false,
            elevation: 0,
            gradients: true,
            acceleration: true,
            duration: 1000
        }).bind("turning", function (event, page, pageObject) {
            let leftPage, rightPage;
            jQuery('.page-content').fadeOut();
            if (page % 2 === 1) {
                leftPage = page - 1;
                rightPage = page;
            } else {
                leftPage = page;
                rightPage = page + 1;
            }
            jQuery('.p' + leftPage + ' .page-content').fadeIn();
            jQuery('.p' + rightPage + ' .page-content').fadeIn();
        }).turn("next");

        jQuery('.page-button').click(function (event) {
            let page = jQuery('.page-number').val() ?? 1;
            page = parseInt(page) + 2;
            console.log(page);
            jQuery("#flip-book").turn("page", page);
        });
    });
</script>
</body>
</html>
