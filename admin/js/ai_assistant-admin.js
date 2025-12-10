(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */


})(jQuery);


jQuery(document).ready(function ($) {

    $(".ai_assistant-settings-page .nav-tab").on("click", function (e) {
        e.preventDefault();

        // Remove active class from all tabs and tab contents
        $(".ai_assistant-settings-page .nav-tab").removeClass("nav-tab-active");
        $(".ai_assistant-settings-page .tab-content").removeClass("active");

        // Add active class to the clicked tab and its associated content
        $(this).addClass("nav-tab-active");
        $($(this).attr("href")).addClass("active");
    });

    // ✅ Toggle folders based on 'open' class
    $(document).on('click', '.folder', function (e) {
        e.stopPropagation();
        const nested = $(this).children('.nested');
        if ($(this).hasClass('open')) {
            // 🔒 If open, slide up and remove 'open' class
            nested.slideUp('fast');
            $(this).removeClass('open');
        } else {
            // 🔓 If not open, slide down and add 'open' class
            nested.slideDown('fast');
            $(this).addClass('open');
        }
    });

    // ✅ On page load, expand active file's parents
    const activeFile = $('.active-file');
    activeFile.parents('.nested').show();
    activeFile.parents('.folder').addClass('open');


    $('.postbox-header').on('click', function () {
        const $postbox = $(this).closest('.postbox');

        if ($postbox.hasClass('closed')) {
            // If 'closed' class exists, remove it and slide down
            $postbox.removeClass('closed');
            $postbox.find('.inside').slideDown('fast');
            $(this).find('.dashicons')
                .removeClass('dashicons-arrow-down')
                .addClass('dashicons-arrow-up');
        } else {
            // If 'closed' class does not exist, add it and slide up
            $postbox.addClass('closed');
            $postbox.find('.inside').slideUp('fast');
            $(this).find('.dashicons')
                .removeClass('dashicons-arrow-up')
                .addClass('dashicons-arrow-down');
        }
    });

    // ✅ Open Modal
    $('.open-dashicon-picker').on('click', function () {
        $('#dashicon-picker-modal').fadeIn('fast');
    });

    // ✅ Close Modal
    $('#close-dashicon-picker, #dashicon-picker-overlay').on('click', function () {
        $('#dashicon-picker-modal').fadeOut('fast');
    });

    // ✅ Handle Icon Selection
    $(document).on('click', '.dashicon-picker-list li', function () {
        const selectedIcon = $(this).data('icon');
        $('.dashi_icon_field').val(`dashicons-${selectedIcon}`); // Paste icon slug into text field
        $('#dashicon-picker-modal').fadeOut('fast');
    });

    // delete files and folders from theme editor
    $(document).on('click', '.delete-item', function (e) {
        e.preventDefault();
        const filePath = $(this).data('path');
        const confirmDelete = confirm(`Are you sure you want to delete "${filePath}"?`);

        if (confirmDelete) {
            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: {
                    action: "ai_assistant_delete_file",
                    file_path: filePath
                },
                success: function (response) {
                    if (response.success) {
                        showAlert(`✅ ${response.data}`, "success");
                        location.reload(); // Refresh after deletion
                    } else {
                        showAlert(`❌ ${response.data}`, "danger");
                    }
                },
                error: function () {
                    showAlert("❌ Error occurred while deleting the file/folder.", "danger");
                }
            });
        }
    });


});


