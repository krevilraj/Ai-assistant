<?php
function list_theme_files($dir, $relative_path = '', $base_url = '?page=ai_assistant-theme-editor&file=')
{
    $items = scandir($dir);
    $folders = $files = [];

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        $item_relative_path = $relative_path === '' ? $item : $relative_path . '/' . $item;

        if (is_dir($path)) {
            $folders[] = ['name' => $item, 'path' => $item_relative_path];
        } else {
            $files[] = ['name' => $item, 'path' => $item_relative_path];
        }
    }

    // 🔥 Folders first
    foreach ($folders as $folder) {
        echo '<li class="folder">' .
            '<span class="folder-name">' . esc_html($folder['name']) . '</span>
              <button class="delete-item" data-path="' . esc_attr($folder['path']) . '" title="Delete">
                  <span class="dashicons dashicons-trash"></span>
              </button>
              <ul class="nested">';
        list_theme_files($dir . '/' . $folder['name'], $folder['path'], $base_url);
        echo '</ul></li>';
    }

    // 📄 Then files
    foreach ($files as $file) {
        $active_class = (isset($_GET['file']) && $_GET['file'] === $file['path']) ? 'active-file' : '';
        echo "<li class='file $active_class'>
                <a href='{$base_url}" . esc_attr($file['path']) . "'>" . esc_html($file['name']) . "</a>
                <button class='delete-item' data-path='" . esc_attr($file['path']) . "' title='Delete'>
                    <span class='dashicons dashicons-trash'></span>
                </button>
              </li>";
    }
}

?>
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