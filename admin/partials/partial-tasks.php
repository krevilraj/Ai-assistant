<li class="create_theme"><span class="open__child">Create theme</span>
    <div class="action__setting">
        <input type="text" name="theme_name" placeholder="Theme Name">
        <input type="text" name="theme_uri" placeholder="Theme URI">
        <input type="text" name="author" placeholder="Author">
        <input type="text" name="author_uri" placeholder="Author URI">
        <input type="text" name="text_domain" placeholder="Text domain(use underscore if space)">

        <?php ai_assistant_render_spark_button('create_theme'); ?>

    </div>
</li>
<li>
    <span class="open__child">Create Page</span>
    <div class="action__setting">
        <input type="text" name="page_name"
               placeholder="Page name"><?php ai_assistant_render_spark_button('create_page_and_template_file'); ?>
        <label>
            <input type="checkbox" name="create_page_template" checked> Create page template
        </label>

    </div>
</li>
<li>
    <span class="open__child">Correct Header</span>
    <div class="action__setting">
        <div class="text-align-right">
            <textarea name="correct_header" id="" cols="30" rows="10" placeholder="Put all the content of the static header"></textarea>

            <?php ai_assistant_render_spark_button('correct_header'); ?>
        </div>

    </div>
</li>

<li>
    <span class="open__child">Correct Footer</span>
    <div class="action__setting">
        <div class="text-align-right">
            <textarea name="correct_footer" id="" cols="30" rows="10" placeholder="Put all the content of the static footer"></textarea>
            <?php ai_assistant_render_spark_button('correct_footer'); ?>
        </div>
    </div>
</li>
<li>
    <span class="open__child">Create Menu</span>
    <div class="action__setting">
        <input type="text" name="menu_name"
               placeholder="Menu name"><?php ai_assistant_render_spark_button('create_menu'); ?>


    </div>
</li>
<li>
    <span class="open__child">Solve Menu</span>
    <div class="action__setting">


        <?php
        $menus = wp_get_nav_menus();
        if (!empty($menus)) {
            echo '<select name="menu__name">';
            foreach ($menus as $menu) {
                echo '<option value="' . esc_attr($menu->slug) . '">' . esc_html($menu->name) . '</option>';
            }
            echo '</select>';
        } else {
            echo '<p>No menus available.</p>';
        }
        ?>
        <p><em>Note: Please select the static menu from the editor. It replace with dynamic wordpress menu to the editor then save it.</em></p>
        <?php ai_assistant_render_spark_button('correct_menu'); ?>


    </div>
</li>
<li>
    <span class="open__child">Create Custom Post Type</span>
    <div class="action__setting">
        <input type="text" name="cpt_slug" placeholder="Post Type Slug">
        <input type="text" name="plural__label" placeholder="Plural Label">
        <input type="text" name="singular__label" placeholder="Singular Label">

        <?php ai_assistant_render_spark_button('create_custom_post_type'); ?>

        <label>
            <input type="checkbox" name="cpt__template" checked> Create template
        </label>

        <h3>Supports</h3>
        <div style="display: flex;flex-direction: column; gap: 10px;">
            <input type="text" id="dashi_icon_field" name="dashi_icon" placeholder="Click to select icon" readonly>
            <button type="button" id="open-dashicon-picker" class="button button-primary">Choose Dashicon</button>

        </div>

        <label>
            <input type="checkbox" name="cpt__editor" checked> Editor
        </label>
        <label>
            <input type="checkbox" name="cpt__featured_image" checked> Featured Image
        </label>
    </div>

</li>