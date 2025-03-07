//grab and move the popup
jQuery(document).ready(function ($) {
    // Ensure the popup has position fixed
    $("#custom-field-popup").css({
        position: "fixed",
        left: "50%",
        top: "50%",
        transform: "translate(-50%, -50%)"
    });

    // Open popup
    $(".open-custom-field-popup").on("click", function (event) {
        event.preventDefault();
        $("#custom-field-popup").show();
    });

    // Close popup
    $("#close-custom-field-popup").on("click", function () {
        $("#custom-field-popup").hide();
    });

    // Handle Tabs
    $(".custom-tab").on("click", function () {
        $(".custom-tab").removeClass("active");
        $(".custom-tab-content").removeClass("active");

        $(this).addClass("active");
        $("#" + $(this).data("tab")).addClass("active");
    });

    // Dragging functionality (fully fixed with scroll support)
    let isDragging = false;
    let offsetX, offsetY;

    $("#drag-popup-handle").on("mousedown", function (event) {
        isDragging = true;

        // Calculate offset relative to popup's current position (viewport)
        const popupPosition = $("#custom-field-popup").position();
        offsetX = event.clientX - popupPosition.left;
        offsetY = event.clientY - popupPosition.top;

        $("#drag-popup-handle").css("cursor", "grabbing");
        event.preventDefault(); // Prevents text selection while dragging
    });

    $(document).on("mousemove", function (event) {
        if (!isDragging) return;

        // Calculate new position relative to the viewport
        let newLeft = event.clientX - offsetX;
        let newTop = event.clientY - offsetY;

        // Ensure popup stays within viewport boundaries
        const windowWidth = $(window).width();
        const windowHeight = $(window).height();
        const popupWidth = $("#custom-field-popup").outerWidth();
        const popupHeight = $("#custom-field-popup").outerHeight();

        newLeft = Math.max(0, Math.min(windowWidth - popupWidth, newLeft));
        newTop = Math.max(0, Math.min(windowHeight - popupHeight, newTop));

        $("#custom-field-popup").css({
            left: newLeft + "px",
            top: newTop + "px",
            transform: "none" // Reset transform during dragging
        });
    });

    $(document).on("mouseup", function () {
        isDragging = false;
        $("#drag-popup-handle").css("cursor", "grab");
    });
});


