<div class="postbox custom-field-box closed">
    <div class="postbox-header">
        <h2>Custom Fields</h2>
    </div>
    <div class="inside">
        <!-- Tab Navigation -->
        <ul class="custom-field-tabs">
            <li class="active" data-tab="create-field">Create</li>
            <li data-tab="apply-field">Apply</li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Create Custom Field -->
            <div id="create-field" class="tab-pane active">


                <div class="custom-editor">
                    <div class="acf-tab-container">
                        <div class="acf-tabs">
                            <button class="acf-tab" data-tab="normal_field">Normal Field</button>
                            <button class="acf-tab" data-tab="repeater_field">Repeater Field</button>
                        </div>

                        <div class="acf-tab-contents">
                            <!-- Normal Field -->
                            <div id="normal_field" class="acf-tab-content">
                                <div class="custom-toolbar">
                                    <button class="custom-toolbar-btn" data-shortcode="text">Text</button>
                                    <button class="custom-toolbar-btn" data-shortcode="textarea">Text Area</button>
                                    <button class="custom-toolbar-btn" data-shortcode="image">Image</button>
                                    <button class="custom-toolbar-btn" data-shortcode="wysiwyg">WYSIWYG</button>
                                    <button class="custom-toolbar-btn" data-shortcode="number">Number</button>
                                    <button class="custom-toolbar-btn" data-shortcode="url">URL</button>
                                    <button class="custom-toolbar-btn" data-shortcode="email">Email</button>
                                    <button class="custom-toolbar-btn" data-shortcode="tel">Tel</button>
                                    <button class="custom-toolbar-btn" data-shortcode="link">Link</button>
                                    <button class="custom-toolbar-btn" data-shortcode="link_array">Link(Array)</button>
                                    <div class="d-full">
                                        <button class="custom-toolbar-btn" data-shortcode="tab">Tab</button>
                                        <button class="custom-toolbar-btn" data-shortcode="checkbox">Checkboxes</button>
                                        <button class="custom-toolbar-btn" data-shortcode="radio">Radio Buttons</button>
                                    </div>
                                    <div class="d-full">
                                        <button class="custom-toobar-option" data-shortcode="options">Options</button>
                                    </div>
                                </div>
                                <div class="field__acf_wrapper">
                                    <textarea name="field__acf" id="field__acf" cols="30" rows="1"></textarea>
                                    <button id="add__field_to_textarea">Add Field</button>
                                </div>
                            </div>

                            <!-- Repeater Field -->
                            <div id="repeater_field" class="acf-tab-content" style="display: none;">
                                <div class="custom-toolbar">
                                    <div class="d-full">
                                        <button class="custom-toolbar-btn1" data-shortcode="repeater">Repeater</button>
                                        <span style="margin-left: 10px;">First click on the repeater then other sub field</span>
                                    </div>

                                    <button class="custom-toolbar-btn1" data-shortcode="text">Text</button>
                                    <button class="custom-toolbar-btn1" data-shortcode="textarea">Text Area</button>
                                    <button class="custom-toolbar-btn1" data-shortcode="image">Image</button>
                                    <button class="custom-toolbar-btn1" data-shortcode="wysiwyg">WYSIWYG</button>
                                    <button class="custom-toolbar-btn1" data-shortcode="number">Number</button>
                                    <button class="custom-toolbar-btn1" data-shortcode="url">URL</button>
                                    <button class="custom-toolbar-btn1" data-shortcode="email">Email</button>
                                    <button class="custom-toolbar-btn1" data-shortcode="tel">Tel</button>
                                    <button class="custom-toolbar-btn1" data-shortcode="link">Link</button>
                                    <button class="custom-toolbar-btn1" data-shortcode="link_array">Link(Array)</button>
                                    <div class="d-full">
                                        <button class="custom-toolbar-btn1" data-shortcode="tab">Tab</button>
                                        <button class="custom-toolbar-btn1" data-shortcode="checkbox">Checkboxes
                                        </button>
                                        <button class="custom-toolbar-btn1" data-shortcode="radio">Radio Buttons
                                        </button>
                                    </div>
                                    <?php
                                    $link_array = get_field('slug'); // Retrieve the array from the 'link' custom field
                                    if ($link_array && isset($link_array['url'])) {
                                        $link_url = esc_url($link_array['url']); // Extract the URL from the array
                                        ?>
                                        <?php echo $link_url; ?>
                                        <?php echo $link_array['title'] ?>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="field__acf_wrapper">
                                    <textarea name="field__rep_acf" id="field__rep_acf" cols="30" rows="6"></textarea>
                                    <button id="add__rep_field_to_textarea">Add Sub Field</button>
                                </div>
                                <div class="after__subfield">

                                </div>
                            </div>
                        </div>
                    </div>


                    <textarea id="custom-form-editor" spellcheck="false"
                              placeholder="Enter your custom field template here..."></textarea>
                    <div class="acf-location-container">
                        <label for="group__field">Group Field name</label>
                        <input type="text" id="group__field">

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

            <!-- Apply Custom Field -->
            <!-- Apply Custom Field -->
            <!-- Apply Custom Field -->
            <div id="apply-field" class="tab-pane">
                <h3>Apply Custom Fields</h3>
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
                            display_acf_fields($fields);
                        } else {
                            echo '<p>No fields found for this group.</p>';
                        }

                        echo '</div>';
                        echo '</div>';
                    }

                    /**
                     * Recursive function to display ACF fields
                     */
                    function display_acf_fields($fields, $is_repeater = false, $repeater_parent = '')
                    {

                        foreach ($fields as $field) {
                            $field_label = esc_html($field['label']);
                            $field_name = esc_attr($field['name']);
                            $field_type = esc_attr($field['type']);

                            // Convert name to slug
                            $field_slug = strtolower(str_replace(" ", "_", preg_replace("/[^a-zA-Z0-9_]/", "", $field_name)));

                            // ✅ Handle Tabs (`h3`) & Repeaters (`fieldset`)
                            if ($field_type === 'tab') {
                                echo "<h3 class='field-tab' data-key='{$field_slug}'>{$field_label}</h3>";
                            } elseif ($field_type === 'repeater') {
                                echo "<fieldset class='repeater-field'>";
                                echo "<legend>{$field_label}</legend>";
                                echo "<button class='field-button repeater-btn' data-name='{$field_slug}' data-type='repeater'>{$field_label}</button>";

                                // ✅ Get subfields for the repeater
                                if (!empty($field['sub_fields'])) {
                                    echo "<div class='repeater-subfields' style='margin-top: 10px;'>";
                                    display_acf_fields($field['sub_fields'], true, $field_name);
                                    echo "</div>";
                                }
                                echo "</fieldset>";
                            } else {
                                // ✅ Normal field or subfield inside repeater
                                $button_class = $is_repeater ? "field-button subfield-btn" : "field-button";
                                $data_type = $is_repeater ? "subfield" : "normal";
                                echo "<button class='{$button_class}' data-name='{$field_slug}' data-parent='{$repeater_parent}' data-type='{$data_type}'>{$field_label}</button>";
                            }
                        }
                    }

                    ?>
                </div>
            </div>


        </div>
    </div>
</div>