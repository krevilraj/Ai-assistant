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
            <button class="custom-tab" data-tab="wp_pages">Pages</button>
            <button class="custom-tab" data-tab="wp_wpml">Wpml</button>
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
                <a target="_blank"
                   href="https://translate.google.com/?sl=pt&tl=es&text=Thank%20you%20for%20your%20message.%20It%20has%20been%20sent.%0AThere%20was%20an%20error%20trying%20to%20send%20your%20message.%20Please%20try%20again%20later.%0AOne%20or%20more%20fields%20have%20an%20error.%20Please%20check%20and%20try%20again.%0AThere%20was%20an%20error%20trying%20to%20send%20your%20message.%20Please%20try%20again%20later.%0AYou%20must%20accept%20the%20terms%20and%20conditions%20before%20sending%20your%20message.%0APlease%20fill%20out%20this%20field.%0AThis%20field%20has%20a%20too%20long%20input.%0AThis%20field%20has%20a%20too%20short%20input.%0AThere%20was%20an%20unknown%20error%20uploading%20the%20file.%0AYou%20are%20not%20allowed%20to%20upload%20files%20of%20this%20type.%0AThe%20uploaded%20file%20is%20too%20large.%0AThere%20was%20an%20error%20uploading%20the%20file.%0APlease%20enter%20a%20date%20in%20YYYY-MM-DD%20format.%0AThis%20field%20has%20a%20too%20early%20date.%0AThis%20field%20has%20a%20too%20late%20date.%0APlease%20enter%20a%20number.%0AThis%20field%20has%20a%20too%20small%20number.%0AThis%20field%20has%20a%20too%20large%20number.%0AThe%20answer%20to%20the%20quiz%20is%20incorrect.%0APlease%20enter%20an%20email%20address.%0APlease%20enter%20a%20URL.%0APlease%20enter%20a%20telephone%20number.%0A&op=translate">
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
            <h3>Apply Custom Fields</h3>
            <div class="acf-field-container">
                <div id="field-groups-accordion">
                    <?php
                    // Clear ACF Cache to fetch the latest fields
                    wp_cache_delete('acf_get_field_groups');

                    // Fetch all ACF field groups
                    $field_groups = acf_get_field_groups();

                    // Loop through each field group
                    foreach ($field_groups as $group) {
                        $group_key = $group['key'];
                        $group_title = $group['title'];

                        // Get the location (example: first location rule)
                        $location = !empty($group['location'][0][0]['param']) ? $group['location'][0][0]['param'] : 'N/A';

                        // Fetch fields for this group
                        $fields = acf_get_fields($group_key);

                        echo '<div class="accordion-item">';
                        echo '<div class="accordion-header">';
                        echo '<span>' . esc_html($group_title) . '</span>';
                        echo '<span>Location: ' . esc_html($location) . '</span>';
                        echo '</div>';
                        echo '<div class="accordion-content">';

                        if (!empty($fields)) {
                            display_acf_fields_popup_with_tabs($fields);
                        } else {
                            echo '<p>No fields found for this group.</p>';
                        }

                        echo '</div>';
                        echo '</div>';
                    }

                    /**
                     * EXACT COPY from your working code
                     */

                    function display_acf_fields_popup_with_tabs($fields)
                    {
                        if (empty($fields) || !is_array($fields)) {
                            echo '<p>No fields found.</p>';
                            return;
                        }

                        // Collect fields into tab buckets
                        $tabs = [];
                        $currentTabKey = 'default';

                        $tabs[$currentTabKey] = [
                            'label'  => 'Fields',
                            'fields' => []
                        ];

                        foreach ($fields as $field) {
                            $type  = $field['type'] ?? '';
                            $label = $field['label'] ?? '';
                            $name  = $field['name'] ?? '';

                            $slug = strtolower(str_replace(" ", "_", preg_replace("/[^a-zA-Z0-9_]/", "", (string)$name)));

                            // ACF Tab => start a new UI tab
                            if ($type === 'tab') {
                                $currentTabKey = $slug ?: ('tab_' . wp_generate_password(6, false, false));
                                if (!isset($tabs[$currentTabKey])) {
                                    $tabs[$currentTabKey] = [
                                        'label'  => $label ?: 'Tab',
                                        'fields' => []
                                    ];
                                }
                                continue;
                            }

                            $tabs[$currentTabKey]['fields'][] = $field;
                        }

                        // If there are no real tabs (only default), just render normal list
                        $hasRealTabs = (count($tabs) > 1);

                        if (!$hasRealTabs) {
                            display_acf_fields_popup($tabs['default']['fields']);
                            return;
                        }

                        // Render tabs UI
                        echo '<div class="acf-ui-tabs">';

                        // Tab buttons
                        echo '<div class="acf-ui-tabs-nav">';
                        $i = 0;
                        foreach ($tabs as $key => $tab) {
                            // skip empty default tab button if it's empty
                            if ($key === 'default' && empty($tab['fields'])) continue;

                            $active = ($i === 0) ? ' is-active' : '';
                            echo '<button type="button" class="acf-ui-tab-btn' . esc_attr($active) . '" data-tab="' . esc_attr($key) . '">'
                                . esc_html($tab['label']) .
                                '</button>';
                            $i++;
                        }
                        echo '</div>';

                        // Panels
                        echo '<div class="acf-ui-tabs-panels">';
                        $i = 0;
                        foreach ($tabs as $key => $tab) {
                            if ($key === 'default' && empty($tab['fields'])) continue;

                            $active = ($i === 0) ? ' is-active' : '';
                            echo '<div class="acf-ui-tab-panel' . esc_attr($active) . '" data-panel="' . esc_attr($key) . '">';

                            if (!empty($tab['fields'])) {
                                display_acf_fields_popup($tab['fields']); // ✅ YOUR SAME BUTTONS
                            } else {
                                echo '<p>No fields in this tab.</p>';
                            }

                            echo '</div>';
                            $i++;
                        }
                        echo '</div>';

                        echo '</div>';
                    }

                    /**
                     * KEEP your existing renderer EXACTLY (same classes/data attrs)
                     * Just paste your original function here if it isn't already.
                     */
                    function display_acf_fields_popup($fields, $is_repeater = false, $repeater_parent = '')
                    {
                        foreach ($fields as $field) {
                            $field_label = esc_html($field['label']);
                            $field_name  = esc_attr($field['name']);
                            $field_type  = esc_attr($field['type']);

                            $field_slug = strtolower(str_replace(" ", "_", preg_replace("/[^a-zA-Z0-9_]/", "", $field_name)));

                            // keep tab inside repeater as a divider (not UI tab)
                            if ($field_type === 'tab') {
                                echo "<div class='acf-subtab-divider'>{$field_label}</div>";
                                continue;
                            }

                            if ($field_type === 'repeater') {
                                echo "<fieldset class='repeater-field'>";
                                echo "<legend>{$field_label}</legend>";
                                echo "<button type='button' class='field-button repeater-btn' data-name='{$field_slug}' data-type='repeater'>{$field_label}</button>";

                                if (!empty($field['sub_fields'])) {
                                    echo "<div class='repeater-subfields' style='margin-top:10px;'>";
                                    display_acf_fields_popup($field['sub_fields'], true, $field_name);
                                    echo "</div>";
                                }
                                echo "</fieldset>";
                            } else {
                                $button_class = $is_repeater ? "field-button subfield-btn" : "field-button";
                                $data_type = $is_repeater ? "subfield" : "normal";
                                echo "<button type='button' class='{$button_class}' data-name='{$field_slug}' data-parent='{$repeater_parent}' data-type='{$data_type}'>{$field_label}</button>";
                            }
                        }
                    }

                    ?>

                </div>
            </div>
        </div>


        <div id="additional_settings" class="custom-tab-content">
            <ul class="action__list">
                <?php include plugin_dir_path(__FILE__) . '../partials/partial-additional_setting.php'; ?>
            </ul>
        </div>

        <div id="wp_pages" class="custom-tab-content">
            <h2>Pages</h2>
            <!-- Search Box -->
            <div class="search-container">
                <input type="text" id="page-search" placeholder="Search pages..." class="search-input">
                <span class="search-icon">🔍</span>
            </div>

            <!-- Pages List -->
            <div class="pages-list-container">
                <div class="pages-list" id="pages-list">
                    <!-- Pages will be loaded here -->
                </div>
            </div>


        </div>

        <div id="wp_wpml" class="custom-tab-content">
            <h2>WPML – Custom Fields JSON</h2>

            <!-- Language selector (just for your own reference) -->
            <div class="wpml-language-select">
                <label for="wpml-language">Target Language:</label>
                <select id="wpml-language">
                    <option value="">Select language…</option>
                    <option value="en">English</option>
                    <option value="fr">French</option>
                    <!-- add more if needed -->
                </select>
            </div>


            <hr>

            <!-- Export section -->
            <div class="wpml-json-section">
                <h3>1. Copy JSON from current page/post</h3>
                <p>
                    Click the button below while you are on a post / page / custom post
                    <strong>edit screen</strong>. It will read all custom fields (post meta)
                    for this item and output them as JSON.
                </p>

                <button id="ai-wpml-copy-json" class="button button-secondary">
                    Copy JSON from this page
                </button>

                <p><strong>Original JSON (source language)</strong></p>
                <textarea id="wpml_json_original" rows="10" style="width:100%;"></textarea>

                <p style="margin-top:8px;">
                    <button id="ai-wpml-copy-json-only" class="button">
                        Copy JSON
                    </button>
                    <button id="ai-wpml-copy-json-with-prompt" class="button button-secondary">
                        Copy for AI (with prompt)
                    </button>
                </p>
                <p class="description">
                    The “Copy for AI” button will prepend an instruction like:<br>
                    <code>Translate this JSON to French. Only return JSON, easy to copy.</code>
                </p>

            </div>

            <hr>

            <!-- Import / update section -->
            <div class="wpml-json-section">
                <h3>2. Paste translated JSON and update fields</h3>
                <p>
                    Translate the JSON above using AI or any tool, then paste the translated
                    JSON here (keep the same structure and keys).
                </p>

                <textarea id="wpml_json_translated" rows="10" style="width:100%;"></textarea>

                <p>
                    <button id="ai-wpml-update-json" class="button button-primary">
                        Update custom fields on this page
                    </button>
                </p>

                <p class="description">
                    ⚠ Use this only on the correct translated post / page (WPML language version).
                </p>
            </div>
        </div>

    </div>
</div>