// on button click for ACF put value in textarea
jQuery(document).ready(function ($) {
    let lastCursorPos = 0;

    $("#add__rep_field_to_textarea").on("click", function () {
        var sourceTextarea = $("#field__rep_acf")[0];
        var targetTextarea = $("#custom-form-editor")[0];

        // ✅ Get the source textarea value
        var sourceValue = sourceTextarea.value.trim();
        if (!sourceValue) {
            showAlert("❌ No content in the field editor!", "danger");
            return;
        }


        // ✅ Extract the repeater name
        var repeaterMatch = sourceValue.match(/\[repeater\s+name="([^"]+)"\]/);
        if (!repeaterMatch || !repeaterMatch[1]) {
            showAlert("❌ No 'repeater' name attribute found!", "danger");
            return;
        }

        var repeaterName = repeaterMatch[1].trim();

        // ✅ Convert repeater name to slug
        var slug = repeaterName.toLowerCase()
            .replace(/\s+/g, "_")  // Replace spaces with underscores
            .replace(/[^a-z0-9_]/g, ""); // Remove invalid characters

        // ✅ Construct the PHP repeater loop template
        var phpRepeaterCode = `<?php if (have_rows('${slug}')): $i = 0; ?>\n` +
            `    <?php while (have_rows('${slug}')) : the_row(); ?>\n` +
            `        ${window.selectedText ? window.selectedText : ""}\n` +
            `    <?php $i++; endwhile; ?>\n` +
            `<?php endif; ?>`;

        // ✅ Replace selected text in the editor with PHP repeater loop
        if (typeof replaceSelectedTextInEditor === "function" && window.selectedText) {
            replaceSelectedTextInEditor(phpRepeaterCode,"Code copied!! Press Ctrl + V to paste.");
        }

        // ✅ Extract all `name` attributes inside the repeater (EXCLUDE REPEATER NAME)
        var fieldMatches = [...sourceValue.matchAll(/\[([a-zA-Z0-9_-]+)\s+name="([^"]+)"/g)];
        var subFields = [];

        fieldMatches.forEach((match, index) => {
            if (index === 0 && match[1] === "repeater") return; // ✅ Skip first match (Repeater)

            let fieldType = match[1].trim(); // Field type (text, image, link_array)
            let fieldName = match[2].trim(); // Field label
            let fieldSlug = fieldName.toLowerCase()
                .replace(/\s+/g, "_")  // Replace spaces with underscores
                .replace(/[^a-z0-9_]/g, ""); // Remove invalid characters

            // ✅ REMOVE `url` and `value` attributes from `[link_array]`
            if (fieldType === "link_array") {
                sourceValue = sourceValue.replace(
                    new RegExp(`\\[link_array\\s+name="${fieldName}"[^\\]]*\\]`, "g"),
                    `[link_array name="${fieldName}"]`
                );
                console.log(sourceValue);
                console.log("sourcevalue test");
            }

            subFields.push({type: fieldType, name: fieldName, slug: fieldSlug});
        });

        // ✅ Append sourceTextarea content to targetTextarea
        targetTextarea.value += (targetTextarea.value ? "\n" : "") + sourceValue;

        // ✅ Create buttons dynamically inside `.after__subfield`
        var buttonContainer = $(".after__subfield").empty(); // Clear previous buttons
        subFields.forEach(function (field) {
            var subFieldButton = $("<button>")
                .text(field.name)
                .attr("data-slug", field.slug)
                .attr("data-type", field.type) // ✅ Store field type
                .addClass("sub-field-btn")
                .on("click", function () {
                    var fieldType = $(this).attr("data-type");
                    var fieldSlug = $(this).attr("data-slug");

                    var phpSubFieldCode;
                    if (fieldType === "link_array") {
                        // ✅ Generate PHP for link_array WITHOUT default URL and value attributes
                        phpSubFieldCode = `
<?php 
    $link_array = get_sub_field('${fieldSlug}'); // Retrieve the array from the 'link' custom field
    if ($link_array && isset($link_array['url']) && isset($link_array['title'])) {
        $link_url = esc_url($link_array['url']); // Extract the URL from the array
?>
        <a href="<?php echo $link_url; ?>"><?php echo esc_html($link_array["title"]); ?></a>
<?php 
    } 
?>
                    `.trim();
                    } else {
                        // ✅ Normal subfield output
                        phpSubFieldCode = `<?php the_sub_field('${fieldSlug}'); ?>`;
                    }

                    if (typeof replaceSelectedTextInEditor === "function") {
                        replaceSelectedTextInEditor(phpSubFieldCode,"Code copied!! Press Ctrl + V to paste.");
                    }
                });

            buttonContainer.append(subFieldButton);
        });

        // ✅ Clear the source textarea after inserting
        sourceTextarea.value = "";
    });

    // Update the textarea with custom behavior for checkbox and radio buttons
    $(".custom-toolbar-btn1").on("click", function () {
        var shortcodeType = $(this).attr("data-shortcode");
        var textarea = $("#field__rep_acf")[0];
        var content = textarea.value.trim();
        var shortcode = "";

        // ✅ Check if Repeater is already present
        var hasRepeater = content.includes("[/repeater]");

        if (shortcodeType === "repeater") {
            if (hasRepeater) {
                showAlert("❌ Repeater is already there, you need to add a subfield.", "danger");
                return;
            }

            // ✅ Add repeater block with cursor inside name=""
            shortcode = "[repeater name=\"\"]\n\n[/repeater]";
            textarea.value += (textarea.value ? "\n" : "") + shortcode;

            // ✅ Move cursor inside `name=""`
            let cursorPosition = textarea.value.indexOf(`name=""`) + 6;
            textarea.setSelectionRange(cursorPosition, cursorPosition);
            textarea.focus();

        } else {
            // ✅ If not repeater, ensure Repeater exists
            if (!hasRepeater) {
                showAlert("❌ First, click on the 'Repeater' button to create a repeater field.", "danger");
                return;
            }

            // ✅ Handle `link_array`
            if (shortcodeType === "link_array") {
                if (!window.selectedText || !window.selectedText.match(/<a\s+[^>]*>.*<\/a>/i)) {
                    showAlert("❌ Please select a valid anchor tag first!", "danger");
                    return;
                }

                // ✅ Extract href and text inside the anchor tag
                var anchorMatch = window.selectedText.match(/<a\s+[^>]*href=["']([^"']+)["'][^>]*>(.*?)<\/a>/i);

                if (!anchorMatch) {
                    showAlert("❌ Invalid anchor tag structure!", "danger");
                    return;
                }

                var extractedHref = anchorMatch[1].trim();  // ✅ Get the href value
                var extractedText = anchorMatch[2].trim(); // ✅ Get anchor text content

                // ✅ Encode quotes in the text (to prevent JSON errors)
                extractedText = extractedText.replace(/"/g, "&quot;");

                // ✅ Create shortcode for link_array
                shortcode = `[link_array name="" url="${extractedHref}" value="${extractedText}"]`;

            } else if (shortcodeType === "checkbox" || shortcodeType === "radio") {
                shortcode = `[${shortcodeType} name="" option=""]`;
            } else {
                shortcode = `[${shortcodeType} name=""${window.selectedText ? ` value="${window.selectedText.replace(/"/g, "&quot;")}"` : ""}]`;
            }

            // ✅ Insert shortcode inside the repeater block
            let repeaterStart = content.indexOf("[repeater");
            let repeaterEnd = content.indexOf("[/repeater]");

            if (repeaterStart !== -1 && repeaterEnd !== -1) {
                let before = content.substring(0, repeaterEnd).trim();
                let after = content.substring(repeaterEnd).trim();

                textarea.value = `${before}\n${shortcode}\n${after}`;

                // ✅ Move cursor inside `name=""`
                let cursorPosition = textarea.value.indexOf(`name=""`, repeaterStart) + 6;
                textarea.setSelectionRange(cursorPosition, cursorPosition);
                textarea.focus();
            }
        }
    });

    // Update the textarea with custom behavior for checkbox and radio buttons
    $(".custom-toolbar-btn").on("click", async function () {
        var shortcodeType = $(this).attr("data-shortcode");
        var shortcode = "";

        if (shortcodeType === "checkbox" || shortcodeType === "radio") {
            shortcode = `[${shortcodeType} name="" option=""]`;
        }
        // ✅ Handle link_array safely
        else if (shortcodeType === "link_array") {
            if (!window.selectedText || !window.selectedText.match(/<a\s+[^>]*href=["'][^"']+["'][^>]*>.*<\/a>/i)) {
                showAlert("❌ Please select a valid anchor tag first!", "danger");
                return;
            }

            // ✅ Extract href and text inside <a> tag
            let linkMatch = window.selectedText.match(/<a\s+[^>]*href=["']([^"']+)["'][^>]*>(.*?)<\/a>/i);
            let hrefValue = linkMatch ? linkMatch[1].trim() : "";
            let anchorText = linkMatch ? linkMatch[2].trim().replace(/"/g, '&quot;') : ""; // ✅ Escape double quotes

            // ✅ Generate safe `link_array` shortcode
            shortcode = `[link_array name="" url="${hrefValue}" value="${anchorText}"]`;
        } else {
            shortcode = `[${shortcodeType} name=""${window.selectedText ? ` value="${window.selectedText.replace(/"/g, '&quot;')}"` : ""}]`;
        }

        var textarea = $("#field__acf")[0];
        textarea.value = shortcode;

        // ✅ Place cursor inside `name=""`
        let cursorPosition = shortcode.indexOf(`name=""`) + 6;
        textarea.setSelectionRange(cursorPosition, cursorPosition);
        textarea.focus();
    });

    $("#add__field_to_textarea").on("click", function () {
        var sourceTextarea = $("#field__acf")[0];
        var targetTextarea = $("#custom-form-editor")[0];

        // ✅ Get the source textarea value
        var sourceValue = sourceTextarea.value.trim();
        if (!sourceValue) {
            showAlert("❌ No content in the field editor!", "danger");
            return;
        }

        // ✅ Append sourceTextarea content to targetTextarea
        targetTextarea.value += (targetTextarea.value ? "\n" : "") + sourceValue;

        // ✅ Extract name attribute content
        var nameMatch = sourceValue.match(/name="([^"]+)"/);
        if (!nameMatch || !nameMatch[1]) {
            showAlert("❌ No 'name' attribute found!", "danger");
            return;
        }

        var nameAttribute = nameMatch[1].trim();

        // ✅ Convert name to slug
        var slug = nameAttribute.toLowerCase()
            .replace(/\s+/g, "_")  // Replace spaces with underscores
            .replace(/[^a-z0-9_]/g, ""); // Remove invalid characters

        // ✅ Handle `link_array` type
        if (sourceValue.startsWith("[link_array")) {
            if (!window.selectedText || !window.selectedText.match(/<a\s+[^>]*>.*<\/a>/i)) {
                showAlert("❌ Please select a valid anchor tag first!", "danger");
                return;
            }

            // ✅ Replace existing href and content inside <a> tag
            var updatedAnchorTag = window.selectedText.replace(
                /<a\s+([^>]*)href\s*=\s*["'][^"']*["']([^>]*)>.*<\/a>/i,
                `<a $1href="<?php echo $link_url; ?>"$2><?php echo $link_array["title"]; ?></a>`
            );

            // ✅ Wrap the anchor tag with ACF link array PHP
            var phpLinkArrayCode = `
<?php 
    $link_array = get_field('${slug}'); // Retrieve the array from the 'link' custom field
    if ($link_array && isset($link_array['url'])) {
        $link_url = esc_url($link_array['url']); // Extract the URL from the array
?>
        ${updatedAnchorTag}
<?php 
    } 
?>
        `.trim();

            if (typeof replaceSelectedTextInEditor === "function") {
                replaceSelectedTextInEditor(phpLinkArrayCode,"✅ Link field added successfully!");
            }
            sourceTextarea.value = "";
            return;
        }

        // ✅ Create the PHP field output for normal fields
        var phpFieldCode = `<?php the_field('${slug}'); ?>`;
        sourceTextarea.value = "";

        if (typeof replaceSelectedTextInEditor === "function") {
            let message = `✅ Field '${nameAttribute}' added successfully!`;
            replaceSelectedTextInEditor(phpFieldCode,message);
        }
    });

    // Handle option button click
    $("button[data-shortcode='options']").on("click", function () {
        var textarea = $("#field__acf")[0];
        var content = textarea.value;

        var selectedOption = window.selectedText.trim(); // Get selected text globally
        if (!selectedOption) {
            alert("❌ No text selected!");
            return;
        }

        console.log("Selected Option:", selectedOption);

        // ✅ Use regex to find the option attribute inside the shortcode
        var optionMatch = content.match(/option="([^"]*)"/);

        if (optionMatch) {
            var existingOptions = optionMatch[1].trim();

            // ✅ Append new option properly
            var newOptions = existingOptions ? existingOptions + "|" + selectedOption : selectedOption;

            // ✅ Replace option attribute with updated value
            var updatedContent = content.replace(/option="([^"]*)"/, `option="${newOptions}"`);

            textarea.value = updatedContent;

            // ✅ Keep the cursor at the end of the updated option
            var newCursorPos = updatedContent.indexOf(`option="${newOptions}"`) + `option="${newOptions}"`.length;
            textarea.setSelectionRange(newCursorPos, newCursorPos);
            textarea.focus();
        } else {
            alert("❌ No option attribute found in the shortcode!");
        }
    });

    // Update last cursor position on keypress or click inside textarea
    $("#custom-form-editor").on("click keyup", function () {
        lastCursorPos = this.selectionStart;
    });

    // Handle JSON creation button
    $("#create-json-btn").on("click", function () {
        var group_field = $("#group__field");
        if (!group_field.val().trim()) {
            showAlert("No group field name available", "danger");
            return;
        }

        var textareaContent = $("#custom-form-editor").val().trim();
        if (!textareaContent) {
            showAlert("No content available to create JSON.", "danger");
            return;
        }

        var fields = [];
        var lines = textareaContent.split("\n");
        var repeaterStack = []; // Stack to track nested repeaters

        lines.forEach(function (line) {
            // ✅ Check for repeater start
            var repeaterMatch = line.match(/\[repeater\s+name="([^"]+)"\]/);
            if (repeaterMatch) {
                var repeaterName = repeaterMatch[1].trim();
                var repeaterSlug = repeaterName.toLowerCase()
                    .replace(/\s+/g, "_")
                    .replace(/[^a-z0-9_]/g, "");

                // Push new repeater to stack
                repeaterStack.push({
                    key: "field_" + repeaterSlug,
                    label: repeaterName,
                    name: repeaterSlug,
                    type: "repeater",
                    sub_fields: []
                });

                return; // Skip further processing for this line
            }

            // ✅ Check for repeater end
            if (line.match(/\[\/repeater\]/)) {
                if (repeaterStack.length > 0) {
                    var completedRepeater = repeaterStack.pop();
                    if (repeaterStack.length > 0) {
                        repeaterStack[repeaterStack.length - 1].sub_fields.push(completedRepeater);
                    } else {
                        fields.push(completedRepeater);
                    }
                }
                return;
            }

            // ✅ Match normal fields including `link_array`
            var match = line.match(/\[([a-zA-Z0-9_-]+)\s+name="([^"]+)"(?:\s+url="([^"]*)")?(?:\s+value="((?:(?!<\/?script).)*)")?(?:\s+option="([^"]*)")?\]/);

            if (match) {
                var type = match[1];  // Field type (e.g., text, textarea, checkbox, image)
                var name = match[2];  // Field name (label)
                var url = match[3] ? match[3].trim() : "";
                var value = match[4] ? match[4].trim() : "";
                var optionsRaw = match[5] || "";

                var slug = name.toLowerCase()
                    .replace(/\s+/g, "_")
                    .replace(/[^a-z0-9_]/g, "");

                // ✅ Decode HTML Entities in `value`
                var decodeEntities = function (str) {
                    var textarea = document.createElement("textarea");
                    textarea.innerHTML = str;
                    return textarea.value;
                };

                value = decodeEntities(value); // ✅ Fix JSON parsing issue

                var field = {
                    key: "field_" + slug,
                    label: name,
                    name: slug,
                    type: type
                };

                // ✅ Ensure link fields are properly formatted
                if (type === "link_array" || type === "link") {
                    field.type = "link";
                    field.return_format = "array"; // ACF return format for link array
                    field.default_value = {
                        "url": url,
                        "title": value // ✅ Store visible link text separately!
                    };
                }

                // ✅ Handle options for checkbox and radio
                if ((type === "checkbox" || type === "radio") && optionsRaw) {
                    field.choices = optionsRaw.split("|").reduce(function (acc, option) {
                        var trimmedOption = option.trim();
                        if (trimmedOption) {
                            acc[trimmedOption] = trimmedOption;
                        }
                        return acc;
                    }, {});
                }

                // ✅ If inside a repeater, add to sub_fields of the last repeater in stack
                if (repeaterStack.length > 0) {
                    repeaterStack[repeaterStack.length - 1].sub_fields.push(field);
                } else {
                    fields.push(field);
                }
            }
        });

        if (fields.length === 0) {
            showAlert("No valid fields found.", "danger");
            return;
        }

        // ✅ Get the selected location rule
        var selectedParam = $(".acf-location-param").val();
        var selectedValue = $(".acf-location-value").val();

        if (!selectedParam || !selectedValue) {
            showAlert("Please select location conditions.", "danger");
            return;
        }

        var locationData = [[{param: selectedParam, operator: "==", value: selectedValue}]];

        var jsonData = {
            key: "group_" + Date.now(),
            title: group_field.val().trim(),
            fields: fields,
            location: locationData,
            style: "default",
            label_placement: "top",
            instruction_placement: "label",
            hide_on_screen: []
        };

        // ✅ Send JSON to AJAX
        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                action: "save_acf_json",
                json_data: JSON.stringify(jsonData),
                location_data: JSON.stringify(locationData)
            },
            success: function (response) {
                showAlert(response.data, "success");
            },
            error: function () {
                showAlert("Failed to save JSON.", "danger");
            }
        });
    });


    $(document).ready(function () {
        $("#contact_form7 .contact-form-btn").on("click", function () {
            let shortcodeType = $(this).data("shortcode");
            let textarea = $("#wpcf7-form");
            let selectedHtml = extractSelectedContent(textarea);

            if (selectedHtml.trim() === "") {
                alert("Please select an input field inside the textarea first.");
                return;
            }

            if (shortcodeType === "convert_to_mail") {
                let shortcodes = extractShortcodesFromTextarea(textarea);
                let mailText = generateParagraphFromShortcodes(shortcodes);

                $("#contact-form-editor-tabs li.active").removeClass('active');
                $("#contact-form-editor-tabs #mail-panel-tab").addClass("active");
                let mailTab = $("#contact-form-editor-tabs #mail-panel-tab").data('panel');
                $("section.contact-form-editor-panel.active").removeClass('active');
                $("section.contact-form-editor-panel").hide();
                $("#" + mailTab).addClass('active').show();

                let mailBodyTextarea = $("#wpcf7-mail-body");
                let mailBodyText = mailBodyTextarea.val();

                // ✅ Check if `--` is present in the textarea
                let separatorIndex = mailBodyText.indexOf("--");

                if (separatorIndex !== -1) {
                    // ✅ If `--` exists, replace everything before it
                    let newText = mailText + "\n" + mailBodyText.substring(separatorIndex);
                    mailBodyTextarea.val(newText);
                } else {
                    // ✅ If `--` is not found, just append
                    mailBodyTextarea.val(mailText);
                }

                // ✅ Scroll smoothly to #wpcf7-mail-body
                $("html, body").animate({
                    scrollTop: mailBodyTextarea.offset().top - 100
                }, 500);

                return;
            } else {
                let shortcode = generateCF7Shortcode(selectedHtml, shortcodeType);
                if (shortcode) {
                    replaceSelectedContent(textarea, shortcode);
                }
            }
        });



        function generateParagraphFromShortcodes(shortcodes) {
            let output = [];

            shortcodes.forEach(shortcode => {
                // Extract field name and placeholder using regex
                let nameMatch = shortcode.match(/\[([a-zA-Z0-9_*]+)\s+([^\s\]]+)/);
                let placeholderMatch = shortcode.match(/placeholder\s+"([^"]+)"/);

                let label = "";

                if (placeholderMatch) {
                    label = placeholderMatch[1]; // Take the placeholder text
                } else if (nameMatch) {
                    label = nameMatch[2].replace(/[_-]/g, " "); // Take field name and format it
                } else {
                    return; // Skip if no valid label found
                }

                // Capitalize first letter
                label = label.charAt(0).toUpperCase() + label.slice(1);

                // Extract the shortcode name
                let shortcodeField = nameMatch ? `[${nameMatch[2]}]` : "";

                output.push(`${label}: ${shortcodeField}`);
            });

            return output.join("\n");
        }

        function extractShortcodesFromTextarea(textarea) {
            let text = textarea.val();
            let shortcodeRegex = /\[([a-zA-Z0-9_*]+)([^\]]*)\]/g;
            let matches = [...text.matchAll(shortcodeRegex)].map(match => match[0]); // Extract full shortcodes

            return matches;
        }

        function extractSelectedContent(textarea) {
            let start = textarea[0].selectionStart;
            let end = textarea[0].selectionEnd;
            return textarea.val().substring(start, end);
        }

        function replaceSelectedContent(textarea, newText) {
            let start = textarea[0].selectionStart;
            let end = textarea[0].selectionEnd;
            let text = textarea.val();

            textarea.val(text.substring(0, start) + newText + text.substring(end));
        }


        function generateCF7Shortcode(htmlString, shortcodeType) {
            let tempDiv = $("<div>").html(htmlString);
            let input = tempDiv.find("input, select, textarea, button");

            // ✅ Handle Submit Button
            if (shortcodeType === "submit") {
                let button = tempDiv.find("button, input[type='submit']");

                if (button.length === 0) {
                    alert("No valid submit button found in the selection.");
                    return "";
                }

                let buttonText = button.is("button") ? button.text().trim() : button.attr("value") || "Submit";
                let classes = button.attr("class") ? `class:${button.attr("class").replace(/\s+/g, " class:")}` : "";

                return `[submit ${classes} "${buttonText}"]`;
            }
            // ✅ Handle Acceptance Field (before checking input fields)
            if (shortcodeType === "acceptance") {
                let selectedText = htmlString.trim();

                if (!selectedText) {
                    alert("Please select the acceptance text first.");
                    return "";
                }

                let randomNumber = Math.floor(Math.random() * 1000);
                return `[acceptance acceptance-${randomNumber}]${selectedText}[/acceptance]`;
            }

            if (input.length === 0) {
                alert("No valid input field found in the selection.");
                return "";
            }

            let isTextarea = input.is("textarea");
            let isSelect = input.is("select");
            let isCheckbox = input.is('input[type="checkbox"]');
            let isRadio = input.is('input[type="radio"]');
            let isFile = input.is('input[type="file"]');

            let type = isTextarea ? "textarea" :
                isSelect ? "select" :
                    isCheckbox ? "checkbox" :
                        isRadio ? "radio" :
                            isFile ? "file" :
                                input.attr("type") || "text";

            let name = input.attr("name") || input.attr("id") || "";

            if (!name) {
                name = `field-${Math.floor(Math.random() * 1000)}`; // ✅ Generate random name if missing
            }

            // ✅ Remove `[]` from names (checkbox, radio, file)
            name = name.replace(/\[\]$/, "");

            let classes = input.attr("class") ? `class:${input.attr("class").replace(/\s+/g, " class:")}` : "";
            let placeholderAttr = input.attr("placeholder") ? `placeholder "${input.attr("placeholder")}"` : "";
            let isRequired = input.is("[required]"); // Check if required exists

            let fieldTypeMapping = {
                text: "text",
                textarea: "textarea",
                number: "number",
                url: "url",
                email: "email",
                tel: "tel",
                date: "date",
                checkbox: "checkbox",
                radio: "radio",
                file: "file",
                acceptance: "acceptance",
                submit: "submit",
                drop_down_menu: "select",
            };

            let shortcodeTypeMapped = isFile ? "file" :
                isCheckbox ? "checkbox" :
                    isRadio ? "radio" :
                        fieldTypeMapping[shortcodeType] || "text";

            // Append '*' for required fields
            if (isRequired) {
                shortcodeTypeMapped += "*";
            }

            // ✅ Handle `<select>` (Dropdown menu)
            if (isSelect) {
                let options = input.find("option").map(function () {
                    return `"${$(this).val()}"`;
                }).get().join(" ");

                return `[${shortcodeTypeMapped} ${name} ${classes} ${options}]`;
            }

            // ✅ Handle Checkbox
            if (isCheckbox) {
                let checkboxes = tempDiv.find(`input[type="checkbox"][name="${input.attr("name")}"]`);
                let options = checkboxes.map(function () {
                    return `"${$(this).val()}"`;
                }).get().join(" ");

                return `[${shortcodeTypeMapped} ${name} ${classes} ${options}]`;
            }

            // ✅ Handle Radio
            if (isRadio) {
                let radios = tempDiv.find(`input[type="radio"][name="${input.attr("name")}"]`);
                let options = radios.map(function () {
                    return `"${$(this).val()}"`;
                }).get().join(" ");

                return `[${shortcodeTypeMapped} ${name} ${classes} ${options}]`;
            }

            // ✅ Handle File Upload
            if (isFile) {
                let acceptAttr = input.attr("accept") || "";
                let fileTypes = acceptAttr ? `filetypes:${acceptAttr.replace(/,\s*/g, "|")}` : "";
                let fileLimit = "limit:1mb"; // Default size limit

                return `[${shortcodeTypeMapped} ${name} ${classes} ${fileTypes} ${fileLimit}]`;
            }

            return `[${shortcodeTypeMapped} ${name} ${classes} ${placeholderAttr}]`;
        }


    });


});


