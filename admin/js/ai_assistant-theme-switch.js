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

        const textarea = document.getElementById("theme-file-editor");

        // ✅ Exit safely if not on the theme/plugin editor page
        if (!textarea) {
            // Not an error — just not the right page
            return;
        }

        // ✅ Prevent double init if function runs multiple times
        if (window.aiAssistantEditor) {
            return;
        }

        let savedTheme = (window.ajax_object && ajax_object.saved_theme) ? ajax_object.saved_theme : "default";

        const urlParams = new URLSearchParams(window.location.search);
        const filePath = urlParams.get("file") || "";
        const fileExtension = filePath.split(".").pop().toLowerCase();

        let mode = "text/plain";
        if (fileExtension === "js") mode = "javascript";
        else if (fileExtension === "css") mode = "css";
        else if (fileExtension === "php") mode = "application/x-httpd-php"; // ✅ correct CodeMirror php mode
        else if (fileExtension === "html" || fileExtension === "htm") mode = "htmlmixed";

        const editor = wp.CodeMirror.fromTextArea(textarea, {
            mode,
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 4,
            tabSize: 4,
            theme: savedTheme,
            matchBrackets: true,
            matchTags: { bothTags: true },
            autoCloseBrackets: true,
            autoCloseTags: mode === "htmlmixed",
            styleActiveLine: true,
            foldGutter: true,
            gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
            extraKeys: {
                "Ctrl-Space": "autocomplete",
                "Ctrl-/": "toggleComment",
                "Shift-Ctrl-/": "toggleBlockComment",
                "Ctrl-Q": function (cm) { cm.foldCode(cm.getCursor()); }
            }
        });

        setTimeout(() => editor.refresh(), 500);
        editor.setSize("100%", "1000px");

        editor.on("change", function () {
            $("#file_save").removeClass("button-disabled").text("Save");
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
                editor.setCursor({line: cursor.line, ch: cursor.ch + newText.length});
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

        $("#go-to-line-btn").on("click", function () {
            var lineNumber = parseInt($("#line-number-input").val(), 10);

            if (!isNaN(lineNumber) && lineNumber > 0) {
                editor.setCursor({line: lineNumber - 1, ch: 0}); // ✅ Move cursor (0-based index)
                editor.focus(); // ✅ Focus the editor
            } else {
                alert("❌ Enter a valid line number!");
            }
        });

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
        window.aiAssistantEditor = editor;
    };


    function beautifyCodeMirror(editor) {
        let mode = editor.getOption("mode");
        let code = editor.getValue();
        let formattedCode = code; // Default is original code

        if (mode === "htmlmixed" || mode === "xml") {
            formattedCode = html_beautify(code, {indent_size: 4});
        } else if (mode === "javascript") {
            formattedCode = js_beautify(code, {indent_size: 4});
        } else if (mode === "css") {
            formattedCode = css_beautify(code, {indent_size: 4});
        } else if (mode === "application/x-httpd-php") {
            formattedCode = php_beautify(code); // Using `php-beautifier`
        }

        editor.setValue(formattedCode);
    }
});