jQuery(document).ready(function ($) {
    let originalContent = $("#theme-file-editor").val().trim();

    // Detect changes in the textarea
    $("#theme-file-editor").on("input", function () {
        let currentContent = $(this).val().trim();
        if (currentContent !== originalContent) {
            $("#file_save").removeClass("button-disabled");
        } else {
            $("#file_save").addClass("button-disabled");
        }
    });

    $("#file_save").on("click", function () {
        var filePath = new URLSearchParams(window.location.search).get("file");

        // ✅ Sync CodeMirror content to the textarea before saving
        var fileEditor = $("#theme-file-editor");
        window.aiAssistantEditor.save();
        var fileContent = fileEditor.val().trim();

        var _this = $(this);

        if (!filePath) {
            showAlert("❌ No file selected!", "danger");
            return;
        }

        _this.text("Saving...");

        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                action: "ai_assistant_save_file",
                file_path: filePath,
                file_content: fileContent
            },
            success: function (response) {
                if (response.success) {
                    showAlert("✅ File saved successfully!", "success");
                    _this.text("Saved");
                } else {
                    showAlert("❌ Failed to save file: " + response.data, "danger");
                    _this.text("Retry");
                }
            },
            error: function () {
                _this.text("Retry");
                showAlert("❌ AJAX error occurred while saving the file.", "danger");
            }
        });
    });


    //tab view of custom field in theme editor
    $(".custom-field-box .custom-field-tabs li").on("click", function () {
        let parentBox = $(this).closest(".custom-field-box");

        // Remove active class from tabs
        parentBox.find(".custom-field-tabs li").removeClass("active");
        $(this).addClass("active");

        // Show the relevant tab content
        let tabId = $(this).attr("data-tab");
        parentBox.find(".tab-pane").removeClass("active");
        parentBox.find("#" + tabId).addClass("active");
    });


    //tab view for repeate in acf
    $(".acf-tab").on("click", function () {
        var tabId = $(this).data("tab");

        // Remove active class from all tabs and add to the clicked one
        $(".acf-tab").removeClass("active");
        $(this).addClass("active");

        // Hide all tab content and show the selected one
        $(".acf-tab-content").hide();
        $("#" + tabId).show();
    });

    // ✅ Open the first tab by default
    $(".acf-tab:first").addClass("active");
    $(".acf-tab-content:first").show();


    $(document).on("keydown", function (event) {
        if (event.ctrlKey && event.key === "s") {
            event.preventDefault(); // Prevent browser's default save dialog

            // ✅ Check if #file_save exists in the DOM
            if ($("#file_save").length) {
                $("#file_save").trigger("click"); // Simulate click
            } else {
                console.warn("❌ Save button (#file_save) not found in DOM!");
            }
        }
    });


    $('#apply-field .accordion-header').on('click', function () {
        $(this).next('.accordion-content').slideToggle();
    });

    // ✅ Handle repeater subfields toggle
    $('#apply-field .repeater-field legend').on('click', function () {
        $(this).siblings('.repeater-subfields').slideToggle();
    });

    // ✅ Handle field button clicks
    $('#apply-field .field-button').on('click', function () {
        var fieldSlug = $(this).data('name');
        var fieldType = $(this).data('type');
        var parentRepeater = $(this).data('parent');

        // ✅ Ensure `window.selectedText` is properly wrapped
        var selectedContent = window.selectedText ? window.selectedText.trim() : "<!-- Add content here -->";

        // ✅ Determine PHP code format
        var phpFieldCode = "";

        if (fieldType === 'repeater') {
            phpFieldCode = `<?php if (have_rows('${fieldSlug}')): $i = 0; ?>\n` +
                `    <?php while (have_rows('${fieldSlug}')) : the_row(); ?>\n` +
                `        ${selectedContent}\n` +  // ✅ Wraps selected text inside the repeater loop
                `    <?php $i++; endwhile; ?>\n` +
                `<?php endif; ?>`;
        } else if (fieldType === 'subfield' && parentRepeater) {
            phpFieldCode = `<?php the_sub_field('${fieldSlug}'); ?>`;
        } else {
            phpFieldCode = `<?php the_field('${fieldSlug}'); ?>`;
        }

        if (typeof replaceSelectedTextInEditor === "function") {
            replaceSelectedTextInEditor(phpFieldCode, "Code copied!! Press Ctrl + V to paste.");
        }
    });

    // to paste the shortcode of the contact form
    $(".cf7-shortcode-btn").on("click", function () {
        let shortcode = $(this).data("shortcode");
        let phpFieldCode = `<?php echo do_shortcode('${shortcode}'); ?>`;

        if (typeof replaceSelectedTextInEditor === "function") {
            replaceSelectedTextInEditor(phpFieldCode, "Shortcode copied!! Press Ctrl+v to paste.");
        }
    });


    let customizerTextarea = $("#field__customizer");

    // ✅ Auto-Insert Section on First Click
    $(".customizer__btn[data-shortcode='create__section']").on("click", function () {
        let textareaContent = customizerTextarea.val().trim();

        // ✅ Check if `[section` already exists
        if (textareaContent.includes("[section")) {
            showAlert("⚠ A section already exists!", "danger");
            return;
        }

        // ✅ Insert `[section]` at the beginning of the textarea
        let sectionContent = `[section name=""]\n[/section]\n`;
        customizerTextarea.val(sectionContent);

        // ✅ Move cursor inside `name=""`
        let cursorPosition = sectionContent.indexOf(`name=""`) + 6;
        customizerTextarea[0].setSelectionRange(cursorPosition, cursorPosition);
        customizerTextarea.focus();
    });

    // ✅ Insert Fields Inside Section
    $(".customizer__btn").not("[data-shortcode='create__section']").on("click", function () {
        let shortcodeType = $(this).attr("data-shortcode");
        let currentContent = customizerTextarea.val().trim();

        // ✅ Auto-Paste Social Section
        if (shortcodeType === "social") {
            let socialContent = `[section name="Social"]
  [url name="Facebook Url"]
  [url name="Instagram Url"]
  [url name="Linkedin Url"]
  [url name="Youtube"]
[/section]`;

            customizerTextarea.val(socialContent);
            showAlert("✅ Social section added!", "success");
            return;
        }

        // ✅ Auto-Paste Contact Section
        if (shortcodeType === "contact") {
            let contactContent = `[section name="Contact Information"]
  [text name="Telephone"]
  [text name="Telephone2"]
  [textarea name="Address"]
  [text name="Email"]
[/section]`;

            customizerTextarea.val(contactContent);
            showAlert("✅ Contact Information section added!", "success");
            return;
        }

        // ✅ Ensure section exists before inserting other fields
        let sectionStart = currentContent.indexOf("[section");
        let sectionEnd = currentContent.indexOf("[/section]");

        if (sectionStart === -1 || sectionEnd === -1) {
            showAlert("⚠ You must create a section first!", "danger");
            return;
        }

        // ✅ Generate Field Shortcode
        let shortcode = `[${shortcodeType} name=""]`;

        // ✅ Insert Field Inside `[section] ... [/section]`
        let newContent =
            currentContent.slice(0, sectionEnd) +
            `  ${shortcode}\n` +
            currentContent.slice(sectionEnd);

        customizerTextarea.val(newContent);

        // ✅ Move cursor inside `name=""`
        let cursorPosition = newContent.indexOf(`name=""`) + 6;
        customizerTextarea[0].setSelectionRange(cursorPosition, cursorPosition);
        customizerTextarea.focus();
    });


    // ✅ Append to `functions.php` on button click
    $("#add__field_to_textarea").on("click", function () {
        let customizerCode = customizerTextarea.val().trim();
        if (!customizerCode) {
            alert("⚠ No content to save!");
            return;
        }

        $.ajax({
            url: ajax_object.ajax_url, // WordPress AJAX URL
            type: "POST",
            data: {
                action: "save_customizer_code",
                customizer_code: customizerCode
            },
            success: function (response) {
                alert("✅ Customizer code added to functions.php!");
            },
            error: function () {
                alert("❌ Error saving to functions.php!");
            }
        });
    });

    $(".customizer-tabs li").on("click", function () {
        let tab = $(this).data("tab");

        // ✅ Switch active tab
        $(".customizer-tabs li").removeClass("active");
        $(this).addClass("active");

        // ✅ Show/Hide content based on tab selection
        $("[data-tab-content]").removeClass("active");
        $(`[data-tab-content="${tab}"]`).addClass("active");
    });




    $(".accordion-toggle").on("click", function () {
        let parentItem = $(this).closest(".accordion-item");
        let accordionBody = parentItem.find(".accordion-body");

        // ✅ Close all other sections
        $(".accordion-item").not(parentItem).removeClass("active").find(".accordion-body").slideUp();

        // ✅ Toggle active class & slide body
        if (accordionBody.is(":visible")) {
            accordionBody.slideUp();
            parentItem.removeClass("active");
        } else {
            accordionBody.slideDown();
            parentItem.addClass("active");
        }
    });

    // ✅ Copy setting on button click
    $(document).on("click", ".copy-btn", function () {
        let settingSlug = $(this).data("slug");
        let phpFieldCode = `<?php echo get_theme_mod('${settingSlug}'); ?>`;

        if (typeof replaceSelectedTextInEditor === "function") {
            replaceSelectedTextInEditor(phpFieldCode, "Code copied!! Press Ctrl+v to paste.");
        }


    });


    /** pages popu **/




});