async function requestClipboardPermission() {
    try {
        // Check clipboard permissions
        const permissionStatus = await navigator.permissions.query({name: "clipboard-read"});

        if (permissionStatus.state === "granted") {
            console.log("Clipboard access is already granted.");
        } else if (permissionStatus.state === "prompt") {
            console.log("Requesting clipboard access... Click a button to proceed.");
        } else {
            console.warn("Clipboard access is denied. Check browser settings.");
        }
    } catch (error) {
        console.error("Clipboard permission request failed:", error);
    }
}

// Call this function when the page loads (optional)
requestClipboardPermission();

jQuery(document).ready(function ($) {
    var postTypes = [];
    var pages = [];
    var pageTemplates = [];
    var taxonomies = [];

    // Fetch dynamic data only once on page load
    function fetchDynamicData() {
        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {action: "fetch_acf_location_data"},
            success: function (response) {
                if (response.success) {
                    postTypes = response.data.post_types;
                    pages = response.data.pages;
                    pageTemplates = response.data.page_templates;
                    taxonomies = response.data.taxonomies;

                    // Set default dropdown values (for post_type initially)
                    populateValueDropdown($(".acf-location-param").val());
                }
            },
            error: function () {
                console.error("AJAX Error: Failed to fetch location data.");
            }
        });
    }

    function populateValueDropdown(selectedParam) {
        var valueSelect = $(".acf-location-value");
        valueSelect.empty();
        valueSelect.append('<option value="">Select a value</option>');

        if (selectedParam === "post_type") {
            postTypes.forEach(function (type) {
                valueSelect.append(`<option value="${type}">${type}</option>`);
            });
        } else if (selectedParam === "page") {
            pages.forEach(function (page) {
                valueSelect.append(`<option value="${page.id}">${page.title}</option>`);
            });
        } else if (selectedParam === "page_template") {
            pageTemplates.forEach(function (template) {
                valueSelect.append(`<option value="${template.file}">${template.label}</option>`); // ✅ Correctly show label instead of filename
            });
        } else if (selectedParam === "taxonomy") {
            taxonomies.forEach(function (taxonomy) {
                valueSelect.append(`<option value="${taxonomy}">${taxonomy}</option>`);
            });
        }
    }


    // Fetch data when page loads
    fetchDynamicData();

    // When first dropdown changes, update second dropdown dynamically
    $(".acf-location-param").on("change", function () {
        populateValueDropdown($(this).val());
    });


});

