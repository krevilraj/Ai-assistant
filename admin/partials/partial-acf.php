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
                    <button class="custom-toobar-option" data-shortcode="options">Options</button>
                </div>


            </div>

            <div class="custom-editor">
                <div class="field__acf_wrapper">

                    <textarea name="field__acf" id="field__acf" cols="30" rows="1"></textarea>
                    <button id="add__field_to_textarea">Add</button>
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
        <div id="apply-field" class="tab-pane">
            <h3>Apply Custom Fields</h3>
            <label>Select Field:</label>
            <select>
                <option value="none">-- Select a field --</option>
                <option value="field_1">Custom Field 1</option>
                <option value="field_2">Custom Field 2</option>
            </select>
            <button class="button button-secondary">Apply Field</button>
        </div>
    </div>
</div>