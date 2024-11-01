<?php if ($slides->have_posts()) { ?>
    <section class="uhsp-slider-wrapper">
        <?php if ($meta['arrow_buttons']) { ?>
            <div class="uhsp-left">
                <img src="<?= $this->asset('images/arrow-left@2x.png') ?>">
            </div>
            <div class="uhsp-right">
                <img src="<?= $this->asset('images/arrow-right@2x.png') ?>">
            </div>
        <?php } ?>

        <section class="uhsp-slider-titles">
            <ul class="uhsp-title-list">
                <?php while ($slides->have_posts()) { $slides->the_post(); ?>
                    <li class="uhsp-title">
                        <h2 class="uhsp-slide-title" style="color: <?= esc_attr($meta['title_color']) ?>;"><?= get_the_title(); ?></h2>
                        <div class="uhsp-slide-subtitle" style="color: <?= esc_attr($meta['subtitle_color']) ?>;"><?= get_the_content(); ?></div>
                    </li>
                <?php } ?>
                <div class="uhsp-hover-bar">
                    <div class="uhsp-hover-bar-color" style="background-color: <?= esc_attr($meta['title_color']) ?>;"></div>
                </div>
            </ul>
        </section>

        <section class="uhsp-slider-images">
            <?php while ($slides->have_posts()) { $slides->the_post(); ?>
                <article class="uhsp-single-slide" style="background-image: url('<?= esc_url(wp_get_attachment_image_src(the_post_thumbnail_url())) ?>')">
                    <img class="uhsp-single-image" src="<?= esc_url(MultiPostThumbnails::get_post_thumbnail_url('slide', 'foreground-icon', get_the_ID(), 'uhsp-foreground-icon@2x')) ?>" alt="<?= esc_attr(get_the_title()) ?>">
                    <div class="ushp-slide-overlay" style="background-color: <?= esc_attr($meta['overlay_color']) ?>; opacity: <?= esc_attr($meta['overlay_opacity']) ?>;"></div>
                </article>
            <?php } ?>
        </section>
    </section>
<?php } ?>