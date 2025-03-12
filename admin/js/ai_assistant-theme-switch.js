jQuery(document).ready(function ($) {
// 🔹 Define global variables for selection tracking
    window.selectionStart = 0;
    window.selectionEnd = 0;
    window.selectedText = "";

    window.aiAssistantInitEditor = function () {
        if (typeof wp === "undefined" || typeof wp.CodeMirror === "undefined") {
            console.error("❌ CodeMirror not loaded!");
            return;
        }

        let savedTheme = ajax_object.saved_theme || "default"; // ✅ Load saved theme from wp_options

        var urlParams = new URLSearchParams(window.location.search);
        var filePath = urlParams.get("file"); // Get 'file' parameter from URL
        var fileExtension = filePath ? filePath.split('.').pop().toLowerCase() : "";

        // ✅ Determine CodeMirror mode based on file extension
        var mode = "text/plain"; // Default mode
        if (fileExtension === "js") mode = "javascript";
        else if (fileExtension === "css") mode = "css";
        else if (fileExtension === "php") mode = "php";
        else if (fileExtension === "html" || fileExtension === "htm") mode = "htmlmixed";

        var editor = wp.CodeMirror.fromTextArea(document.getElementById("theme-file-editor"), {
            mode: mode, // ✅ Dynamic mode selection
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 4,
            tabSize: 4,
            theme: savedTheme, // ✅ Apply saved theme
            matchBrackets: true,
            matchTags: { bothTags: true },
            autoCloseBrackets: true,
            autoCloseTags: mode === "htmlmixed", // ✅ Enable only for HTML
            styleActiveLine: true,
            extraKeys: { "Ctrl-Space": "autocomplete" },


        });

// ✅ Force a refresh to fix possible rendering issues
        setTimeout(() => {
            editor.refresh();
        }, 500);


        // ✅ Set custom height
        editor.setSize("100%", "1000px");

        // ✅ Remove 'button-disabled' when content changes
        editor.on("change", function () {
            $("#file_save").removeClass("button-disabled");
            $("#file_save").text("Save");
        });

        // ✅ Track selection globally (Fix missing last character issue)
        editor.on("beforeSelectionChange", function (instance, obj) {
            let selections = obj.ranges;
            if (selections.length > 0) {
                window.selectionStart = selections[0].anchor.ch;
                window.selectionEnd = selections[0].head.ch;

                // ✅ Delay retrieving selected text to ensure full selection
                setTimeout(() => {
                    window.selectedText = editor.getSelection();
                }, 10);
            }
        });

        // ✅ Function to insert text at the cursor position
        window.replaceSelectedTextInsideEditor = function (newText, message) {
            if (!editor) {
                console.warn("🚨 CodeMirror editor not detected!");
                return;
            }

            if (!window.selectedText) {
                let cursor = editor.getCursor();
                editor.replaceRange(newText, cursor);
                editor.setCursor({ line: cursor.line, ch: cursor.ch + newText.length });
                showAlert(message || "Code inserted at cursor position!", "success");
            } else {
                editor.replaceSelection(newText);
                editor.focus();
            }
        };

        // ✅ Function to replace selected text
        window.replaceSelectedTextInEditor = function (newText, message) {
            if (!window.selectedText) {
                let tempInput = $("<input>");
                $("body").append(tempInput);
                tempInput.val(newText).select();
                document.execCommand("copy");
                tempInput.remove();
                showAlert(message, "success");
            } else {
                editor.replaceSelection(newText);
                editor.focus();
            }
        };

        // ✅ Set the checkbox state based on the saved theme
        $("#theme-toggle-checkbox").prop("checked", savedTheme === "dracula");

        // ✅ Theme Toggle Switch Logic
        $("#theme-toggle-checkbox").on("change", function () {
            let newTheme = $(this).is(":checked") ? "dracula" : "default";
            editor.setOption("theme", newTheme);
            if (newTheme === "dracula") {
                $("body").addClass("ai-dark-theme");
            } else {
                $("body").removeClass("ai-dark-theme");
            }

            // ✅ Save theme preference via AJAX
            $.post(ajax_object.ajax_url, {
                action: "save_codemirror_theme",
                theme: newTheme
            }, function (response) {
                if (!response.success) {
                    console.log("Error saving theme");
                }
            });
        });

        // ✅ Store editor globally
        window.aiAssistantEditor = editor;
    };
});