jQuery(document).ready(function($) {
    let searchTimeout;

    // Load pages on page load
    loadPages();

    // Search functionality
    $('#page-search').on('keyup', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();

        searchTimeout = setTimeout(function() {
            loadPages(searchTerm);
        }, 300); // Delay for better performance
    });

    // Function to load pages via AJAX
    function loadPages(search = '') {
        const $pagesList = $('#pages-list');

        // Show loading state
        $pagesList.html('<div class="loading">Loading pages...</div>');

        $.ajax({
            url: pagesListAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_pages_list',
                nonce: pagesListAjax.nonce,
                search: search
            },
            success: function(response) {
                if (response.success) {
                    $pagesList.html(response.data.html);

                    // Update count if you want to show it
                    if (response.data.count !== undefined) {
                        console.log('Pages found: ' + response.data.count);
                    }
                } else {
                    $pagesList.html('<div class="error">Error loading pages</div>');
                }
            },
            error: function() {
                $pagesList.html('<div class="error">Failed to load pages. Please try again.</div>');
            }
        });
    }

    // Optional: Refresh pages button (if you add one)
    $(document).on('click', '#refresh-pages', function() {
        $('#page-search').val('');
        loadPages();
    });



    /**
     * Small helper: use showAlert if available, else fallback to alert()
     */
    function aiNotify(message, type) {
        if (typeof showAlert === 'function') {
            showAlert(message, type || 'success');
        } else {
            alert(message);
        }
    }


    // 📝 Create new file (full path from theme root)
    $(document).on('click', '#ai-create-file', function (e) {
        e.preventDefault();

        const filePath = prompt(
            'Enter FULL file path relative to the theme root.\n' +
            'Examples:\n' +
            '  templates/home.php\n' +
            '  partials/blocks/section.php'
        );

        if (!filePath) {
            return;
        }

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'ai_assistant_create_file',
                file_path: filePath
            },
            success: function (response) {
                if (response.success) {
                    aiNotify(response.data, 'success');
                    location.reload();
                } else {
                    aiNotify(response.data || '❌ Failed to create file.', 'danger');
                }
            },
            error: function () {
                aiNotify('❌ Error while creating file.', 'danger');
            }
        });
    });

    // 📁 Create new folder (full path from theme root)
    $(document).on('click', '#ai-create-folder', function (e) {
        e.preventDefault();

        const folderPath = prompt(
            'Enter FULL folder path relative to the theme root.\n' +
            'Examples:\n' +
            '  templates\n' +
            '  partials/blocks\n' +
            '  assets/css'
        );

        if (!folderPath) {
            return;
        }

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'ai_assistant_create_folder',
                folder_path: folderPath
            },
            success: function (response) {
                if (response.success) {
                    aiNotify(response.data, 'success');
                    location.reload();
                } else {
                    aiNotify(response.data || '❌ Failed to create folder.', 'danger');
                }
            },
            error: function () {
                aiNotify('❌ Error while creating folder.', 'danger');
            }
        });
    });

});


