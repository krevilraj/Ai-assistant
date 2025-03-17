<div class="postbox closed" id="ai__task">
    <div class="postbox-header">
        <h2>Customizer Builder</h2>
    </div>
    <div class="inside">
        <nav class="customizer-tabs">
            <ul>
                <li data-tab="create-customizer" class="active">Create</li>
                <li data-tab="apply-customizer">Apply</li>
            </ul>
        </nav>

        <!-- ✅ Create Customizer Section -->
        <div data-tab-content="create-customizer" class="customizer__content active">
            <div class="custom-toolbar">
                <button class="customizer__btn" data-shortcode="create__section">Add Section</button>
                <div>
                    <button class="customizer__btn" data-shortcode="text">Text</button>
                    <button class="customizer__btn" data-shortcode="textarea">Textarea</button>
                    <button class="customizer__btn" data-shortcode="url">URL</button>
                    <button class="customizer__btn" data-shortcode="image">Image</button>
                    <button class="customizer__btn" data-shortcode="color">Color Picker</button>
                    <button class="customizer__btn" data-shortcode="checkbox">Checkbox</button>
                    <button class="customizer__btn" data-shortcode="radio">Radio</button>
                </div>
                <div>
                    <button class="customizer__btn" data-shortcode="social">Social</button>
                    <button class="customizer__btn" data-shortcode="contact">Contact</button>
                </div>
            </div>

            <div class="field__customizer_wrapper">
                <textarea name="field__customizer" id="field__customizer" cols="50" rows="15"></textarea>
                <label>Text domain</label>
                <input type="text" name="text_domain" value="<?php echo get_option('ai_assistant_text_domain', '');?>">
                <div>
                    <button id="add__field_to_textarea">Create Customizer</button>
                </div>
            </div>
        </div>

        <!-- ✅ Apply Customizer Section -->
        <div data-tab-content="apply-customizer" class="available_customizer">
            <div class="customizer-accordion">
                <?php
                // ✅ Fetch Customizer Data
                $customizer_dir = get_template_directory() . '/customizer/';
                $customizer_files = glob($customizer_dir . '*.php');

                $sections = [];

                if ($customizer_files) {
                    foreach ($customizer_files as $file) {
                        $content = file_get_contents($file);

                        // ✅ Extract Section Names
                        if (preg_match("/add_section\(\s*'([^']+)',\s*array\(\s*'title'\s*=>\s*__\('([^']+)'/", $content, $matches)) {
                            $section_id = $matches[1];
                            $section_name = $matches[2];

                            $sections[$section_id] = [
                                'name' => $section_name,
                                'settings' => []
                            ];
                        }

                        // ✅ Extract Settings
                        if (preg_match_all("/add_setting\(\s*'([^']+)'/", $content, $setting_matches)) {
                            foreach ($setting_matches[1] as $setting) {
                                if (!empty($sections)) {
                                    $last_section = array_key_last($sections);
                                    $sections[$last_section]['settings'][] = $setting;
                                }
                            }
                        }
                    }
                }
                ?>

                <?php if (!empty($sections)) : ?>
                    <?php foreach ($sections as $section_id => $section) : ?>
                        <div class="accordion-item">
                            <h3 class="accordion-toggle"><?php echo esc_html($section['name']); ?></h3>
                            <div class="accordion-body">
                                <?php if (!empty($section['settings'])) : ?>
                                    <?php foreach ($section['settings'] as $setting) : ?>
                                        <button class="copy-btn" data-slug="<?php echo esc_attr($setting); ?>">
                                            <?php echo esc_html(str_replace("_", " ", $setting)); ?>
                                        </button>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <p>No settings found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p style="color: red;">⚠ No Customizer Sections Found! Check folder path.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
