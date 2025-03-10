const snippetHandlers = {
    the_title: () => insertSnippet(`<?php the_title(); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 5 }); // Move inside ()
        editor.focus();
    }),

    the_permalink: () => insertSnippet(`<?php the_permalink(); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 5 }); // Move inside ()
        editor.focus();
    }),

    the_post_thumbnail: () => insertSnippet(`<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 5 }); // Move inside ()
        editor.focus();
    }),

    the_date: () => insertSnippet(`<?php echo get_the_date() ;?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 5 }); // Move inside ()
        editor.focus();
    }),

    the_excerpt: () => insertSnippet(`<?php the_excerpt(); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 5 }); // Move inside ()
        editor.focus();
    }),
    the_field: () => insertSnippet(`<?php the_field(''); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 6 }); // Move inside ()
        editor.focus();
    }),

    the_field_title: () => insertSnippet(`<?php the_field('title'); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 6 }); // Move inside ()
        editor.focus();
    }),

    the_field_description: () => insertSnippet(`<?php the_field('description'); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 6 }); // Move inside ()
        editor.focus();
    }),

    the_field_image: () => insertSnippet(`<?php the_field('image'); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 6 }); // Move inside ()
        editor.focus();
    }),

    the_field_link: () => insertSnippet(`<?php the_field('link@cursor@'); ?>`, (editor, cursor) => {
        editor.setCursor({ line: cursor.line, ch: cursor.ch - 6 }); // Move inside ()
        editor.focus();
    }),

    the_field_link_array: () => insertSnippet(`<?php
    $link_array = get_field('@cursor@'); // Retrieve the array from the 'link' custom field 
    if ($link_array && isset($link_array['url'])) {
        $link_url = esc_url($link_array['url']); // Extract the URL from the array
        ?>
        <?php echo $link_url; ?>
        <?php echo $link_array['title'] ?>
        <?php
    }
?>`)
    ,






    /*********************** Javascript ****************************************/
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
        // Find the cursor placeholder
        let cursorIndex = code.indexOf("@cursor@");

        // Remove @cursor@ from the code
        if (cursorIndex !== -1) {
            code = code.replace("@cursor@", ""); // Remove placeholder
        }

        replaceSelectedTextInsideEditor(code, "");

        // Ensure CodeMirror editor exists before calling callback
        if (typeof window.aiAssistantEditor !== "undefined") {
            let editor = window.aiAssistantEditor;
            let cursor = editor.getCursor();

            if (cursorIndex !== -1) {
                // Convert cursor index into line/ch position
                let lines = code.substring(0, cursorIndex).split("\n");
                let cursorLine = cursor.line + lines.length - 1;
                let cursorCh = lines[lines.length - 1].length; // Character position in last line

                // Move cursor to the exact @cursor@ location
                editor.setCursor({ line: cursorLine, ch: cursorCh });
                editor.focus();
            }

            // Execute callback if provided
            if (typeof callback === "function") {
                setTimeout(() => {
                    callback(editor, editor.getCursor());
                }, 50);
            }
        }
    } else {
        console.warn("🚨 replaceSelectedTextInsideEditor function is missing!");
    }
}


