const snippetHandlers = {
    the_title: () => insertSnippet(`<?php the_title(); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 5 }); // Move inside ()
        editor.focus();
    }),

    the_permalink: () => insertSnippet(`<?php the_permalink(); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 5 }); // Move inside ()
        editor.focus();
    }),

    the_post_thumbnail: () => insertSnippet(`<?php the_post_thumbnail(); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 5 }); // Move inside ()
        editor.focus();
    }),

    console_log: () => insertSnippet(`console.log("");`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 3 }); // Move inside ()
        editor.focus();
    }),

    alertjs: () => insertSnippet(`alert("");`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 3 }); // Move inside ()
        editor.focus();
    }),

    document_ready: () => insertSnippet(`$(document).ready(function(){ \n    console.log("Document is ready!"); \n});`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line + 1, ch: 4 }); // Move inside function
        editor.focus();
    }),

    default_css: () => insertSnippet(`body { margin: 0; padding: 0; font-family: Arial, sans-serif; }`),
    margin_auto: () => insertSnippet(`.center-div { margin: auto; width: 50%; }`)
};

// ✅ Function to insert text into the editor with callback support
function insertSnippet(code, callback) {
    if (typeof replaceSelectedTextInsideEditor === "function") {
        replaceSelectedTextInsideEditor(code, "");

        // ✅ Ensure CodeMirror editor exists before calling callback
        if (typeof window.aiAssistantEditor !== "undefined") {
            let editor = window.aiAssistantEditor;
            let cursor = editor.getCursor();

            // ✅ Execute callback function after inserting the snippet
            if (typeof callback === "function") {
                setTimeout(() => {
                    callback(editor, cursor);
                }, 50); // Small delay to ensure text is inserted
            }
        }
    } else {
        console.warn("🚨 replaceSelectedTextInsideEditor function is missing!");
    }
}
