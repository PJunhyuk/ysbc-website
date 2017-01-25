<?php

// Require hero style 2 class
require_once THEME_DIR . "/extensions/hero/style-2.class.php";

// Init an object of Hero class
$hero = new Hero_Style_2();
?>

<section id="hero-2" <?php echo $hero->get_section_attr(); ?>>
    <?php
        $hero->overlay();
        $hero->top_line();
    ?>

    <div class="heading-block">
        <div class="container">
            <div class="row row-centered-columns light-text">
                <div class="col-lg-8 col-sm-7 col-xs-12">
                    <?php
                        $hero->heading_top();
                        $hero->heading();
                        $hero->subheading();
                    ?>
                </div>

                <div class="col-lg-4 col-sm-5 col-xs-12 align-center">
                    <?php $hero->form(); ?>
                </div>
            </div>
        </div>
    </div>
</section>