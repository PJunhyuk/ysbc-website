<?php

// Require hero style 1 class
require_once THEME_DIR . "/extensions/hero/style-4.class.php";

// Init an object of Hero class
$hero = new Hero_Style_4();
?>

<section id="hero-4" <?php echo $hero->get_section_attr( 'light-text' ); ?>>
    <?php
        $hero->overlay();
        $hero->top_line();
    ?>

    <div class="heading-block">
        <div class="container">
            <div class="row row-centered-columns">
                <div class="col-sm-4 col-xs-12">
                    <?php $hero->img(); ?>
                </div>

                <div class="col-sm-8 col-xs-12">
                    <?php
                        $hero->heading_top();
                        $hero->heading();
                        $hero->subheading();
                        $hero->buttons();
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>