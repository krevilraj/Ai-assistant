const wordpressSnippetHandlers = {
    the_title: () => insertSnippetV2(`<?php the_title(@cursor@); ?>`),
    the_permalink: () => insertSnippetV2(`<?php the_permalink(@cursor@); ?>`),
    the_post_thumbnail: () => insertSnippetV2(`<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full@cursor@'); ?>`),
    the_date: () => insertSnippetV2(`<?php echo get_the_date('@cursor@') ;?>`),
    the_excerpt: () => insertSnippetV2(`<?php the_excerpt(@cursor@); ?>`),
    the_field: () => insertSnippetV2(`<?php the_field('@cursor@'); ?>`),
    the_field_title: () => insertSnippetV2(`<?php the_field('title@cursor@'); ?>`),
    the_field_description: () => insertSnippetV2(`<?php the_field('description@cursor@'); ?>`),
    the_field_image: () => insertSnippetV2(`<?php the_field('image@cursor@'); ?>`),
    the_field_link: () => insertSnippetV2(`<?php the_field('link@cursor@'); ?>`),
    template_url: () => insertSnippetV2(`<?php bloginfo('template_url'); ?>@cursor@`),
    convert_to_translatable_text: () => insertSnippetV3('get_text_domain',`<?php echo __('@content@','@processedtext@@cursor@'); ?>`),
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
    today_date: () => insertSnippetV2(`<?php echo date('Y@cursor@'); ?>`),
    the_content: () => insertSnippetV2(`<?php the_content(@cursor@); ?>`),
    get_template_part: () => insertSnippetV2(` <?php get_template_part('partials/partial','@cursor@'); ?>`),
    wp_query: () => insertSnippetV2(`<?php
$args = array(
    'post_type'      => '@cursor@',
    'posts_per_page' => -1, // ✅ Fetch all banners
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC'
);

$query = new WP_Query($args);

if ($query->have_posts()) :
    while ($query->have_posts()) : $query->the_post(); ?>
        
        @content@

    <?php endwhile;
    wp_reset_postdata(); // ✅ Reset query
endif;
?>
`),
    if_get_field: () => {
        insertSnippetV2(`
        <?php if (get_field('@cursor@')) { ?>
            @content@
        <?php } ?>
    `);
    },
    the_logo: () => insertSnippetV2(`<?php 
    if (has_custom_logo()) {
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = wp_get_attachment_image($custom_logo_id, 'full', false, array('class' => 'img-fluid'));
        echo $logo;
    }`),
    page_link: (link) => insertSnippetV2(`${link}@cursor@`, 1),
    first_class: () => insertSnippetV2(`<?php if($i==0) echo '@content@@cursor@';?>`),
    even_class: () => insertSnippetV2(`<?php if($i%2==0) echo '@content@@cursor@';?>`),
    odd_class: () => insertSnippetV2(`<?php if($i%2!=0) echo '@content@@cursor@';?>`),




};
