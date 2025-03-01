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