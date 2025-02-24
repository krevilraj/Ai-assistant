<?php
function list_theme_files($dir, $relative_path = '', $base_url = '?page=ai_assistant-theme-editor&file=') {
    $items = scandir($dir);
    $folders = $files = [];

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        $item_relative_path = $relative_path === '' ? $item : $relative_path . '/' . $item;

        if (is_dir($path)) $folders[] = ['name' => $item, 'path' => $item_relative_path];
        else $files[] = ['name' => $item, 'path' => $item_relative_path];
    }

    // Folders first
    foreach ($folders as $folder) {
        echo '<li class="folder">' . esc_html($folder['name']) . '<ul class="nested">';
        list_theme_files($dir . '/' . $folder['name'], $folder['path'], $base_url);
        echo '</ul></li>';
    }

    // Then files
    foreach ($files as $file) {
        $active_class = (isset($_GET['file']) && $_GET['file'] === $file['path']) ? 'active-file' : '';
        echo "<li class='file $active_class'><a href='{$base_url}" . esc_attr($file['path']) . "'>" . esc_html($file['name']) . "</a></li>";
    }
}
?>



<div class="ai-theme-editor">
    <div class="theme-editor-container" style="display: flex; gap: 20px;">
        <!-- Left: Textarea Editor -->
        <div style="width: 70%;">
            <h2 id="file-title">Selected file content:</h2>
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

        </div>

        <!-- Right: Theme Files and Tasks -->
        <?php include plugin_dir_path(__FILE__) . '../partials/partial-sidebar.php'; ?>
    </div>
</div>