function showAlert(message, type) {
    // Remove existing alerts
    jQuery(".custom-alert").remove();

    // Create alert element
    var alertBox = jQuery('<div class="custom-alert ' + type + '">' + message + '</div>');

    // Append to body
    jQuery("body").append(alertBox);

    // Slide in
    setTimeout(function () {
        alertBox.css("right", "20px");
    }, 100); // Slight delay for smooth transition

    // Slide out after 3 seconds
    setTimeout(function () {
        alertBox.css("right", "-400px");
        setTimeout(function () {
            alertBox.remove(); // Remove from DOM after slide out
        }, 500); // Wait for transition to finish
    }, 3000); // Stay for 3 seconds

    setTimeout(function () {
        jQuery(".icon-wrapper").removeClass('anim');
    }, 2000);
}

jQuery(document).ready(function ($) {
    function displayGroupedFields(fields) {
        const container = $(".acf-field-container");
        container.empty();

        let tabWrapper = null;

        fields.forEach((field) => {
            if (field.type === "tab") {
                if (tabWrapper) {
                    container.append(tabWrapper);
                }
                tabWrapper = $('<div class="tab-wrapper"></div>');
                tabWrapper.append(`<label>${field.label}</label>`);
            } else {
                if (!tabWrapper) {
                    tabWrapper = $('<div class="tab-wrapper"></div>');
                    tabWrapper.append(`<label>Default</label>`);
                }
                tabWrapper.append(
                    `<button class="acf-field-btn" data-slug="${field.slug}" data-type="${field.type}">${field.label}</button>`
                ); // ✅ Added data-type
            }
        });

        if (tabWrapper) {
            container.append(tabWrapper);
        }
    }

    $("#get_custom_fields").on("click", function () {
        var pageUrl = $("#url").val().trim();

        if (!pageUrl) {
            alert("Please enter a valid URL.");
            return;
        }

        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                action: "get_custom_fields_from_url",
                page_url: pageUrl,
            },
            success: function (response) {
                if (response.success) {
                    $(".acf-field-container").empty();
                    if (response.data.length === 0) {
                        $(".acf-field-container").append("<p>No custom fields found.</p>");
                    } else {
                        displayGroupedFields(response.data);
                    }
                } else {
                    $(".acf-field-container").html("<p>No custom fields available.</p>");
                }
            },
            error: function () {
                $(".acf-field-container").html("<p>Failed to load custom fields.</p>");
            },
        });
    });


    // reset the permalink select the post name default
    $("#reset_permalink").on("click", function () {
        if (confirm("Are you sure you want to reset permalinks to 'Post name'?")) {
            $.ajax({
                url: ajax_object.ajax_url, // Ensure ajax_object is localized in WordPress
                type: "POST",
                data: {
                    action: "reset_permalink"
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.data); // Success message
                        location.reload();    // Reload to reflect new permalinks
                    } else {
                        showAlert("❌ Failed to reset permalinks.", "danger");
                    }
                },
                error: function () {
                    showAlert("❌ AJAX error occurred.", "danger");
                }
            });
        }
    });

    $(".open__child").on("click", function () {
        var actionSetting = $(this).siblings(".action__setting");
        $(this).toggleClass("active"); // Toggle 'active' class for rotation

        if (actionSetting.is(":visible")) {
            actionSetting.slideUp();
        } else {
            actionSetting.slideDown();
        }
    });
});

