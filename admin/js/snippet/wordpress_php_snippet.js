const wordpressSnippetHandlers = {
    the_title: () => insertSnippet(`<?php the_title(); ?>`, 5),
    the_permalink: () => insertSnippet(`<?php the_permalink(); ?>`, 5),
    the_post_thumbnail: () => insertSnippet(`<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>`, 6),
    the_date: () => insertSnippet(`<?php echo get_the_date() ;?>`, 5),
    the_excerpt: () => insertSnippet(`<?php the_excerpt(); ?>`, 5),
    the_field: () => insertSnippet(`<?php the_field(''); ?>`, 6),
    the_field_title: () => insertSnippet(`<?php the_field('title'); ?>`, 6),
    the_field_description: () => insertSnippet(`<?php the_field('description'); ?>`, 6),
    the_field_image: () => insertSnippet(`<?php the_field('image'); ?>`, 6),
    the_field_link: () => insertSnippet(`<?php the_field('link'); ?>`, 6),
    the_field_link_array: () => insertSnippetV2(`<?php
    $link_array = get_field('@cursor@'); 
    if ($link_array && isset($link_array['url'])) {
        $link_url = esc_url($link_array['url']); 
        ?>
        @content@
        <?php echo $link_url; ?>
        <?php echo $link_array['title']; ?>
        <?php
    }
?>`),
    today_date: () => insertSnippet(`<?php echo date('Y'); ?>`, 6),
    the_content: () => insertSnippet(`<?php the_content(); ?>`, 6),
    get_template_part: () => insertSnippet(` <?php get_template_part('partials/partial',''); ?>`, 6),
    if_get_field: () => {
        insertSnippetV2(`
        <?php if (get_field('@cursor@')) { ?>
            @content@
        <?php } ?>
    `);
    },
    the_logo: () => insertSnippet(`<?php 
    if (has_custom_logo()) {
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = wp_get_attachment_image($custom_logo_id, 'full', false, array('class' => 'img-fluid'));
        echo $logo;
    }`),
    page_link: (link) => insertSnippet(`${link}`, 1),




};
