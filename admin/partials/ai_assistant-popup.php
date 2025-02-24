<!-- Popup -->
<div id="custom-field-popup" class="custom-field-popup">
    <div class="custom-field-popup-content">
        <div class="close_n_drag">
            <span id="drag-popup-handle" class="drag-handle dashicons dashicons-move"></span>
            <span id="close-custom-field-popup" class="dashicons dashicons-no-alt"></span>
            <!-- Drag Icon -->
        </div>

        <!-- Tabs -->
        <div class="custom-tabs">
            <button class="custom-tab active" data-tab="basic_setting">Basic Setting</button>
            <button class="custom-tab " data-tab="create_acf">Create ACF</button>
            <button class="custom-tab" data-tab="apply_acf">Apply ACF</button>
            <button class="custom-tab" data-tab="static_to_dynamic">Static to Dynamic</button>
            <button class="custom-tab" data-tab="additional_settings">Additional Settings</button>
        </div>

        <!-- Tab Content -->
        <div id="basic_setting" class="custom-tab-content active">
            <h2>Basic Message</h2>
            <ul class="action__list">
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
                <li><span id="reset_permalink">Blog pages show at most</span></li>


            </ul>

        </div>
        <div id="create_acf" class="custom-tab-content">
            <div class="custom-toolbar">

                <button class="custom-toolbar-btn" data-shortcode="text">Text</button>
                <button class="custom-toolbar-btn" data-shortcode="textarea">Text Area</button>
                <button class="custom-toolbar-btn" data-shortcode="image">Image</button>
                <button class="custom-toolbar-btn" data-shortcode="wysiwyg">WYSIWYG</button>
                <button class="custom-toolbar-btn" data-shortcode="number">Number</button>
                <button class="custom-toolbar-btn" data-shortcode="url">URL</button>
                <button class="custom-toolbar-btn" data-shortcode="email">Email</button>
                <button class="custom-toolbar-btn" data-shortcode="tel">Tel</button>

                <div class="d-full">
                    <button class="custom-toolbar-btn" data-shortcode="tab">Tab</button>
                    <button class="custom-toolbar-btn" data-shortcode="checkbox">Checkboxes</button>
                    <button class="custom-toolbar-btn" data-shortcode="radio">Radio Buttons</button>
                    <br>

                </div>
                <div class="d-full">
                    <button class="" data-shortcode="options">Options</button>
                </div>


            </div>

            <div class="custom-editor">
                <textarea id="custom-form-editor" placeholder="Enter your custom field template here..."></textarea>
                <div class="acf-location-container">
                    <label>Show this field group if</label>
                    <div class="acf-location-row">
                        <!-- First Dropdown: Condition Type -->
                        <select class="acf-location-param">
                            <option value="post_type">Post Type</option>
                            <option value="page">Page</option>
                            <option value="page_template">Page Template</option>
                            <option value="taxonomy">Taxonomy</option>
                        </select>

                        <!-- Second Dropdown: Operator -->
                        <select class="acf-location-operator">
                            <option value="==">is equal to</option>
                            <option value="!=">is not equal to</option>
                        </select>

                        <!-- Third Dropdown: Dynamic Values (Initially Empty) -->

                        <select class="acf-location-value">
                            <option value="">Select a value</option>

                        </select>
                    </div>

                </div>

                <button id="create-json-btn" style="margin-top: 10px;">Create JSON</button>
            </div>
        </div>
        <div id="apply_acf" class="custom-tab-content">
            <div class="acf-location-container">
                <label>URl:</label>
                <input type="text" id="url">
                <button id="get_custom_fields" style="margin-top: 10px;">Get Custom Fields</button>
            </div>
            <div class="acf-field-container">

            </div>
        </div>
        <div id="static_to_dynamic" class="custom-tab-content">
            <h2>Static to Dynamic Process</h2>
            <ul class="action__list">
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
                        <textarea name="correct_header" id="" cols="30" rows="10"></textarea>

                        <?php ai_assistant_render_spark_button('correct_header'); ?>


                    </div>
                </li>
                <li>
                    <span class="open__child">Correct Footer</span>
                    <div class="action__setting">
                        <textarea name="correct_footer" id="" cols="30" rows="10"></textarea>
                        <?php ai_assistant_render_spark_button('correct_footer'); ?>
                    </div>
                </li>

                <li>
                    <span class="open__child">Create Menu</span>
                    <div class="action__setting">
                        <input type="text" name="menu_name"
                               placeholder="Menu name"><?php ai_assistant_render_spark_button('create_menu'); ?>


                    </div>
                </li>




            </ul>
        </div>
        <div id="additional_settings" class="custom-tab-content">
            <textarea id="settings-editor" placeholder="Enter Additional Settings..."></textarea>
        </div>
    </div>
</div>




