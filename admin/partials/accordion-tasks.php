<div class="postbox closed" id="ai__task">
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
                    <div class="text-align-right">
                        <textarea name="correct_header" id="" cols="30" rows="10"
                                  placeholder="Put all the content of the static header"></textarea>

                        <?php ai_assistant_render_spark_button('correct_header'); ?>
                    </div>

                </div>
            </li>

            <li>
                <span class="open__child">Correct Footer</span>
                <div class="action__setting">
                    <div class="text-align-right">
                        <textarea name="correct_footer" id="" cols="30" rows="10"
                                  placeholder="Put all the content of the static footer"></textarea>
                        <?php ai_assistant_render_spark_button('correct_footer'); ?>
                    </div>
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
                    <p><em>Note: Please select the static menu from the editor. It replace with dynamic wordpress menu
                            to the editor then save it.</em></p>
                    <?php ai_assistant_render_spark_button('correct_menu'); ?>


                </div>
            </li>
            <li>
                <span class="open__child">Create Custom Post Type</span>
                <div class="action__setting">
                    <input type="text" name="cpt_slug" placeholder="Post Type Slug">
                    <input type="text" name="plural__label" placeholder="Plural Label">
                    <input type="text" name="singular__label" placeholder="Singular Label">

                    <?php ai_assistant_render_spark_button('create_custom_post_type'); ?>

                    <label>
                        <input type="checkbox" name="cpt__template" checked> Create Single Page
                    </label>
                    <label>
                        <input type="checkbox" name="cpt__archive_template" checked> Create Archive Page
                    </label>

                    <div class="d-flex no-of-posts">
                        <p>No of Post in Archive</p>
                        <input type="number" name="no_of_posts" placeholder="Post per page" min="1" value="1">
                    </div>

                    <h3>Supports</h3>
                    <div style="display: flex;flex-direction: column; gap: 10px;">
                        <input type="text" class="dashi_icon_field" name="dashi_icon" placeholder="Click to select icon"
                               readonly>
                        <button type="button" class=" open-dashicon-picker button button-primary">Choose Dashicon
                        </button>

                    </div>

                    <label>
                        <input type="checkbox" name="cpt__title" checked> Title
                    </label>
                    <label>
                        <input type="checkbox" name="cpt__editor" checked> Editor
                    </label>
                    <label>
                        <input type="checkbox" name="cpt__featured_image" checked> Featured Image
                    </label>
                </div>

            </li>

        </ul>
    </div>
</div>

