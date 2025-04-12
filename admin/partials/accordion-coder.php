<div class="postbox closed coder_snippet">
    <div class="postbox-header">
        <h2>Coding Snippet</h2>
    </div>
    <div class="inside">
        <nav>
            <ul>
                <li><a href="#" class="tab-link active" data-tab="all">All</a></li>
                <li><a href="#" class="tab-link" data-tab="Wordpress">WordPress and PHP</a></li>
                <li><a href="#" class="tab-link" data-tab="Js">JS</a></li>
                <li><a href="#" class="tab-link" data-tab="Css">CSS</a></li>
            </ul>
        </nav>

        <div class="coder_tab__content" id="all">
            <ul class="coding_action__list"></ul>
        </div>
        <div class="coder_tab__content" id="Wordpress">
            <h5>Theme</h5>
            <ul class="coding_action__list">
                <li><button data-command="the_logo">Logo</button></li>
                <li><button data-command="the_title">The Title</button></li>
                <li><button data-command="the_permalink">The Permalink</button></li>
                <li><button data-command="the_post_thumbnail">The post thumbnail</button></li>
                <li><button data-command="the_content">The Content</button></li>
                <li><button data-command="the_date">The Date</button></li>
                <li><button data-command="the_excerpt">The Excerpt</button></li>
                <li><button data-command="get_template_part">Get Template Part</button></li>
                <li><button data-command="template_url">Template Url</button></li>
            </ul>
            <h5>ACF</h5>
            <ul class="coding_action__list">
                <li><button data-command="the_field">The Field</button></li>
                <li><button data-command="the_field_title">The Field Title</button></li>
                <li><button data-command="the_field_description">The Field Description</button></li>
                <li><button data-command="the_field_image">The Field Image</button></li>
                <li><button data-command="the_field_link">The Field Link</button></li>
                <li><button data-command="the_field_link_array">The Field Link Array</button></li>
                <li><button data-command="if_get_field">If Get Field</button></li>

            </ul>
            <h5>Custom Post Type</h5>
            <ul class="coding_action__list">
                <li><button data-command="wp_query">WP Query</button></li>
                <li><button data-command="the_title">The Title</button></li>
                <li><button data-command="the_permalink">The Permalink</button></li>
                <li><button data-command="the_post_thumbnail">The post thumbnail</button></li>
                <li><button data-command="the_content">The Content</button></li>
                <li><button data-command="the_date">The Date</button></li>
                <li><button data-command="the_excerpt">The Excerpt</button></li>

            </ul>

            <h5>Page link</h5>
            <ul class="coding_action__list">
                <?php
                $pages = get_pages();
                foreach ($pages as $page) {
                    $slug = '/' . get_post_field('post_name', $page->ID);
                    echo '<li><button data-command="page_link" data-pageid="' . esc_attr($page->ID) . '" data-link="' . esc_attr($slug) . '">' . esc_html($page->post_title) . '</button></li>';
                }
                ?>
            </ul>

            <h5>PHP</h5>
            <ul class="coding_action__list">
                <li><button data-command="today_date">Today Date</button></li>
                <li><button data-command="first_class">First Class</button></li>
                <li><button data-command="even_class">Even Class</button></li>
                <li><button data-command="odd_class">Odd Class</button></li>

            </ul>

            <h5>WPML Translation</h5>
            <ul class="coding_action__list">
                <li><button data-command="convert_to_translatable_text">Conver Translatable Text</button></li>

            </ul>



        </div>
        <div class="coder_tab__content" id="Js">
            <ul class="coding_action__list">
                <li><button data-command="console_log">Console.log</button></li>
                <li><button data-command="alertjs">Alert</button></li>
                <li><button data-command="document_ready">Document ready</button></li>
            </ul>
        </div>
        <div class="coder_tab__content" id="Css">
            <ul class="coding_action__list">
                <li><button data-command="default_css">Default css</button></li>
                <li><button data-command="margin_auto">Margin Auto</button></li>
            </ul>
        </div>
    </div>

</div>