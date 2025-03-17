<div class="ai-theme-editor">
    <div class="text-align-right">
        <div id="ai-assistant-theme-toggle">
            <label class="theme-switch">
                <input type="checkbox" id="theme-toggle-checkbox">
                <span class="slider round"></span>
            </label>
        </div>

    </div>


    <div class="theme-editor-container" style="display: flex; gap: 20px;">
        <!-- Left: Textarea Editor -->
        <div style="width: 70%;">
            <?php
            // ✅ Get file parameter from URL
            $file_name_display = "No file selected";
            if (isset($_GET['file'])) {
                $relative_path = sanitize_text_field($_GET['file']); // Sanitize file input
                $theme_dir = realpath(get_stylesheet_directory());   // Absolute path to the theme directory
                $file_path = realpath($theme_dir . '/' . $relative_path); // Resolve absolute path

                // ✅ Extract filename from path for display
                if ($file_path && strpos($file_path, $theme_dir) === 0 && file_exists($file_path)) {
                    $file_name_display = basename($file_path);
                    $file_content = file_get_contents($file_path);
                } else {
                    $file_content = "❌ File not found or access denied: " . esc_html($relative_path);
                }
            } else {
                $file_content = "ℹ️ Select a file from the list to view its content.";
            }
            ?>

            <div class="editor__header">
                <h2 id="file-title">Editing: <span><?php echo esc_html($file_name_display); ?></span></h2>


                <div>
                    <input type="number" id="line-number-input" min="1" placeholder="Enter line no.">
                    <button id="go-to-line-btn">Go</button>
                </div>
            </div>


            <div class="editor-container">
                <div id="line-numbers"></div>
                <div class="editor-container">
                    <textarea id="theme-file-editor" spellcheck="false" placeholder="File content will appear here..."><?php echo esc_textarea($file_content); ?></textarea>
                    <button class="button button-primary button-disabled" id="file_save">Save</button>
                </div>
            </div>
        </div>

        <!-- Right: Theme Files and Tasks -->
        <?php include plugin_dir_path(__FILE__) . '../partials/partial-sidebar.php'; ?>
    </div>
</div>


