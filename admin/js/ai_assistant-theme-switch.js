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

