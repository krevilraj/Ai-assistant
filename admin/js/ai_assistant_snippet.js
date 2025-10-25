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
    loadScript('/wp-content/plugins/Ai-assistant/admin/js/snippet/wordpress_php_snippet.js', function () {
        loadScript('/wp-content/plugins/Ai-assistant/admin/js/snippet/js_snippet.js', function () {
            loadScript('/wp-content/plugins/Ai-assistant/admin/js/snippet/css_snippet.js', function () {
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

function insertSnippetV3(prefunction, code, offsetOrCallback, callback) {
    if (typeof replaceSelectedTextInsideEditor === "function") {
        let editor = window.aiAssistantEditor;
        if (!editor) {
            console.warn("🚨 CodeMirror instance not found!");
            return;
        }

        let doc = editor.getDoc();
        let selectedContent = window.selectedText ? window.selectedText.trim() : "";

        // ✅ Replace @content@
        code = code.replace("@content@", selectedContent || "");

        // ✅ Replace @processedtext@ with return value of the prefunction (e.g. get_text_domain)
        if (typeof window[prefunction] === "function") {
            const textDomain = window[prefunction]();
            code = code.replace("@processedtext@", textDomain || "");
        } else {
            console.warn(`🚨 Function '${prefunction}' is not defined!`);
            code = code.replace("@processedtext@", "");
        }

        const fallbackCursorPos = doc.getCursor();

        replaceSelectedTextInsideEditor(code, "");

        setTimeout(() => {
            selectCursorPlaceholder(fallbackCursorPos);
        }, 50);
    } else {
        console.warn("🚨 replaceSelectedTextInsideEditor function is missing!");
    }
}


function get_text_domain() {
    return typeof ajax_object !== "undefined" && ajax_object.text_domain
        ? ajax_object.text_domain
        : "default_textdomain"; // fallback
}


/* ===========================
 * Server Action (Secure + Nonce)
 * =========================== */
(function ($) {
    // --- Helpers ---
    function dcShowAlert(msg, type) {
        if (typeof window.showAlert === "function") window.showAlert(msg, type);
        else alert(msg);
    }
    function dcToBool(v) { return v === true || v === "true" || v === 1 || v === "1"; }
    function dcGetSelectedText() {
        try {
            if (window.aiAssistantEditor && typeof window.aiAssistantEditor.getDoc === "function") {
                const doc = window.aiAssistantEditor.getDoc();
                const sel = doc.getSelection();
                if (sel && sel.trim().length) return sel;
            }
        } catch (e) {}
        if (typeof window.selectedText === "string" && window.selectedText.trim().length) return window.selectedText;
        try { return String(window.getSelection ? window.getSelection() : ""); } catch (e) { return ""; }
    }
    function dcReplaceSelectionWith(text) {
        if (typeof window.replaceSelectedTextInsideEditor === "function") {
            window.replaceSelectedTextInsideEditor(text, "");
            return true;
        }
        if (window.aiAssistantEditor && typeof window.aiAssistantEditor.getDoc === "function") {
            const doc = window.aiAssistantEditor.getDoc();
            const ranges = doc.listSelections();
            if (ranges && ranges.length) doc.replaceSelection(text, "around");
            else doc.replaceRange(text, doc.getCursor());
            window.aiAssistantEditor.focus();
            return true;
        }
        return false;
    }

    // --- Main: Server Action handler ---
    $(document).on("click", ".server-action", function () {
        const $btn = $(this);
        const action               = $btn.data("action");
        if (!action) return;

        const reload               = dcToBool($btn.data("reload"));
        const confirmRequired      = dcToBool($btn.data("confirm"));
        const confirmText          = $btn.data("confirmText") || `Are you sure to run "${action}"?`;
        const wantsPayload         = dcToBool($btn.data("payload"));
        const mustSelect           = dcToBool($btn.data("mustSelect"));
        const replaceWithResponse  = dcToBool($btn.data("replaceWithResponse"));
        const successMessage       = ($btn.data("successMessage") || "").toString().trim();
        const errorMessage         = ($btn.data("errorMessage") || "").toString().trim() || "❌ AJAX error occurred.";

        // --- Build request data ---
        const data = { action: action };

        // ✅ Include nonce if available
        if (window.ajax_object && ajax_object.nonce) {
            data._ajax_nonce = ajax_object.nonce;
        } else if ($btn.data("nonce")) {
            data._ajax_nonce = $btn.data("nonce");
        }

        // --- Handle selection payload ---
        let selectedText = "";
        if (wantsPayload || mustSelect) {
            selectedText = dcGetSelectedText();
            if (mustSelect && (!selectedText || !selectedText.trim().length)) {
                dcShowAlert("⚠️ Please select some text in the editor first.", "danger");
                return;
            }
        }
        if (wantsPayload) data.content = (selectedText || "").toString();

        // --- Handle optional data-extra='{"foo":"bar"}' ---
        const extra = $btn.data("extra");
        if (extra) {
            try {
                const obj = (typeof extra === "object") ? extra : JSON.parse(extra);
                Object.assign(data, obj);
            } catch (e) {
                console.warn("⚠️ Invalid JSON in data-extra:", extra);
            }
        }

        const runAjax = () => {
            $.ajax({
                url: (window.ajax_object && ajax_object.ajax_url) ? ajax_object.ajax_url : "",
                type: "POST",
                data: data,
                success: function (response) {
                    if (response && response.success) {
                        if (replaceWithResponse) {
                            let replacement = "";
                            if (typeof response.data === "string") {
                                replacement = response.data;
                            } else if (response.data?.replacement) {
                                replacement = response.data.replacement;
                            } else if (response.data?.content) {
                                replacement = response.data.content;
                            }
                            const ok = dcReplaceSelectionWith(replacement);
                            if (!ok) console.warn("⚠️ Could not replace selection (no editor?).");
                        }
                        dcShowAlert(successMessage || response.data || "✅ Success.", "success");
                        if (reload) location.reload();
                    } else {
                        dcShowAlert(errorMessage, "danger");
                    }
                },
                error: function () {
                    dcShowAlert(errorMessage, "danger");
                }
            });
        };

        if (confirmRequired) {
            if (confirm(confirmText)) runAjax();
        } else {
            runAjax();
        }
    });
})(jQuery);
