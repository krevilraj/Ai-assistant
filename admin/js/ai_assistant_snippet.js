jQuery(document).ready(function ($) {
    var editor = $("#theme-file-editor");

    // ✅ Initialize global snippetHandlers object
    window.snippetHandlers = {};

    // ✅ Function to dynamically load external JS files
    function loadScript(url, callback) {
        $.getScript(url)
            .done(callback)
            .fail(() => console.error(`🚨 Error loading script: ${url}`));
    }

    // ✅ Function to merge snippet handlers
    function mergeHandlers() {
        if (typeof wordpressSnippetHandlers !== "undefined") {
            Object.assign(snippetHandlers, wordpressSnippetHandlers);
        }
        if (typeof jsSnippetHandlers !== "undefined") {
            Object.assign(snippetHandlers, jsSnippetHandlers);
        }
        if (typeof cssSnippetHandlers !== "undefined") {
            Object.assign(snippetHandlers, cssSnippetHandlers);
        }
        console.log("✅ All snippet handlers loaded successfully!");
    }

    // ✅ Load all snippet files sequentially and merge handlers
    loadScript('/wp-content/plugins/ai-assistant/admin/js/snippet/wordpress_php_snippet.js', function () {
        loadScript('/wp-content/plugins/ai-assistant/admin/js/snippet/js_snippet.js', function () {
            loadScript('/wp-content/plugins/ai-assistant/admin/js/snippet/css_snippet.js', function () {
                mergeHandlers();
            });
        });
    });

    $(".coder_snippet .tab-link").on("click", function (e) {
        e.preventDefault();

        var tabId = $(this).data("tab");

        // Remove active class from all links and add to the clicked one
        $(".tab-link").removeClass("active");
        $(this).addClass("active");

        // Hide all tab contents
        $(".coder_snippet .coder_tab__content").hide();

        if (tabId === "all") {
            var allItems = "";
            $(".coder_tab__content:not(#all) .coding_action__list li").each(function () {
                allItems += `<li>${$(this).html()}</li>`;
            });
            $("#all .coding_action__list").html(allItems);
            $("#all").show();
        } else {
            $("#" + tabId).show();
        }
    });

    // Show "All" tab on page load
    $(".coder_snippet .tab-link[data-tab='all']").click();

    // ✅ Snippet Click Handler
    $(document).on("click", ".coding_action__list button", function () {
        const command = $(this).data("command");
        if(command === "page_link"){
            const pageLink = $(this).data("link");
            wordpressSnippetHandlers.page_link(pageLink);
            return;
        }

        const handler = snippetHandlers[command];

        if (typeof handler === "function") {
            handler(); // 🚀 Call the function dynamically
        } else {
            console.warn(`🚨 No handler found for command: ${command}`);
        }
    });




});
function insertSnippet(code, offsetOrCallback, callback) {
    if (typeof replaceSelectedTextInsideEditor === "function") {
        let selectedContent = window.selectedText ? window.selectedText.trim() : "";
        let cursorIndex = code.indexOf("@cursor@");
        let isMultiLine = cursorIndex !== -1;
        code = code.replace("@content@", selectedContent); // Remove @cursor@ placeholder
        if (isMultiLine) {
            code = code.replace("@cursor@", ""); // Remove @cursor@ placeholder

        }

        replaceSelectedTextInsideEditor(code, "");

        if (typeof window.aiAssistantEditor !== "undefined") {
            let editor = window.aiAssistantEditor;
            let cursor = editor.getCursor();

            setTimeout(() => {
                if (isMultiLine) {
                    let lines = code.substring(0, cursorIndex).split("\n");
                    let cursorLine = cursor.line + lines.length - 1;
                    let cursorCh = lines[lines.length - 1].length;

                    console.log("cursorIndex: "+cursorIndex);
                    console.log(lines);
                    console.log("2 cursorLine: "+cursorLine);
                    console.log("3 cursorCh: "+cursorCh);

                    editor.setCursor({ line: cursorLine, ch: cursorCh });
                } else if (typeof offsetOrCallback === "number") {
                    editor.setCursor({ line: cursor.line, ch: cursor.ch - offsetOrCallback });
                }

                editor.focus();

                if (typeof offsetOrCallback === "function") {
                    offsetOrCallback(editor, editor.getCursor());
                } else if (typeof callback === "function") {
                    callback(editor, editor.getCursor());
                }
            }, 50);
        }
    } else {
        console.warn("🚨 replaceSelectedTextInsideEditor function is missing!");
    }
}

function insertSnippetV2(code, offsetOrCallback, callback) {
    if (typeof replaceSelectedTextInsideEditor === "function") {
        let editor = window.aiAssistantEditor; // Get CodeMirror instance
        if (!editor) {
            console.warn("🚨 CodeMirror instance not found!");
            return;
        }

        let doc = editor.getDoc();
        let selectedContent = window.selectedText ? window.selectedText.trim() : "";

        // ✅ Replace @content@ with selected text (if empty, keep @content@ as a placeholder)
        if (selectedContent) {
            code = code.replace("@content@", selectedContent);
        }else{
            code = code.replace("@content@", "");
        }

        // ✅ Store cursor position before insertion if no text is selected
        let fallbackCursorPos = doc.getCursor();

        // ✅ Insert modified code into the editor
        replaceSelectedTextInsideEditor(code, "");

        // ✅ Find & select @cursor@
        setTimeout(() => {
            selectCursorPlaceholder(fallbackCursorPos);
        }, 50); // Small delay to ensure the editor updates
    } else {
        console.warn("🚨 replaceSelectedTextInsideEditor function is missing!");
    }
}

function selectCursorPlaceholder(fallbackCursorPos) {
    let editor = window.aiAssistantEditor;
    if (!editor) {
        console.warn("🚨 CodeMirror instance not found!");
        return;
    }

    let doc = editor.getDoc();
    let cursor = editor.getSearchCursor("@cursor@", { line: 0, ch: 0 }); // Search from the start

    if (cursor.findNext()) {
        let from = cursor.from();
        let to = cursor.to();

        doc.setSelection(from, to); // ✅ Select @cursor@
        doc.replaceSelection(""); // ✅ Remove @cursor@
        console.log("✅ @cursor@ selected and removed!");
        editor.focus();
    } else {
        // ✅ If @cursor@ is not found, set cursor back to where it was before insertion
        doc.setCursor(fallbackCursorPos);
        console.log("🚨 @cursor@ not found! Cursor reset to previous position.");
    }
}





















