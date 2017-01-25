<?php

// Require hero style 1 class
require_once THEME_DIR . "/extensions/hero/style-3.class.php";

// Init an object of Hero class
$hero = new Hero_Style_3();
?>

<section id="hero-3" <?php echo $hero->get_section_attr( 'light-text' ); ?>>
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
            ?>

            <div class="row">
                <div <?php echo $hero->form_wrapper_attr(); ?>>
                    <?php $hero->form(); ?>
                </div>
            </div>
        </div>
    </div>
</section>