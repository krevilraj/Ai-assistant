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
            matchTags: {bothTags: true},
            autoCloseBrackets: true,
            autoCloseTags: mode === "htmlmixed", // ✅ Enable only for HTML
            styleActiveLine: true,
            extraKeys: {
                "Ctrl-Space": "autocomplete",
                "Ctrl-/": "toggleComment",
                "Shift-Ctrl-/": "toggleBlockComment",
                "Tab": function (cm) {
                    expandAbbreviation(cm);
                }
            }


        });

        /**
         * ✅ Expands Emmet-style Abbreviations in CodeMirror
         * Supports:
         * - `div` → `<div></div>`
         * - `div.class` → `<div class="class"></div>`
         * - `span#id` → `<span id="id"></span>`
         * - `a` → `<a href=""></a>`
         * - `a.class` → `<a href="" class="class"></a>`
         * - `a#id` → `<a href="" id="id"></a>`
         * - `div>button` → `<div><button></button></div>`
         * - `ul>li*3` → `<ul><li></li><li></li><li></li></ul>`
         */


        /**
         * ✅ Expands Emmet-style Abbreviations in CodeMirror
         * Now Works Even If There's Other Text or HTML on the Line!
         */
        function expandAbbreviation(cm) {
            console.log('Expanding Emmet Abbreviation...');
            var cursor = cm.getCursor();
            var line = cm.getLine(cursor.line);

            // ✅ Find the LAST Emmet-like abbreviation in the line
            var match = line.match(/([\w.#>*\d-]+)$/);
            if (!match) {
                console.log('❌ No valid Emmet match found, default Tab behavior.');
                cm.execCommand("defaultTab");
                return;
            }

            var abbreviation = match[1];

            // ✅ Generate the expanded HTML
            var expanded = parseEmmet(abbreviation);

            if (!expanded) {
                console.log('❌ Not a valid HTML tag, skipping expansion.');
                cm.execCommand("defaultTab");
                return;
            }

            // ✅ Replace only the detected abbreviation (not the whole line!)
            var startPos = { line: cursor.line, ch: match.index };
            var endPos = { line: cursor.line, ch: cursor.ch };

            cm.replaceRange(expanded, startPos, endPos);
            cm.setCursor(cursor.line, startPos.ch + expanded.length - (`</div>`.length));

            console.log(`✅ Expansion Success: ${expanded}`);
        }

        /**
         * ✅ Parses Emmet-style syntax and generates corresponding HTML
         */
        function parseEmmet(abbreviation) {
            const validTags = new Set([
                "a", "abbr", "address", "article", "aside", "audio", "b", "blockquote", "button", "canvas",
                "caption", "cite", "code", "col", "colgroup", "data", "datalist", "dd", "del", "details",
                "dfn", "dialog", "div", "dl", "dt", "em", "fieldset", "figcaption", "figure", "footer",
                "form", "h1", "h2", "h3", "h4", "h5", "h6", "header", "hr", "i", "iframe", "img", "input",
                "ins", "kbd", "label", "legend", "li", "main", "mark", "menu", "meter", "nav", "object",
                "ol", "optgroup", "option", "output", "p", "picture", "pre", "progress", "q", "s",
                "section", "select", "small", "source", "span", "strong", "sub", "summary", "sup", "svg",
                "table", "tbody", "td", "textarea", "tfoot", "th", "thead", "time", "tr", "track", "u",
                "ul", "var", "video", "wbr"
            ]);

            const parts = abbreviation.split(">");
            let html = "";
            let indentLevel = 0;
            let indent = "  ";
            let openTags = []; // Stack to track open tags

            function createElement(tag) {
                let multiple = 1;
                let className = "";
                let id = "";
                let match = tag.match(/^([\w-]*)(?:#([\w-]+))?(?:\.([\w-.]+))?\*?(\d+)?$/);

                if (!match) return null;

                let tagName = match[1] || "div"; // Extract tag name (default to div)
                if (!validTags.has(tagName)) return null; // ✅ Ensure it's a valid HTML tag

                id = match[2] ? ` id="${match[2]}"` : "";
                className = match[3] ? ` class="${match[3].replace(/\./g, ' ')}"` : "";
                multiple = match[4] ? parseInt(match[4], 10) : 1;

                let elements = "";
                for (let i = 0; i < multiple; i++) {
                    if (tagName === "a") {
                        elements += `${indent.repeat(indentLevel)}<a href=""${id}${className}></a>\n`; // ✅ Always include `href=""`
                    } else {
                        elements += `${indent.repeat(indentLevel)}<${tagName}${id}${className}>\n`;
                        openTags.push(tagName); // Track open tag for nesting
                    }
                }
                return elements;
            }

            parts.forEach((part, index) => {
                let elementHTML = createElement(part);
                if (elementHTML) {
                    html += elementHTML;
                    if (index < parts.length - 1) {
                        indentLevel++; // Increase indent level for nesting
                    }
                }
            });

            // ✅ Close open tags properly
            while (openTags.length > 0) {
                let closingTag = openTags.pop();
                indentLevel = Math.max(0, indentLevel - 1); // 🔥 FIX: Prevent indent going negative
                html += `${indent.repeat(indentLevel)}</${closingTag}>\n`;
            }

            return html.trim(); // Return valid HTML or empty if invalid
        }





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

        // ✅ Store editor globally
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