jQuery(document).ready(function ($) {

    // ✅ Populate input fields with saved values
    function populateThemeDetails() {
        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {action: "ai_assistant_get_theme_details"},
            success: function (response) {
                if (response.success) {
                    const themeSection = $("li.create_theme");

                    themeSection.find('input[name="theme_name"]').val(response.data.theme_name);
                    themeSection.find('input[name="theme_uri"]').val(response.data.theme_uri);
                    themeSection.find('input[name="author"]').val(response.data.author);
                    themeSection.find('input[name="author_uri"]').val(response.data.author_uri);
                    themeSection.find('input[name="text_domain"]').val(response.data.text_domain);
                }
            },
            error: function () {
                showAlert("❌ Failed to load theme details.", "danger");
            }
        });
    }


    populateThemeDetails(); // ✅ Load on page load


    // ✅ Create theme and save data


    const actionHandlers = {
        change_default_page: handleChangeDefaultPage,
        create_theme: handleCreateTheme,
        create_page_and_template_file: handleCreatePageAndTemplate,
        create_menu: handleCreateMenu,
        correct_header: handleCorrectHeader,
        correct_footer: handleCorrectFooter,
        correct_menu: handleCorrectMenu,
        create_custom_post_type: handleCreateCPT,
        create_user_type: handleCreateUserType,
        remove_user_type: handleDeleteUserType,
        change__admin_page_link: handleAdminUrl,
        // ⚡️ Add new actions here without modifying main event handler
    };

    $(document).on("click", ".change_setting", function () {
        const _this = $(this);
        const action = _this.data('action');
        const handler = actionHandlers[action];

        if (typeof handler === 'function') {
            handler(_this, $); // 🚀 Dynamically call the handler function
        } else {
            console.warn(`🚨 No handler defined for action: ${action}`);
        }
    });

    // 🌟 ✅ Common Utility Functions
    function showLoading(_this) {
        const statusElement = $('<span class="btn__status"><em>wait...</em></span>');
        _this.find('.icon-wrapper').append(statusElement);
        return statusElement;
    }

    function showSuccess(_this, message) {
        showAlert(`✅ ${message}`, "success");
        _this.find('.icon-wrapper').addClass("anim");
        setTimeout(() => _this.find('.icon-wrapper').removeClass("anim"), 1200);
    }

    function sendAjax(data, _this, callback) {
        const statusElement = showLoading(_this);
        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: data,
            success: function (response) {
                statusElement.remove();
                if (response.success) {
                    showSuccess(_this, response.data);
                    if (callback) callback(response);
                } else {
                    showAlert(`❌ ${response.data}`, "danger");
                }
            },
            error: function (xhr) {
                statusElement.remove();
                showAlert(`❌ AJAX error: ${xhr.responseText}`, "danger");
            }
        });
    }

    // 💡 💡 💡 Handler Functions Below (Reusable & Clean) 💡 💡 💡
    // change default homepage
    function handleChangeDefaultPage(_this, $) {
        const pageId = _this.closest('li').find('[name="page_id"]').val().trim();
        if (!pageId) return showAlert("❌ Page ID is required.", "danger");
        sendAjax({action: "set_homepage", page_id: pageId}, _this);
    }

    // create folder and files for theme
    function handleCreateTheme(_this, $) {
        const parentLi = _this.closest('li');
        const themeData = {
            action: "ai_assistant_create_theme",
            theme_name: parentLi.find('[name="theme_name"]').val().trim(),
            theme_uri: parentLi.find('[name="theme_uri"]').val().trim(),
            author: parentLi.find('[name="author"]').val().trim(),
            author_uri: parentLi.find('[name="author_uri"]').val().trim(),
            text_domain: parentLi.find('[name="text_domain"]').val().trim()
        };

        if (!themeData.theme_name) return showAlert("❌ Theme Name is required.", "danger");
        sendAjax(themeData, _this, populateThemeDetails);
    }

    //create page and template
    function handleCreatePageAndTemplate(_this, $) {
        const parentLi = _this.closest('li');
        var pageDom = parentLi.find('[name="page_name"]');
        const pageName = pageDom.val().trim();
        const createTemplate = parentLi.find('[name="create_page_template"]').is(':checked') ? 1 : 0;

        if (!pageName) return showAlert("❌ Page name is required.", "danger");
        sendAjax({
            action: "ai_assistant_create_page_and_template",
            page_name: pageName,
            create_template: createTemplate
        }, _this);
        pageDom.val("");
    }

    // Create menu and redirect to menu page
    function handleCreateMenu(_this, $) {
        const menuName = _this.closest('li').find('input[name="menu_name"]').val().trim();
        if (!menuName) return showAlert("❌ Menu Name is required.", "danger");

        sendAjax({action: "ai_assistant_create_menu", menu_name: menuName}, _this, function (response) {
            setTimeout(() => window.location.href = `/wp-admin/nav-menus.php?action=edit&menu=${response.data.menu_id}`, 1500);
        });
    }

    // handle Correction of Header
    function handleCorrectHeader(_this, $) {
        const headerContent = _this.closest('li').find('textarea[name="correct_header"]').val().trim();
        if (!headerContent) return showAlert("❌ Header content is required.", "danger");

        sendAjax({action: "ai_assistant_correct_header", header_content: headerContent}, _this);
    }

    //handle Correction of Footer
    function handleCorrectFooter(_this, $) {
        const footerContent = _this.closest('li').find('textarea[name="correct_footer"]').val().trim();
        if (!footerContent) return showAlert("❌ Footer content cannot be empty.", "danger");

        sendAjax({action: "ai_assistant_correct_footer", footer_content: footerContent}, _this);
    }

    // handle correction of menu


    let selectedMenuHtml = "";
    let selectionStart = 0;
    let selectionEnd = 0;

    // ✅ Track selected text in the textarea
    $("#theme-file-editor").on("mouseup keyup", function () {
        selectionStart = this.selectionStart;
        selectionEnd = this.selectionEnd;
        selectedMenuHtml = this.value.substring(selectionStart, selectionEnd);
    });

    // ✅ Function to replace selected text in textarea
    function replaceSelectedText(newText) {
        const textarea = document.getElementById('theme-file-editor');
        const content = textarea.value;

        // ✅ Replace only the selected text
        const updatedContent = content.substring(0, selectionStart) +
            newText +
            content.substring(selectionEnd);

        textarea.value = updatedContent;
        textarea.focus();
    }

    function handleCorrectMenu(_this, $) {
        console.log(window.selectedText); // ✅ Now using global selectedText
        const parentLi = _this.closest('li');
        const menuName = parentLi.find('[name="menu__name"]').val().trim();

        if (!menuName) return showAlert("❌ Please select a menu.", "danger");

        // ✅ Ensure the selection from CodeMirror is used
        let selectedMenuHtml = typeof window.selectedText !== "undefined" ? window.selectedText.trim() : "";

        if (!selectedMenuHtml) {
            showAlert("❌ Please select the menu HTML in the editor before clicking.", "danger");
            return;
        }

        // ✅ Send selected menu to AJAX
        sendAjax({
            action: "ai_assistant_correct_menu",
            menu_name: menuName,
            menu_html: selectedMenuHtml
        }, _this, function (response) {
            if (response.success) {
                showAlert(response.data.message, "success");

                // ✅ Replace selected menu HTML with the WordPress menu code
                if (typeof replaceSelectedTextInEditor === "function") {
                    replaceSelectedTextInEditor(response.data.menu_code,"Wordpress menu copied!! Press Ctrl + V to paste.");
                }
            } else {
                showAlert(response.data, "danger");
            }
        });
    }


    // 🌟 🚀 🌟 Handle CPT Creation
    function handleCreateCPT(_this, $) {
        const parentLi = _this.closest('li');
        const cptData = {
            action: "ai_assistant_create_cpt",
            cpt_slug: parentLi.find('[name="cpt_slug"]').val().trim(),
            plural_label: parentLi.find('[name="plural__label"]').val().trim(),
            singular_label: parentLi.find('[name="singular__label"]').val().trim(),
            dashi_icon: parentLi.find('[name="dashi_icon"]').val().trim(),
            supports: []
        };

        if (!cptData.cpt_slug || !cptData.plural_label || !cptData.singular_label) {
            return showAlert("❌ Slug, Plural Label, and Singular Label are required.", "danger");
        }

        if (parentLi.find('[name="cpt__editor"]').is(':checked')) cptData.supports.push('editor');
        if (parentLi.find('[name="cpt__featured_image"]').is(':checked')) cptData.supports.push('thumbnail');
        const createTemplate = parentLi.find('[name="cpt__template"]').is(':checked') ? 1 : 0;
        cptData.create_template = createTemplate;

        sendAjax(cptData, _this, function (response) {
            if (createTemplate) {
                showAlert(`✅ Custom Post Type '${cptData.cpt_slug}' created with single-${cptData.cpt_slug}.php template.`, "success");
            } else {
                showAlert(`✅ Custom Post Type '${cptData.cpt_slug}' created successfully.`, "success");
            }
        });
    }

    function handleCreateUserType(_this, $) {
        const parentLi = _this.closest('li');
        const userType = parentLi.find('input[name="user_type"]:text').val().trim();
        const userRole = parentLi.find('input[name="user_type"]:checked').val();

        if (!userType) return showAlert("❌ User type is required.", "danger");
        if (!userRole) return showAlert("❌ Please select a role.", "danger");

        sendAjax({action: "ai_assistant_create_user_type", user_type: userType, user_role: userRole}, _this);
    }

    function handleDeleteUserType(_this, $) {
        const parentLi = _this.closest('li');
        const userType = parentLi.find('input[name="remove_user_type"]:text').val().trim();
        sendAjax({action: "ai_assistant_delete_user_role", role: userType}, _this);
    }

    function handleAdminUrl(_this, $) {
        const parentLi = _this.closest('li');
        const adminLink = parentLi.find('input[name="admin__page_link"]').val().trim();

        if (!adminLink) {
            showAlert("❌ Admin Page Link is required.", "danger");
            return;
        }

        // Construct the PHP echo statement dynamically
        const phpCode = `<?php echo admin_url('${adminLink}'); ?>`;

        prompt("Copy to clipboard: Ctrl+C, Enter", phpCode);
        // Show the PHP code in an alert
        showAlert(`✅ Generated PHP Code:\n${phpCode}`, "success");
    }


    // 🔹 Define global variables for selection tracking
    window.selectionStart = 0;
    window.selectionEnd = 0;
    window.selectedText = "";

    window.aiAssistantInitEditor = function () {
        if (typeof wp === 'undefined' || typeof wp.CodeMirror === 'undefined') {
            console.error("❌ CodeMirror not loaded!");
            return;
        }

        var editor = wp.CodeMirror.fromTextArea(document.getElementById("theme-file-editor"), {
            mode: "php",
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 4,
            tabSize: 4,
            theme: "default",
            matchBrackets: true,
            autoCloseBrackets: true,
            styleActiveLine: true
        });

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
                }, 10); // Small delay to fix missing last character
            }
        });

        // ✅ Function to replace selected text
        window.replaceSelectedTextInEditor = function (newText, message) {
            if (!window.selectedText) {
                // ✅ Auto-copy to clipboard
                let tempInput = $("<input>");
                $("body").append(tempInput);
                tempInput.val(newText).select();
                document.execCommand("copy");
                tempInput.remove();
                showAlert(message, 'success');
            }else{
                editor.replaceSelection(newText);
                editor.focus();
            }

        };

        // ✅ Store editor globally
        window.aiAssistantEditor = editor;
    };


});























