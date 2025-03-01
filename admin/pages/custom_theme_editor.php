<div class="ai-theme-editor">
    <div class="theme-editor-container" style="display: flex; gap: 20px;">
        <!-- Left: Textarea Editor -->
        <div style="width: 70%;">
            <h2 id="file-title">Selected file content:</h2>
            <div class="editor-container">
                <div id="line-numbers"></div>
                <?php
                // ✅ Get file parameter from URL
                if (isset($_GET['file'])) {
                    $relative_path = sanitize_text_field($_GET['file']); // Sanitize file input
                    $theme_dir = realpath(get_stylesheet_directory());   // Absolute path to the theme directory
                    $file_path = realpath($theme_dir . '/' . $relative_path); // Resolve absolute path

                    // ✅ Check if the file exists and is within the theme directory
                    if ($file_path && strpos($file_path, $theme_dir) === 0 && file_exists($file_path)) {
                        $file_content = file_get_contents($file_path);
                    } else {
                        $file_content = "❌ File not found or access denied: " . esc_html($relative_path);
                    }
                } else {
                    $file_content = "ℹ️ Select a file from the list to view its content.";
                }

                // ✅ Output file content safely
                echo '<textarea id="theme-file-editor" placeholder="File content will appear here...">' . esc_textarea($file_content) . '</textarea>';
                ?>

                <button class="button button-primary button-disabled" id="file_save">Save</button>
            </div>
        </div>

        <!-- Right: Theme Files and Tasks -->
        <?php include plugin_dir_path(__FILE__) . '../partials/partial-sidebar.php'; ?>
    </div>
</div>
