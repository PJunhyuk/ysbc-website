<?php

// Require hero style 3 class
require_once THEME_DIR . "/extensions/hero/style-1.class.php";

// Init an object of Hero class
$hero = new Hero_Style_1();
?>

<section id="hero" <?php echo $hero->get_section_attr( 'light-text' ); ?>>
    <?php
        $hero->overlay();
        $hero->top_line();
    ?>

    <div class="heading-block">
        <div class="container">
            <?php
                $hero->heading_top();
                $hero->heading();
                $hero->subheading();
                $hero->buttons();
            ?>
        </div>
    </div>
</section>