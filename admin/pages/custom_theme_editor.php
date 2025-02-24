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
        <div style="width: 30%;">
            <!-- Theme Files Box -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>Theme Files</h2>
                </div>
                <div class="inside">
                    <!-- ✅ HTML Structure -->
                    <ul class="file-list" id="theme-files-list">
                        <?php list_theme_files(get_stylesheet_directory()); ?>
                    </ul>


                </div>
            </div>

            <!-- Tasks Box -->
            <div class="postbox closed">
                <div class="postbox-header">
                    <h2>Tasks</h2>
                </div>
                <div class="inside">
                    <ul class="action__list task-list">
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

                        <li>
                            <span class="open__child">Solve Menu</span>
                            <div class="action__setting">


                                <?php
                                $menus = wp_get_nav_menus();
                                if (!empty($menus)) {
                                    echo '<select name="menu__name">';
                                    foreach ($menus as $menu) {
                                        echo '<option value="' . esc_attr($menu->slug) . '">' . esc_html($menu->name) . '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    echo '<p>No menus available.</p>';
                                }
                                ?>

                                <textarea id="menu-editor" rows="10" placeholder="Paste or write menu HTML here..."></textarea>
                                <?php ai_assistant_render_spark_button('correct_menu'); ?>


                            </div>
                        </li>


                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
