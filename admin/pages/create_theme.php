<div class="wrap ai-theme-generator">
    <h1>Theme Generator</h1>
    <p class="ai-tg-subtitle">
        Upload a ZIP and generate pages using the existing theme or a new theme.
    </p>

    <form id="ai-theme-generator-form"
          class="ai-tg-form"
          method="post"
          enctype="multipart/form-data">

        <!-- TARGET MODE -->
        <div class="ai-tg-card">
            <h2 class="ai-tg-card-title">1) Where should we generate?</h2>

            <div class="ai-tg-radio-row">
                <label class="ai-tg-radio">
                    <input type="radio" name="ai_tg_target_mode" value="existing" checked>
                    <span class="ai-tg-radio-ui"></span>
                    <span class="ai-tg-radio-text">
                        <strong>Use existing active theme</strong>
                        <small>Recommended for Phase 1</small>
                    </span>
                </label>

                <label class="ai-tg-radio">
                    <input type="radio" name="ai_tg_target_mode" value="new">
                    <span class="ai-tg-radio-ui"></span>
                    <span class="ai-tg-radio-text">
                        <strong>Create a new theme</strong>
                        <small>Generate a fresh theme folder</small>
                    </span>
                </label>
            </div>

            <!-- NEW THEME OPTIONS -->
            <div class="ai-tg-new-theme-options">
                <div class="ai-tg-grid">

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_theme_name">Theme Name *</label>
                        <input type="text" id="ai_tg_theme_name" placeholder="My New Theme">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_theme_slug">Theme Slug</label>
                        <input type="text" id="ai_tg_theme_slug" placeholder="my-new-theme">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_text_domain">Text Domain *</label>
                        <input type="text" id="ai_tg_text_domain" placeholder="my-new-theme">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_theme_uri">Theme URI</label>
                        <input type="url" id="ai_tg_theme_uri" placeholder="https://example.com">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_author">Author</label>
                        <input type="text" id="ai_tg_author">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_author_uri">Author URI</label>
                        <input type="url" id="ai_tg_author_uri">
                    </div>

                </div>

                <label class="ai-tg-check">
                    <input type="checkbox" id="ai_tg_activate_theme">
                    <span class="ai-tg-check-ui"></span>
                    <span class="ai-tg-check-text">
                        Activate theme after generation
                        <small>(optional)</small>
                    </span>
                </label>
            </div>
        </div>

        <!-- ZIP UPLOAD -->
        <div class="ai-tg-card">
            <h2 class="ai-tg-card-title">2) Upload ZIP</h2>

            <div class="ai-field ai-tg-upload">
                <label for="ai_tg_zip">ZIP File *</label>
                <input type="file" id="ai_tg_zip" accept=".zip">
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="ai-tg-actions">
            <button type="submit" class="button button-primary ai-tg-btn">
                Upload & Generate
            </button>
            <button type="reset" class="button ai-tg-btn-secondary">
                Reset
            </button>
        </div>

        <!-- LOG -->
        <div class="ai-tg-card ai-tg-log">
            <h2 class="ai-tg-card-title">Output</h2>
            <div class="ai-tg-log-box">
                <div class="ai-tg-log-line"><span class="ai-dot"></span> Ready</div>
                <div class="ai-tg-log-line ai-muted">UI only – backend comes next.</div>
            </div>
        </div>

    </form>
</div>
<div class="ai-card">
    <h2 class="ai-card-title">Detected Pages (Root level)</h2>

    <table class="ai-table" id="ai-tg-pages-table">
        <thead>
        <tr>
            <th>File</th>
            <th>Page Title</th>
            <th>Slug</th>
            <th>Create</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Always define it to avoid warnings
        $detected_pages = isset($detected_pages) && is_array($detected_pages) ? $detected_pages : [];
        ?>
        <?php if (empty($detected_pages)) : ?>

            <?php foreach ($detected_pages as $i => $p):
                // $p example: ['path'=>'index.html','name'=>'index.html','suggested_title'=>'Home','suggested_slug'=>'home']
                ?>
                <tr class="ai-page-row">
                    <td>
                        <code><?php echo esc_html($p['path']); ?></code>
                        <input type="hidden" name="ai_tg_pages[<?php echo (int)$i; ?>][path]"
                               value="<?php echo esc_attr($p['path']); ?>">
                    </td>

                    <td>
                        <input
                                type="text"
                                class="regular-text ai-page-title"
                                name="ai_tg_pages[<?php echo (int)$i; ?>][title]"
                                value="<?php echo esc_attr($p['suggested_title']); ?>"
                                placeholder="Page title"
                                autocomplete="off"
                        >
                    </td>

                    <td>
                        <input
                                type="text"
                                class="regular-text ai-page-slug"
                                name="ai_tg_pages[<?php echo (int)$i; ?>][slug]"
                                value="<?php echo esc_attr($p['suggested_slug']); ?>"
                                placeholder="page-slug"
                                autocomplete="off"
                        >
                    </td>

                    <td style="text-align:center;">
                        <label class="ai-switch">
                            <input type="checkbox" name="ai_tg_pages[<?php echo (int)$i; ?>][create]" value="1" checked>
                            <span class="ai-switch-slider"></span>
                        </label>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <p class="description">
        Rename titles as you want — slugs will auto-update unless you manually edit the slug.
    </p>
</div>
