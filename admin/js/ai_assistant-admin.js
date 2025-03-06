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


    // $(document).ready(function () {
    //     window.aiAssistantInitEditor = function () {
    //         if (typeof wp === 'undefined' || typeof wp.CodeMirror === 'undefined') {
    //             console.error("❌ CodeMirror not loaded!");
    //             return;
    //         }
    //
    //         let textarea = document.getElementById("theme-file-editor");
    //
    //         // ✅ Check if CodeMirror is already initialized
    //         if (textarea.classList.contains("codemirror-initialized")) {
    //             console.warn("⚠️ CodeMirror already initialized, skipping...");
    //             return;
    //         }
    //
    //         // ✅ Initialize CodeMirror
    //         let editor = wp.CodeMirror.fromTextArea(textarea, {
    //             mode: "php",
    //             lineNumbers: true,
    //             lineWrapping: true,
    //             indentUnit: 4,
    //             tabSize: 4,
    //             theme: "default",
    //             matchBrackets: true,
    //             autoCloseBrackets: true,
    //             styleActiveLine: true
    //         });
    //
    //         // ✅ Set custom height
    //         editor.setSize("100%", "1000px"); // Adjust height here
    //
    //         // Track selection for replacing text
    //             let selectionStart = 0;
    //             let selectionEnd = 0;
    //             let selectedText = "";
    //         editor.on("beforeSelectionChange", function (instance, obj) {
    //             let selections = obj.ranges;
    //             if (selections.length > 0) {
    //                 selectionStart = selections[0].anchor.ch;
    //                 selectionEnd = selections[0].head.ch;
    //                 selectedText = editor.getSelection();
    //             }
    //         });
    //
    //         // ✅ Mark as initialized to prevent duplicate instances
    //         textarea.classList.add("codemirror-initialized");
    //
    //         // Store editor globally
    //         window.aiAssistantEditor = editor;
    //     };
    //
    //     // ✅ Call the function after DOM is ready
    //     if (document.getElementById("theme-file-editor")) {
    //         aiAssistantInitEditor();
    //     }
    // });


})(jQuery);
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.ai_assistant-settings-page .nav-tab');
    const tabContents = document.querySelectorAll('.ai_assistant-settings-page .tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();

            // Remove active class from all tabs and tab contents
            tabs.forEach(t => t.classList.remove('nav-tab-active'));
            tabContents.forEach(tc => tc.classList.remove('active'));

            // Add active class to the clicked tab and its content
            this.classList.add('nav-tab-active');
            const target = document.querySelector(this.getAttribute('href'));
            target.classList.add('active');
        });
    });
});


jQuery(document).ready(function ($) {
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

});

jQuery(document).ready(function ($) {
    // ✅ Open Modal
    $('#open-dashicon-picker').on('click', function () {
        $('#dashicon-picker-modal').fadeIn('fast');
    });

    // ✅ Close Modal
    $('#close-dashicon-picker, #dashicon-picker-overlay').on('click', function () {
        $('#dashicon-picker-modal').fadeOut('fast');
    });

    // ✅ Handle Icon Selection
    $(document).on('click', '.dashicon-picker-list li', function () {
        const selectedIcon = $(this).data('icon');
        $('#dashi_icon_field').val(`dashicons-${selectedIcon}`); // Paste icon slug into text field
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


    if (window.location.href.includes("admin.php?page=ai_assistant-theme-editor&file=header.php&needcorrection=true")) {

        // ✅ Show message
        showAlert("✅ You can copy-paste the header in the editor then click on Save this now", "success");

        // ✅ Remove 'removed' class from #ai__tasks
        $("#ai__task").removeClass("closed");

        // ✅ Add 'active' class to .correct__header .open__child
        $(".correct__header").find(".open__child").addClass("active");
        $(".correct__header").addClass("alert__active");

        var actionSetting = $(".correct__header").find(".action__setting");

        if (actionSetting.is(":visible")) {
            actionSetting.slideUp();
        } else {
            actionSetting.slideDown();
        }


        // ✅ Bounce the button
        let button = $(".change_setting");
        button.addClass("bounce");

        // ✅ Remove bounce effect after 1.5 seconds
        setTimeout(() => {
            $(".change_setting").removeClass("bounce");
            $(".correct__header").removeClass("alert__active");
        }, 7000);
    }

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
            replaceSelectedTextInEditor(phpFieldCode);
        } else {
            // ✅ Prompt Copy Instead
            prompt("Press CTRL + C to copy the PHP code:", phpFieldCode);
        }
    });



});