jQuery(document).ready(function ($) {

    // Copy JSON from current post/page
    $('#ai-wpml-copy-json').on('click', function (e) {
        e.preventDefault();

        if (typeof aiAssistantAdmin === 'undefined' || !aiAssistantAdmin.current_post_id) {
            alert('No post ID found. Please use this on a post/page edit screen.');
            return;
        }

        $.post(aiAssistantAdmin.ajax_url, {
            action: 'ai_assistant_get_meta_json',
            nonce: aiAssistantAdmin.nonce,
            post_id: aiAssistantAdmin.current_post_id
        }).done(function (response) {
            if (response.success && response.data && response.data.meta) {
                $('#wpml_json_original').val(
                    JSON.stringify(response.data.meta, null, 2)
                );
            } else {
                alert((response.data && response.data.message) || 'Error fetching custom fields.');
            }
        }).fail(function () {
            alert('AJAX error fetching custom fields.');
        });
    });

    // Update JSON back to custom fields
    $('#ai-wpml-update-json').on('click', function (e) {
        e.preventDefault();

        if (typeof aiAssistantAdmin === 'undefined' || !aiAssistantAdmin.current_post_id) {
            alert('No post ID found. Please use this on a post/page edit screen.');
            return;
        }

        var raw = $('#wpml_json_translated').val().trim();
        if (!raw) {
            alert('Please paste translated JSON in the second textarea.');
            return;
        }

        let parsed;
        try {
            parsed = JSON.parse(raw);
        } catch (err) {
            alert('Invalid JSON. Please check the format.');
            return;
        }

        $.post(aiAssistantAdmin.ajax_url, {
            action: 'ai_assistant_update_meta_from_json',
            nonce: aiAssistantAdmin.nonce,
            post_id: aiAssistantAdmin.current_post_id,
            meta_json: JSON.stringify(parsed)
        }).done(function (response) {
            if (response.success) {
                alert(response.data.message || 'Custom fields updated successfully.');
            } else {
                alert((response.data && response.data.message) || 'Error updating custom fields.');
            }
        }).fail(function () {
            alert('AJAX error updating custom fields.');
        });
    });





    // Helper: copy text to clipboard
    function aiAssistantCopyToClipboard(text) {
        if (!text) {
            alert('Nothing to copy.');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                // optional: console.log('Copied');
            }).catch(function () {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }

        function fallbackCopy(t) {
            var temp = document.createElement('textarea');
            temp.style.position = 'fixed';
            temp.style.left = '-9999px';
            temp.value = t;
            document.body.appendChild(temp);
            temp.select();
            try {
                document.execCommand('copy');
            } catch (e) {}
            document.body.removeChild(temp);
        }
    }

    // Copy JSON only
    $('#ai-wpml-copy-json-only').on('click', function (e) {
        e.preventDefault();
        var json = $('#wpml_json_original').val().trim();
        if (!json) {
            alert('Original JSON is empty. Click "Copy JSON from this page" first.');
            return;
        }
        aiAssistantCopyToClipboard(json);
        alert('JSON copied to clipboard.');
    });

    // Copy JSON with AI prompt
    $('#ai-wpml-copy-json-with-prompt').on('click', function (e) {
        e.preventDefault();
        var json = $('#wpml_json_original').val().trim();
        if (!json) {
            alert('Original JSON is empty. Click "Copy JSON from this page" first.');
            return;
        }

        var langLabel = $('#wpml-language option:selected').text().trim();
        if (!langLabel) {
            langLabel = 'this language';
        }

        var prompt =
            'Translate this JSON to ' + langLabel +
            '. Keep the same keys. Only translate the text values. ' +
            'Return only JSON, easy to copy:\n\n' + json;

        aiAssistantCopyToClipboard(prompt);
        alert('Prompt + JSON copied to clipboard. Paste it into ChatGPT.');
    });



});












