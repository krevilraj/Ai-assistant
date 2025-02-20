<!-- Popup -->
<div id="custom-field-popup" class="custom-field-popup">
    <div class="custom-field-popup-content">
        <span id="close-custom-field-popup">&times;</span>
        <span id="drag-popup-handle" class="drag-handle">☰</span> <!-- Drag Icon -->

        <h2>Create Custom Field</h2>

        <!-- Tabs -->
        <div class="custom-tabs">
            <button class="custom-tab active" data-tab="form">Create ACF</button>
            <button class="custom-tab" data-tab="mail">Apply ACF</button>
            <button class="custom-tab" data-tab="messages">Messages</button>
            <button class="custom-tab" data-tab="settings">Additional Settings</button>
        </div>

        <!-- Tab Content -->
        <div id="form" class="custom-tab-content active">
            <div class="custom-toolbar">
                <button class="custom-toolbar-btn" data-shortcode="tab">Tab</button>
                <button class="custom-toolbar-btn" data-shortcode="text">Text</button>
                <button class="custom-toolbar-btn" data-shortcode="email">Email</button>
                <button class="custom-toolbar-btn" data-shortcode="url">URL</button>
                <button class="custom-toolbar-btn" data-shortcode="tel">Tel</button>
                <button class="custom-toolbar-btn" data-shortcode="number">Number</button>
                <button class="custom-toolbar-btn" data-shortcode="date">Date</button>
                <button class="custom-toolbar-btn" data-shortcode="textarea">Text Area</button>
                <button class="custom-toolbar-btn" data-shortcode="dropdown">Drop-down Menu</button>
                <button class="custom-toolbar-btn" data-shortcode="checkbox">Checkboxes</button>
                <button class="custom-toolbar-btn" data-shortcode="radio">Radio Buttons</button>
                <button class="custom-toolbar-btn" data-shortcode="acceptance">Acceptance</button>
                <button class="custom-toolbar-btn" data-shortcode="quiz">Quiz</button>
                <button class="custom-toolbar-btn" data-shortcode="file">File</button>
                <button class="custom-toolbar-btn" data-shortcode="submit">Submit</button>
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

        <div id="mail" class="custom-tab-content">
            <div class="acf-location-container">
                <label>URl:</label>
                <input type="text" id="url" >
                <button id="get_custom_fields" style="margin-top: 10px;">Get Custom Fields</button>
            </div>
            <div class="acf-field-container">

            </div>
        </div>

        <div id="messages" class="custom-tab-content">
            <textarea id="messages-editor" placeholder="Enter Custom Messages..."></textarea>
        </div>

        <div id="settings" class="custom-tab-content">
            <textarea id="settings-editor" placeholder="Enter Additional Settings..."></textarea>
        </div>
    </div>
</div>
