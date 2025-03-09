<li><span class="open__child">Change page to homepage</span>
    <div class="action__setting">
        <input type="text" name="page_id" placeholder="Page id">
        <?php ai_assistant_render_spark_button('change_default_page'); ?>
    </div>
</li>
<li><span class="open__child">Create Page</span>
    <div class="action__setting">
        <input type="text" name="page_name" placeholder="Page name"><br>
        <input type="checkbox" name="create_page_template"> Create page template
        <?php ai_assistant_render_spark_button('create_page_and_template_file'); ?>
    </div>
</li>
<li><span id="reset_permalink">Reset permalink</span></li>
<li><span class="open__child">Create User Type</span>
    <div class="action__setting">
        <input type="text" name="user_type" placeholder="User Type"><br>
        <p>Assign role</p>
        <label>
            <input type="radio" name="user_type" value="subscriber">Subscriber
        </label>
        <label>
            <input type="radio" name="user_type" value="author">Author
        </label>
        <label>
            <input type="radio" name="user_type" value="contributor">Contributor
        </label>
        <label>
            <input type="radio" name="user_type" value="editor">Editor
        </label>
        <label>
            <input type="radio" name="user_type" value="administrator">Administrator
        </label>

        <?php ai_assistant_render_spark_button('create_user_type'); ?>
    </div>
</li>
<li><span class="open__child">Remove User Type</span>
    <div class="action__setting">
        <input type="text" name="remove_user_type" placeholder="User Type">
        <?php ai_assistant_render_spark_button('remove_user_type'); ?>
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
            <input type="checkbox" name="cpt__template" checked> Create Single Page
        </label>
        <label>
            <input type="checkbox" name="cpt__archive_template" checked> Create Archive Page
        </label>

        <div class="d-flex no-of-posts">
            <p>No of Post in Archive</p>
            <input type="number" name="no_of_posts" placeholder="Post per page" min="1" value="1">
        </div>

        <h3>Supports</h3>
        <div style="display: flex;flex-direction: column; gap: 10px;">
            <input type="text" class="dashi_icon_field" name="dashi_icon" placeholder="Click to select icon"
                   readonly>
            <button type="button" class=" open-dashicon-picker button button-primary">Choose Dashicon
            </button>

        </div>

        <label>
            <input type="checkbox" name="cpt__title" checked> Title
        </label>
        <label>
            <input type="checkbox" name="cpt__editor" checked> Editor
        </label>
        <label>
            <input type="checkbox" name="cpt__featured_image" checked> Featured Image
        </label>
    </div>

</li>