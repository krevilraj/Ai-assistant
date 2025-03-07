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