<div id="dashicon-picker-modal" style="display:none;">
    <div id="dashicon-picker-overlay"></div>
    <div id="dashicon-picker-content">
        <h3>Select a Dashicon</h3>
        <ul class="dashicon-picker-list">
            <li data-icon="menu"><a href="#" title="menu"><span class="dashicons dashicons-menu"></span></a></li>
            <li data-icon="admin-site"><a href="#" title="admin-site"><span
                            class="dashicons dashicons-admin-site"></span></a></li>
            <li data-icon="dashboard"><a href="#" title="dashboard"><span
                            class="dashicons dashicons-dashboard"></span></a></li>
            <li data-icon="admin-media"><a href="#" title="admin-media"><span
                            class="dashicons dashicons-admin-media"></span></a></li>
            <li data-icon="admin-page"><a href="#" title="admin-page"><span
                            class="dashicons dashicons-admin-page"></span></a></li>
            <li data-icon="admin-comments"><a href="#" title="admin-comments"><span
                            class="dashicons dashicons-admin-comments"></span></a></li>
            <li data-icon="admin-appearance"><a href="#" title="admin-appearance"><span
                            class="dashicons dashicons-admin-appearance"></span></a></li>
            <li data-icon="admin-plugins"><a href="#" title="admin-plugins"><span
                            class="dashicons dashicons-admin-plugins"></span></a></li>
            <li data-icon="admin-users"><a href="#" title="admin-users"><span
                            class="dashicons dashicons-admin-users"></span></a></li>
            <li data-icon="admin-tools"><a href="#" title="admin-tools"><span
                            class="dashicons dashicons-admin-tools"></span></a></li>
            <li data-icon="admin-settings"><a href="#" title="admin-settings"><span
                            class="dashicons dashicons-admin-settings"></span></a></li>
            <li data-icon="admin-network"><a href="#" title="admin-network"><span
                            class="dashicons dashicons-admin-network"></span></a></li>
            <li data-icon="admin-generic"><a href="#" title="admin-generic"><span
                            class="dashicons dashicons-admin-generic"></span></a></li>
            <li data-icon="admin-home"><a href="#" title="admin-home"><span
                            class="dashicons dashicons-admin-home"></span></a></li>
            <li data-icon="admin-collapse"><a href="#" title="admin-collapse"><span
                            class="dashicons dashicons-admin-collapse"></span></a></li>
            <li data-icon="filter"><a href="#" title="filter"><span class="dashicons dashicons-filter"></span></a>
            </li>
            <li data-icon="admin-customizer"><a href="#" title="admin-customizer"><span
                            class="dashicons dashicons-admin-customizer"></span></a></li>
            <li data-icon="admin-multisite"><a href="#" title="admin-multisite"><span
                            class="dashicons dashicons-admin-multisite"></span></a></li>
            <li data-icon="admin-links"><a href="#" title="admin-links"><span
                            class="dashicons dashicons-admin-links"></span></a></li>
            <li data-icon="format-links"><a href="#" title="format-links"><span
                            class="dashicons dashicons-format-links"></span></a></li>
            <li data-icon="admin-post"><a href="#" title="admin-post"><span
                            class="dashicons dashicons-admin-post"></span></a></li>
            <li data-icon="format-standard"><a href="#" title="format-standard"><span
                            class="dashicons dashicons-format-standard"></span></a></li>
            <li data-icon="format-image"><a href="#" title="format-image"><span
                            class="dashicons dashicons-format-image"></span></a></li>
            <li data-icon="format-gallery"><a href="#" title="format-gallery"><span
                            class="dashicons dashicons-format-gallery"></span></a></li>
            <li data-icon="format-audio"><a href="#" title="format-audio"><span
                            class="dashicons dashicons-format-audio"></span></a></li>
            <li data-icon="format-video"><a href="#" title="format-video"><span
                            class="dashicons dashicons-format-video"></span></a></li>
            <li data-icon="format-chat"><a href="#" title="format-chat"><span
                            class="dashicons dashicons-format-chat"></span></a></li>
            <li data-icon="format-status"><a href="#" title="format-status"><span
                            class="dashicons dashicons-format-status"></span></a></li>
            <li data-icon="format-aside"><a href="#" title="format-aside"><span
                            class="dashicons dashicons-format-aside"></span></a></li>
            <li data-icon="format-quote"><a href="#" title="format-quote"><span
                            class="dashicons dashicons-format-quote"></span></a></li>
            <li data-icon="welcome-write-blog"><a href="#" title="welcome-write-blog"><span
                            class="dashicons dashicons-welcome-write-blog"></span></a></li>
            <li data-icon="welcome-edit-page"><a href="#" title="welcome-edit-page"><span
                            class="dashicons dashicons-welcome-edit-page"></span></a></li>
            <li data-icon="welcome-add-page"><a href="#" title="welcome-add-page"><span
                            class="dashicons dashicons-welcome-add-page"></span></a></li>
            <li data-icon="welcome-view-site"><a href="#" title="welcome-view-site"><span
                            class="dashicons dashicons-welcome-view-site"></span></a></li>
            <li data-icon="welcome-widgets-menus"><a href="#" title="welcome-widgets-menus"><span
                            class="dashicons dashicons-welcome-widgets-menus"></span></a></li>
            <li data-icon="welcome-comments"><a href="#" title="welcome-comments"><span
                            class="dashicons dashicons-welcome-comments"></span></a></li>
            <li data-icon="welcome-learn-more"><a href="#" title="welcome-learn-more"><span
                            class="dashicons dashicons-welcome-learn-more"></span></a></li>
            <li data-icon="image-crop"><a href="#" title="image-crop"><span
                            class="dashicons dashicons-image-crop"></span></a></li>
            <li data-icon="image-rotate"><a href="#" title="image-rotate"><span
                            class="dashicons dashicons-image-rotate"></span></a></li>
            <li data-icon="image-rotate-left"><a href="#" title="image-rotate-left"><span
                            class="dashicons dashicons-image-rotate-left"></span></a></li>
            <li data-icon="image-rotate-right"><a href="#" title="image-rotate-right"><span
                            class="dashicons dashicons-image-rotate-right"></span></a></li>
            <li data-icon="image-flip-vertical"><a href="#" title="image-flip-vertical"><span
                            class="dashicons dashicons-image-flip-vertical"></span></a></li>
            <li data-icon="image-flip-horizontal"><a href="#" title="image-flip-horizontal"><span
                            class="dashicons dashicons-image-flip-horizontal"></span></a></li>
            <li data-icon="image-filter"><a href="#" title="image-filter"><span
                            class="dashicons dashicons-image-filter"></span></a></li>
            <li data-icon="undo"><a href="#" title="undo"><span class="dashicons dashicons-undo"></span></a></li>
            <li data-icon="redo"><a href="#" title="redo"><span class="dashicons dashicons-redo"></span></a></li>
            <li data-icon="editor-bold"><a href="#" title="editor-bold"><span
                            class="dashicons dashicons-editor-bold"></span></a></li>
            <li data-icon="editor-italic"><a href="#" title="editor-italic"><span
                            class="dashicons dashicons-editor-italic"></span></a></li>
            <li data-icon="editor-ul"><a href="#" title="editor-ul"><span
                            class="dashicons dashicons-editor-ul"></span></a></li>
            <li data-icon="editor-ol"><a href="#" title="editor-ol"><span
                            class="dashicons dashicons-editor-ol"></span></a></li>
            <li data-icon="editor-quote"><a href="#" title="editor-quote"><span
                            class="dashicons dashicons-editor-quote"></span></a></li>
            <li data-icon="editor-alignleft"><a href="#" title="editor-alignleft"><span
                            class="dashicons dashicons-editor-alignleft"></span></a></li>
            <li data-icon="editor-aligncenter"><a href="#" title="editor-aligncenter"><span
                            class="dashicons dashicons-editor-aligncenter"></span></a></li>
            <li data-icon="editor-alignright"><a href="#" title="editor-alignright"><span
                            class="dashicons dashicons-editor-alignright"></span></a></li>
            <li data-icon="editor-insertmore"><a href="#" title="editor-insertmore"><span
                            class="dashicons dashicons-editor-insertmore"></span></a></li>
            <li data-icon="editor-spellcheck"><a href="#" title="editor-spellcheck"><span
                            class="dashicons dashicons-editor-spellcheck"></span></a></li>
            <li data-icon="editor-distractionfree"><a href="#" title="editor-distractionfree"><span
                            class="dashicons dashicons-editor-distractionfree"></span></a></li>
            <li data-icon="editor-expand"><a href="#" title="editor-expand"><span
                            class="dashicons dashicons-editor-expand"></span></a></li>
            <li data-icon="editor-contract"><a href="#" title="editor-contract"><span
                            class="dashicons dashicons-editor-contract"></span></a></li>
            <li data-icon="editor-kitchensink"><a href="#" title="editor-kitchensink"><span
                            class="dashicons dashicons-editor-kitchensink"></span></a></li>
            <li data-icon="editor-underline"><a href="#" title="editor-underline"><span
                            class="dashicons dashicons-editor-underline"></span></a></li>
            <li data-icon="editor-justify"><a href="#" title="editor-justify"><span
                            class="dashicons dashicons-editor-justify"></span></a></li>
            <li data-icon="editor-textcolor"><a href="#" title="editor-textcolor"><span
                            class="dashicons dashicons-editor-textcolor"></span></a></li>
            <li data-icon="editor-paste-word"><a href="#" title="editor-paste-word"><span
                            class="dashicons dashicons-editor-paste-word"></span></a></li>
            <li data-icon="editor-paste-text"><a href="#" title="editor-paste-text"><span
                            class="dashicons dashicons-editor-paste-text"></span></a></li>
            <li data-icon="editor-removeformatting"><a href="#" title="editor-removeformatting"><span
                            class="dashicons dashicons-editor-removeformatting"></span></a></li>
            <li data-icon="editor-video"><a href="#" title="editor-video"><span
                            class="dashicons dashicons-editor-video"></span></a></li>
            <li data-icon="editor-customchar"><a href="#" title="editor-customchar"><span
                            class="dashicons dashicons-editor-customchar"></span></a></li>
            <li data-icon="editor-outdent"><a href="#" title="editor-outdent"><span
                            class="dashicons dashicons-editor-outdent"></span></a></li>
            <li data-icon="editor-indent"><a href="#" title="editor-indent"><span
                            class="dashicons dashicons-editor-indent"></span></a></li>
            <li data-icon="editor-help"><a href="#" title="editor-help"><span
                            class="dashicons dashicons-editor-help"></span></a></li>
            <li data-icon="editor-strikethrough"><a href="#" title="editor-strikethrough"><span
                            class="dashicons dashicons-editor-strikethrough"></span></a></li>
            <li data-icon="editor-unlink"><a href="#" title="editor-unlink"><span
                            class="dashicons dashicons-editor-unlink"></span></a></li>
            <li data-icon="editor-rtl"><a href="#" title="editor-rtl"><span
                            class="dashicons dashicons-editor-rtl"></span></a></li>
            <li data-icon="editor-break"><a href="#" title="editor-break"><span
                            class="dashicons dashicons-editor-break"></span></a></li>
            <li data-icon="editor-code"><a href="#" title="editor-code"><span
                            class="dashicons dashicons-editor-code"></span></a></li>
            <li data-icon="editor-paragraph"><a href="#" title="editor-paragraph"><span
                            class="dashicons dashicons-editor-paragraph"></span></a></li>
            <li data-icon="editor-table"><a href="#" title="editor-table"><span
                            class="dashicons dashicons-editor-table"></span></a></li>
            <li data-icon="align-left"><a href="#" title="align-left"><span
                            class="dashicons dashicons-align-left"></span></a></li>
            <li data-icon="align-right"><a href="#" title="align-right"><span
                            class="dashicons dashicons-align-right"></span></a></li>
            <li data-icon="align-center"><a href="#" title="align-center"><span
                            class="dashicons dashicons-align-center"></span></a></li>
            <li data-icon="align-none"><a href="#" title="align-none"><span
                            class="dashicons dashicons-align-none"></span></a></li>
            <li data-icon="lock"><a href="#" title="lock"><span class="dashicons dashicons-lock"></span></a></li>
            <li data-icon="unlock"><a href="#" title="unlock"><span class="dashicons dashicons-unlock"></span></a>
            </li>
            <li data-icon="calendar"><a href="#" title="calendar"><span class="dashicons dashicons-calendar"></span></a>
            </li>
            <li data-icon="calendar-alt"><a href="#" title="calendar-alt"><span
                            class="dashicons dashicons-calendar-alt"></span></a></li>
            <li data-icon="visibility"><a href="#" title="visibility"><span
                            class="dashicons dashicons-visibility"></span></a></li>
            <li data-icon="hidden"><a href="#" title="hidden"><span class="dashicons dashicons-hidden"></span></a>
            </li>
            <li data-icon="post-status"><a href="#" title="post-status"><span
                            class="dashicons dashicons-post-status"></span></a></li>
            <li data-icon="edit"><a href="#" title="edit"><span class="dashicons dashicons-edit"></span></a></li>
            <li data-icon="post-trash"><a href="#" title="post-trash"><span
                            class="dashicons dashicons-post-trash"></span></a></li>
            <li data-icon="trash"><a href="#" title="trash"><span class="dashicons dashicons-trash"></span></a></li>
            <li data-icon="sticky"><a href="#" title="sticky"><span class="dashicons dashicons-sticky"></span></a>
            </li>
            <li data-icon="external"><a href="#" title="external"><span class="dashicons dashicons-external"></span></a>
            </li>
            <li data-icon="arrow-up"><a href="#" title="arrow-up"><span class="dashicons dashicons-arrow-up"></span></a>
            </li>
            <li data-icon="arrow-down"><a href="#" title="arrow-down"><span
                            class="dashicons dashicons-arrow-down"></span></a></li>
            <li data-icon="arrow-left"><a href="#" title="arrow-left"><span
                            class="dashicons dashicons-arrow-left"></span></a></li>
            <li data-icon="arrow-right"><a href="#" title="arrow-right"><span
                            class="dashicons dashicons-arrow-right"></span></a></li>
            <li data-icon="arrow-up-alt"><a href="#" title="arrow-up-alt"><span
                            class="dashicons dashicons-arrow-up-alt"></span></a></li>
            <li data-icon="arrow-down-alt"><a href="#" title="arrow-down-alt"><span
                            class="dashicons dashicons-arrow-down-alt"></span></a></li>
            <li data-icon="arrow-left-alt"><a href="#" title="arrow-left-alt"><span
                            class="dashicons dashicons-arrow-left-alt"></span></a></li>
            <li data-icon="arrow-right-alt"><a href="#" title="arrow-right-alt"><span
                            class="dashicons dashicons-arrow-right-alt"></span></a></li>
            <li data-icon="arrow-up-alt2"><a href="#" title="arrow-up-alt2"><span
                            class="dashicons dashicons-arrow-up-alt2"></span></a></li>
            <li data-icon="arrow-down-alt2"><a href="#" title="arrow-down-alt2"><span
                            class="dashicons dashicons-arrow-down-alt2"></span></a></li>
            <li data-icon="arrow-left-alt2"><a href="#" title="arrow-left-alt2"><span
                            class="dashicons dashicons-arrow-left-alt2"></span></a></li>
            <li data-icon="arrow-right-alt2"><a href="#" title="arrow-right-alt2"><span
                            class="dashicons dashicons-arrow-right-alt2"></span></a></li>
            <li data-icon="leftright"><a href="#" title="leftright"><span
                            class="dashicons dashicons-leftright"></span></a></li>
            <li data-icon="sort"><a href="#" title="sort"><span class="dashicons dashicons-sort"></span></a></li>
            <li data-icon="randomize"><a href="#" title="randomize"><span
                            class="dashicons dashicons-randomize"></span></a></li>
            <li data-icon="list-view"><a href="#" title="list-view"><span
                            class="dashicons dashicons-list-view"></span></a></li>
            <li data-icon="excerpt-view"><a href="#" title="excerpt-view"><span
                            class="dashicons dashicons-excerpt-view"></span></a></li>
            <li data-icon="grid-view"><a href="#" title="grid-view"><span
                            class="dashicons dashicons-grid-view"></span></a></li>
            <li data-icon="hammer"><a href="#" title="hammer"><span class="dashicons dashicons-hammer"></span></a>
            </li>
            <li data-icon="art"><a href="#" title="art"><span class="dashicons dashicons-art"></span></a></li>
            <li data-icon="migrate"><a href="#" title="migrate"><span
                            class="dashicons dashicons-migrate"></span></a></li>
            <li data-icon="performance"><a href="#" title="performance"><span
                            class="dashicons dashicons-performance"></span></a></li>
            <li data-icon="universal-access"><a href="#" title="universal-access"><span
                            class="dashicons dashicons-universal-access"></span></a></li>
            <li data-icon="universal-access-alt"><a href="#" title="universal-access-alt"><span
                            class="dashicons dashicons-universal-access-alt"></span></a></li>
            <li data-icon="tickets"><a href="#" title="tickets"><span
                            class="dashicons dashicons-tickets"></span></a></li>
            <li data-icon="nametag"><a href="#" title="nametag"><span
                            class="dashicons dashicons-nametag"></span></a></li>
            <li data-icon="clipboard"><a href="#" title="clipboard"><span
                            class="dashicons dashicons-clipboard"></span></a></li>
            <li data-icon="heart"><a href="#" title="heart"><span class="dashicons dashicons-heart"></span></a></li>
            <li data-icon="megaphone"><a href="#" title="megaphone"><span
                            class="dashicons dashicons-megaphone"></span></a></li>
            <li data-icon="schedule"><a href="#" title="schedule"><span class="dashicons dashicons-schedule"></span></a>
            </li>
            <li data-icon="wordpress"><a href="#" title="wordpress"><span
                            class="dashicons dashicons-wordpress"></span></a></li>
            <li data-icon="wordpress-alt"><a href="#" title="wordpress-alt"><span
                            class="dashicons dashicons-wordpress-alt"></span></a></li>
            <li data-icon="pressthis"><a href="#" title="pressthis"><span
                            class="dashicons dashicons-pressthis"></span></a></li>
            <li data-icon="update"><a href="#" title="update"><span class="dashicons dashicons-update"></span></a>
            </li>
            <li data-icon="screenoptions"><a href="#" title="screenoptions"><span
                            class="dashicons dashicons-screenoptions"></span></a></li>
            <li data-icon="cart"><a href="#" title="cart"><span class="dashicons dashicons-cart"></span></a></li>
            <li data-icon="feedback"><a href="#" title="feedback"><span class="dashicons dashicons-feedback"></span></a>
            </li>
            <li data-icon="cloud"><a href="#" title="cloud"><span class="dashicons dashicons-cloud"></span></a></li>
            <li data-icon="translation"><a href="#" title="translation"><span
                            class="dashicons dashicons-translation"></span></a></li>
            <li data-icon="tag"><a href="#" title="tag"><span class="dashicons dashicons-tag"></span></a></li>
            <li data-icon="category"><a href="#" title="category"><span class="dashicons dashicons-category"></span></a>
            </li>
            <li data-icon="archive"><a href="#" title="archive"><span
                            class="dashicons dashicons-archive"></span></a></li>
            <li data-icon="tagcloud"><a href="#" title="tagcloud"><span class="dashicons dashicons-tagcloud"></span></a>
            </li>
            <li data-icon="text"><a href="#" title="text"><span class="dashicons dashicons-text"></span></a></li>
            <li data-icon="media-archive"><a href="#" title="media-archive"><span
                            class="dashicons dashicons-media-archive"></span></a></li>
            <li data-icon="media-audio"><a href="#" title="media-audio"><span
                            class="dashicons dashicons-media-audio"></span></a></li>
            <li data-icon="media-code"><a href="#" title="media-code"><span
                            class="dashicons dashicons-media-code"></span></a></li>
            <li data-icon="media-default"><a href="#" title="media-default"><span
                            class="dashicons dashicons-media-default"></span></a></li>
            <li data-icon="media-document"><a href="#" title="media-document"><span
                            class="dashicons dashicons-media-document"></span></a></li>
            <li data-icon="media-interactive"><a href="#" title="media-interactive"><span
                            class="dashicons dashicons-media-interactive"></span></a></li>
            <li data-icon="media-spreadsheet"><a href="#" title="media-spreadsheet"><span
                            class="dashicons dashicons-media-spreadsheet"></span></a></li>
            <li data-icon="media-text"><a href="#" title="media-text"><span
                            class="dashicons dashicons-media-text"></span></a></li>
            <li data-icon="media-video"><a href="#" title="media-video"><span
                            class="dashicons dashicons-media-video"></span></a></li>
            <li data-icon="playlist-audio"><a href="#" title="playlist-audio"><span
                            class="dashicons dashicons-playlist-audio"></span></a></li>
            <li data-icon="playlist-video"><a href="#" title="playlist-video"><span
                            class="dashicons dashicons-playlist-video"></span></a></li>
            <li data-icon="controls-play"><a href="#" title="controls-play"><span
                            class="dashicons dashicons-controls-play"></span></a></li>
            <li data-icon="controls-pause"><a href="#" title="controls-pause"><span
                            class="dashicons dashicons-controls-pause"></span></a></li>
            <li data-icon="controls-forward"><a href="#" title="controls-forward"><span
                            class="dashicons dashicons-controls-forward"></span></a></li>
            <li data-icon="controls-skipforward"><a href="#" title="controls-skipforward"><span
                            class="dashicons dashicons-controls-skipforward"></span></a></li>
            <li data-icon="controls-back"><a href="#" title="controls-back"><span
                            class="dashicons dashicons-controls-back"></span></a></li>
            <li data-icon="controls-skipback"><a href="#" title="controls-skipback"><span
                            class="dashicons dashicons-controls-skipback"></span></a></li>
            <li data-icon="controls-repeat"><a href="#" title="controls-repeat"><span
                            class="dashicons dashicons-controls-repeat"></span></a></li>
            <li data-icon="controls-volumeon"><a href="#" title="controls-volumeon"><span
                            class="dashicons dashicons-controls-volumeon"></span></a></li>
            <li data-icon="controls-volumeoff"><a href="#" title="controls-volumeoff"><span
                            class="dashicons dashicons-controls-volumeoff"></span></a></li>
            <li data-icon="yes"><a href="#" title="yes"><span class="dashicons dashicons-yes"></span></a></li>
            <li data-icon="no"><a href="#" title="no"><span class="dashicons dashicons-no"></span></a></li>
            <li data-icon="no-alt"><a href="#" title="no-alt"><span class="dashicons dashicons-no-alt"></span></a>
            </li>
            <li data-icon="plus"><a href="#" title="plus"><span class="dashicons dashicons-plus"></span></a></li>
            <li data-icon="plus-alt"><a href="#" title="plus-alt"><span class="dashicons dashicons-plus-alt"></span></a>
            </li>
            <li data-icon="plus-alt2"><a href="#" title="plus-alt2"><span
                            class="dashicons dashicons-plus-alt2"></span></a></li>
            <li data-icon="minus"><a href="#" title="minus"><span class="dashicons dashicons-minus"></span></a></li>
            <li data-icon="dismiss"><a href="#" title="dismiss"><span
                            class="dashicons dashicons-dismiss"></span></a></li>
            <li data-icon="marker"><a href="#" title="marker"><span class="dashicons dashicons-marker"></span></a>
            </li>
            <li data-icon="star-filled"><a href="#" title="star-filled"><span
                            class="dashicons dashicons-star-filled"></span></a></li>
            <li data-icon="star-half"><a href="#" title="star-half"><span
                            class="dashicons dashicons-star-half"></span></a></li>
            <li data-icon="star-empty"><a href="#" title="star-empty"><span
                            class="dashicons dashicons-star-empty"></span></a></li>
            <li data-icon="flag"><a href="#" title="flag"><span class="dashicons dashicons-flag"></span></a></li>
            <li data-icon="info"><a href="#" title="info"><span class="dashicons dashicons-info"></span></a></li>
            <li data-icon="warning"><a href="#" title="warning"><span
                            class="dashicons dashicons-warning"></span></a></li>
            <li data-icon="share"><a href="#" title="share"><span class="dashicons dashicons-share"></span></a></li>
            <li data-icon="share1"><a href="#" title="share1"><span class="dashicons dashicons-share1"></span></a>
            </li>
            <li data-icon="share-alt"><a href="#" title="share-alt"><span
                            class="dashicons dashicons-share-alt"></span></a></li>
            <li data-icon="share-alt2"><a href="#" title="share-alt2"><span
                            class="dashicons dashicons-share-alt2"></span></a></li>
            <li data-icon="twitter"><a href="#" title="twitter"><span
                            class="dashicons dashicons-twitter"></span></a></li>
            <li data-icon="rss"><a href="#" title="rss"><span class="dashicons dashicons-rss"></span></a></li>
            <li data-icon="email"><a href="#" title="email"><span class="dashicons dashicons-email"></span></a></li>
            <li data-icon="email-alt"><a href="#" title="email-alt"><span
                            class="dashicons dashicons-email-alt"></span></a></li>
            <li data-icon="facebook"><a href="#" title="facebook"><span class="dashicons dashicons-facebook"></span></a>
            </li>
            <li data-icon="facebook-alt"><a href="#" title="facebook-alt"><span
                            class="dashicons dashicons-facebook-alt"></span></a></li>
            <li data-icon="networking"><a href="#" title="networking"><span
                            class="dashicons dashicons-networking"></span></a></li>
            <li data-icon="googleplus"><a href="#" title="googleplus"><span
                            class="dashicons dashicons-googleplus"></span></a></li>
            <li data-icon="location"><a href="#" title="location"><span class="dashicons dashicons-location"></span></a>
            </li>
            <li data-icon="location-alt"><a href="#" title="location-alt"><span
                            class="dashicons dashicons-location-alt"></span></a></li>
            <li data-icon="camera"><a href="#" title="camera"><span class="dashicons dashicons-camera"></span></a>
            </li>
            <li data-icon="images-alt"><a href="#" title="images-alt"><span
                            class="dashicons dashicons-images-alt"></span></a></li>
            <li data-icon="images-alt2"><a href="#" title="images-alt2"><span
                            class="dashicons dashicons-images-alt2"></span></a></li>
            <li data-icon="video-alt"><a href="#" title="video-alt"><span
                            class="dashicons dashicons-video-alt"></span></a></li>
            <li data-icon="video-alt2"><a href="#" title="video-alt2"><span
                            class="dashicons dashicons-video-alt2"></span></a></li>
            <li data-icon="video-alt3"><a href="#" title="video-alt3"><span
                            class="dashicons dashicons-video-alt3"></span></a></li>
            <li data-icon="vault"><a href="#" title="vault"><span class="dashicons dashicons-vault"></span></a></li>
            <li data-icon="shield"><a href="#" title="shield"><span class="dashicons dashicons-shield"></span></a>
            </li>
            <li data-icon="shield-alt"><a href="#" title="shield-alt"><span
                            class="dashicons dashicons-shield-alt"></span></a></li>
            <li data-icon="sos"><a href="#" title="sos"><span class="dashicons dashicons-sos"></span></a></li>
            <li data-icon="search"><a href="#" title="search"><span class="dashicons dashicons-search"></span></a>
            </li>
            <li data-icon="slides"><a href="#" title="slides"><span class="dashicons dashicons-slides"></span></a>
            </li>
            <li data-icon="analytics"><a href="#" title="analytics"><span
                            class="dashicons dashicons-analytics"></span></a></li>
            <li data-icon="chart-pie"><a href="#" title="chart-pie"><span
                            class="dashicons dashicons-chart-pie"></span></a></li>
            <li data-icon="chart-bar"><a href="#" title="chart-bar"><span
                            class="dashicons dashicons-chart-bar"></span></a></li>
            <li data-icon="chart-line"><a href="#" title="chart-line"><span
                            class="dashicons dashicons-chart-line"></span></a></li>
            <li data-icon="chart-area"><a href="#" title="chart-area"><span
                            class="dashicons dashicons-chart-area"></span></a></li>
            <li data-icon="groups"><a href="#" title="groups"><span class="dashicons dashicons-groups"></span></a>
            </li>
            <li data-icon="businessman"><a href="#" title="businessman"><span
                            class="dashicons dashicons-businessman"></span></a></li>
            <li data-icon="id"><a href="#" title="id"><span class="dashicons dashicons-id"></span></a></li>
            <li data-icon="id-alt"><a href="#" title="id-alt"><span class="dashicons dashicons-id-alt"></span></a>
            </li>
            <li data-icon="products"><a href="#" title="products"><span class="dashicons dashicons-products"></span></a>
            </li>
            <li data-icon="awards"><a href="#" title="awards"><span class="dashicons dashicons-awards"></span></a>
            </li>
            <li data-icon="forms"><a href="#" title="forms"><span class="dashicons dashicons-forms"></span></a></li>
            <li data-icon="testimonial"><a href="#" title="testimonial"><span
                            class="dashicons dashicons-testimonial"></span></a></li>
            <li data-icon="portfolio"><a href="#" title="portfolio"><span
                            class="dashicons dashicons-portfolio"></span></a></li>
            <li data-icon="book"><a href="#" title="book"><span class="dashicons dashicons-book"></span></a></li>
            <li data-icon="book-alt"><a href="#" title="book-alt"><span class="dashicons dashicons-book-alt"></span></a>
            </li>
            <li data-icon="download"><a href="#" title="download"><span class="dashicons dashicons-download"></span></a>
            </li>
            <li data-icon="upload"><a href="#" title="upload"><span class="dashicons dashicons-upload"></span></a>
            </li>
            <li data-icon="backup"><a href="#" title="backup"><span class="dashicons dashicons-backup"></span></a>
            </li>
            <li data-icon="clock"><a href="#" title="clock"><span class="dashicons dashicons-clock"></span></a></li>
            <li data-icon="lightbulb"><a href="#" title="lightbulb"><span
                            class="dashicons dashicons-lightbulb"></span></a></li>
            <li data-icon="microphone"><a href="#" title="microphone"><span
                            class="dashicons dashicons-microphone"></span></a></li>
            <li data-icon="desktop"><a href="#" title="desktop"><span
                            class="dashicons dashicons-desktop"></span></a></li>
            <li data-icon="tablet"><a href="#" title="tablet"><span class="dashicons dashicons-tablet"></span></a>
            </li>
            <li data-icon="smartphone"><a href="#" title="smartphone"><span
                            class="dashicons dashicons-smartphone"></span></a></li>
            <li data-icon="phone"><a href="#" title="phone"><span class="dashicons dashicons-phone"></span></a></li>
            <li data-icon="smiley"><a href="#" title="smiley"><span class="dashicons dashicons-smiley"></span></a>
            </li>
            <li data-icon="index-card"><a href="#" title="index-card"><span
                            class="dashicons dashicons-index-card"></span></a></li>
            <li data-icon="carrot"><a href="#" title="carrot"><span class="dashicons dashicons-carrot"></span></a>
            </li>
            <li data-icon="building"><a href="#" title="building"><span class="dashicons dashicons-building"></span></a>
            </li>
            <li data-icon="store"><a href="#" title="store"><span class="dashicons dashicons-store"></span></a></li>
            <li data-icon="album"><a href="#" title="album"><span class="dashicons dashicons-album"></span></a></li>
            <li data-icon="palmtree"><a href="#" title="palmtree"><span class="dashicons dashicons-palmtree"></span></a>
            </li>
            <li data-icon="tickets-alt"><a href="#" title="tickets-alt"><span
                            class="dashicons dashicons-tickets-alt"></span></a></li>
            <li data-icon="money"><a href="#" title="money"><span class="dashicons dashicons-money"></span></a></li>
            <li data-icon="thumbs-up"><a href="#" title="thumbs-up"><span
                            class="dashicons dashicons-thumbs-up"></span></a></li>
            <li data-icon="thumbs-down"><a href="#" title="thumbs-down"><span
                            class="dashicons dashicons-thumbs-down"></span></a></li>
            <li data-icon="layout"><a href="#" title="layout"><span class="dashicons dashicons-layout"></span></a>
            </li>
            <li data-icon="align-pull-left"><a href="#" title="align-pull-left"><span
                            class="dashicons dashicons-align-pull-left"></span></a></li>
            <li data-icon="align-pull-right"><a href="#" title="align-pull-right"><span
                            class="dashicons dashicons-align-pull-right"></span></a></li>
            <li data-icon="block-default"><a href="#" title="block-default"><span
                            class="dashicons dashicons-block-default"></span></a></li>
            <li data-icon="cloud-saved"><a href="#" title="cloud-saved"><span
                            class="dashicons dashicons-cloud-saved"></span></a></li>
            <li data-icon="cloud-upload"><a href="#" title="cloud-upload"><span
                            class="dashicons dashicons-cloud-upload"></span></a></li>
            <li data-icon="columns"><a href="#" title="columns"><span
                            class="dashicons dashicons-columns"></span></a></li>
            <li data-icon="cover-image"><a href="#" title="cover-image"><span
                            class="dashicons dashicons-cover-image"></span></a></li>
            <li data-icon="embed-audio"><a href="#" title="embed-audio"><span
                            class="dashicons dashicons-embed-audio"></span></a></li>
            <li data-icon="embed-generic"><a href="#" title="embed-generic"><span
                            class="dashicons dashicons-embed-generic"></span></a></li>
            <li data-icon="embed-photo"><a href="#" title="embed-photo"><span
                            class="dashicons dashicons-embed-photo"></span></a></li>
            <li data-icon="embed-post"><a href="#" title="embed-post"><span
                            class="dashicons dashicons-embed-post"></span></a></li>
            <li data-icon="embed-video"><a href="#" title="embed-video"><span
                            class="dashicons dashicons-embed-video"></span></a></li>
            <li data-icon="exit"><a href="#" title="exit"><span class="dashicons dashicons-exit"></span></a></li>
            <li data-icon="html"><a href="#" title="html"><span class="dashicons dashicons-html"></span></a></li>
            <li data-icon="info-outline"><a href="#" title="info-outline"><span
                            class="dashicons dashicons-info-outline"></span></a></li>
            <li data-icon="insert-after"><a href="#" title="insert-after"><span
                            class="dashicons dashicons-insert-after"></span></a></li>
            <li data-icon="insert-before"><a href="#" title="insert-before"><span
                            class="dashicons dashicons-insert-before"></span></a></li>
            <li data-icon="insert"><a href="#" title="insert"><span class="dashicons dashicons-insert"></span></a>
            </li>
            <li data-icon="remove"><a href="#" title="remove"><span class="dashicons dashicons-remove"></span></a>
            </li>
            <li data-icon="shortcode"><a href="#" title="shortcode"><span
                            class="dashicons dashicons-shortcode"></span></a></li>
            <li data-icon="table-col-after"><a href="#" title="table-col-after"><span
                            class="dashicons dashicons-table-col-after"></span></a></li>
            <li data-icon="table-col-before"><a href="#" title="table-col-before"><span
                            class="dashicons dashicons-table-col-before"></span></a></li>
            <li data-icon="table-col-delete"><a href="#" title="table-col-delete"><span
                            class="dashicons dashicons-table-col-delete"></span></a></li>
            <li data-icon="table-row-after"><a href="#" title="table-row-after"><span
                            class="dashicons dashicons-table-row-after"></span></a></li>
            <li data-icon="table-row-before"><a href="#" title="table-row-before"><span
                            class="dashicons dashicons-table-row-before"></span></a></li>
            <li data-icon="table-row-delete"><a href="#" title="table-row-delete"><span
                            class="dashicons dashicons-table-row-delete"></span></a></li>
            <li data-icon="saved"><a href="#" title="saved"><span class="dashicons dashicons-saved"></span></a></li>
            <li data-icon="amazon"><a href="#" title="amazon"><span class="dashicons dashicons-amazon"></span></a>
            </li>
            <li data-icon="google"><a href="#" title="google"><span class="dashicons dashicons-google"></span></a>
            </li>
            <li data-icon="linkedin"><a href="#" title="linkedin"><span class="dashicons dashicons-linkedin"></span></a>
            </li>
            <li data-icon="pinterest"><a href="#" title="pinterest"><span
                            class="dashicons dashicons-pinterest"></span></a></li>
            <li data-icon="podio"><a href="#" title="podio"><span class="dashicons dashicons-podio"></span></a></li>
            <li data-icon="reddit"><a href="#" title="reddit"><span class="dashicons dashicons-reddit"></span></a>
            </li>
            <li data-icon="spotify"><a href="#" title="spotify"><span
                            class="dashicons dashicons-spotify"></span></a></li>
            <li data-icon="twitch"><a href="#" title="twitch"><span class="dashicons dashicons-twitch"></span></a>
            </li>
            <li data-icon="whatsapp"><a href="#" title="whatsapp"><span class="dashicons dashicons-whatsapp"></span></a>
            </li>
            <li data-icon="xing"><a href="#" title="xing"><span class="dashicons dashicons-xing"></span></a></li>
            <li data-icon="youtube"><a href="#" title="youtube"><span
                            class="dashicons dashicons-youtube"></span></a></li>
            <li data-icon="database-add"><a href="#" title="database-add"><span
                            class="dashicons dashicons-database-add"></span></a></li>
            <li data-icon="database-export"><a href="#" title="database-export"><span
                            class="dashicons dashicons-database-export"></span></a></li>
            <li data-icon="database-import"><a href="#" title="database-import"><span
                            class="dashicons dashicons-database-import"></span></a></li>
            <li data-icon="database-remove"><a href="#" title="database-remove"><span
                            class="dashicons dashicons-database-remove"></span></a></li>
            <li data-icon="database-view"><a href="#" title="database-view"><span
                            class="dashicons dashicons-database-view"></span></a></li>
            <li data-icon="database"><a href="#" title="database"><span class="dashicons dashicons-database"></span></a>
            </li>
            <li data-icon="bell"><a href="#" title="bell"><span class="dashicons dashicons-bell"></span></a></li>
            <li data-icon="airplane"><a href="#" title="airplane"><span class="dashicons dashicons-airplane"></span></a>
            </li>
            <li data-icon="car"><a href="#" title="car"><span class="dashicons dashicons-car"></span></a></li>
            <li data-icon="calculator"><a href="#" title="calculator"><span
                            class="dashicons dashicons-calculator"></span></a></li>
            <li data-icon="ames"><a href="#" title="ames"><span class="dashicons dashicons-ames"></span></a></li>
            <li data-icon="printer"><a href="#" title="printer"><span
                            class="dashicons dashicons-printer"></span></a></li>
            <li data-icon="beer"><a href="#" title="beer"><span class="dashicons dashicons-beer"></span></a></li>
            <li data-icon="coffee"><a href="#" title="coffee"><span class="dashicons dashicons-coffee"></span></a>
            </li>
            <li data-icon="drumstick"><a href="#" title="drumstick"><span
                            class="dashicons dashicons-drumstick"></span></a></li>
            <li data-icon="food"><a href="#" title="food"><span class="dashicons dashicons-food"></span></a></li>
            <li data-icon="bank"><a href="#" title="bank"><span class="dashicons dashicons-bank"></span></a></li>
            <li data-icon="hourglass"><a href="#" title="hourglass"><span
                            class="dashicons dashicons-hourglass"></span></a></li>
            <li data-icon="money-alt"><a href="#" title="money-alt"><span
                            class="dashicons dashicons-money-alt"></span></a></li>
            <li data-icon="open-folder"><a href="#" title="open-folder"><span
                            class="dashicons dashicons-open-folder"></span></a></li>
            <li data-icon="pdf"><a href="#" title="pdf"><span class="dashicons dashicons-pdf"></span></a></li>
            <li data-icon="pets"><a href="#" title="pets"><span class="dashicons dashicons-pets"></span></a></li>
            <li data-icon="privacy"><a href="#" title="privacy"><span
                            class="dashicons dashicons-privacy"></span></a></li>
            <li data-icon="superhero"><a href="#" title="superhero"><span
                            class="dashicons dashicons-superhero"></span></a></li>
            <li data-icon="superhero-alt"><a href="#" title="superhero-alt"><span
                            class="dashicons dashicons-superhero-alt"></span></a></li>
            <li data-icon="edit-page"><a href="#" title="edit-page"><span
                            class="dashicons dashicons-edit-page"></span></a></li>
            <li data-icon="fullscreen-alt"><a href="#" title="fullscreen-alt"><span
                            class="dashicons dashicons-fullscreen-alt"></span></a></li>
            <li data-icon="fullscreen-exit-alt"><a href="#" title="fullscreen-exit-alt"><span
                            class="dashicons dashicons-fullscreen-exit-alt"></span></a></li>
        </ul>
        <button type="button" id="close-dashicon-picker" class="button button-secondary">Close</button>
    </div>
</div>