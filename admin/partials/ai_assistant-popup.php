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
            <button class="custom-tab " data-tab="contact_form7">Contact Form 7</button>
            <button class="custom-tab" data-tab="apply_acf">Apply ACF</button>
            <button class="custom-tab" data-tab="additional_settings">Additional Settings</button>
        </div>

        <!-- Tab Content -->
        <div id="basic_setting" class="custom-tab-content active">
            <h2>Basic Message</h2>
            <ul class="action__list">
                <?php include plugin_dir_path(__FILE__) . '../partials/partial-miscelleneous_task.php'; ?>
            </ul>

        </div>
        <div id="contact_form7" class="custom-tab-content">
            <div class="custom-toolbar">

                <button class="contact-form-btn" data-shortcode="text">Text</button>
                <button class="contact-form-btn" data-shortcode="email">Email</button>
                <button class="contact-form-btn" data-shortcode="url">URL</button>
                <button class="contact-form-btn" data-shortcode="tel">Tel</button>
                <button class="contact-form-btn" data-shortcode="number">Number</button>
                <button class="contact-form-btn" data-shortcode="date">Date</button>
                <button class="contact-form-btn" data-shortcode="textarea">Text Area</button>
                <button class="contact-form-btn" data-shortcode="drop_down_menu">Drop down menu</button>
                <button class="contact-form-btn" data-shortcode="checkbox">Checkboxes</button>
                <button class="contact-form-btn" data-shortcode="radio">Radio Buttons</button>
                <button class="contact-form-btn" data-shortcode="acceptance">Acceptance</button>
                <button class="contact-form-btn" data-shortcode="file">File</button>
                <button class="contact-form-btn" data-shortcode="submit">Submit</button>


            </div>
            <h3>Mail Message</h3>
            <button class="contact-form-btn" data-shortcode="convert_to_mail">Convert for mail message</button>

            <h3>Translate all the messages of the form</h3>

            <p>Visit
                <a target="_blank" href="https://translate.google.com/?sl=pt&tl=es&text=Thank%20you%20for%20your%20message.%20It%20has%20been%20sent.%0AThere%20was%20an%20error%20trying%20to%20send%20your%20message.%20Please%20try%20again%20later.%0AOne%20or%20more%20fields%20have%20an%20error.%20Please%20check%20and%20try%20again.%0AThere%20was%20an%20error%20trying%20to%20send%20your%20message.%20Please%20try%20again%20later.%0AYou%20must%20accept%20the%20terms%20and%20conditions%20before%20sending%20your%20message.%0APlease%20fill%20out%20this%20field.%0AThis%20field%20has%20a%20too%20long%20input.%0AThis%20field%20has%20a%20too%20short%20input.%0AThere%20was%20an%20unknown%20error%20uploading%20the%20file.%0AYou%20are%20not%20allowed%20to%20upload%20files%20of%20this%20type.%0AThe%20uploaded%20file%20is%20too%20large.%0AThere%20was%20an%20error%20uploading%20the%20file.%0APlease%20enter%20a%20date%20in%20YYYY-MM-DD%20format.%0AThis%20field%20has%20a%20too%20early%20date.%0AThis%20field%20has%20a%20too%20late%20date.%0APlease%20enter%20a%20number.%0AThis%20field%20has%20a%20too%20small%20number.%0AThis%20field%20has%20a%20too%20large%20number.%0AThe%20answer%20to%20the%20quiz%20is%20incorrect.%0APlease%20enter%20an%20email%20address.%0APlease%20enter%20a%20URL.%0APlease%20enter%20a%20telephone%20number.%0A&op=translate">
                    Google Translate
                </a> translate to desire language and paste below:
            </p>

            <select id="cf7_form_selector" name="cf7_form_selector">
                <option value="">Select a Contact Form</option>
                <?php
                $forms = get_posts(['post_type' => 'wpcf7_contact_form', 'numberposts' => -1]);
                foreach ($forms as $form) {
                    echo "<option value='{$form->ID}'>" . esc_html($form->post_title) . "</option>";
                }
                ?>
            </select>

            <textarea name="json__translated_text" id="json__translated_text" cols="30" rows="10"></textarea>
            <?php ai_assistant_render_spark_button('translate_validation_text'); ?>
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
        <div id="additional_settings" class="custom-tab-content">
            <ul class="action__list">
                <?php include plugin_dir_path(__FILE__) . '../partials/partial-additional_setting.php'; ?>
            </ul>
        </div>
    </div>
</div>




