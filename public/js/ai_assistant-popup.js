jQuery(document).ready(function ($) {
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

    // Dragging functionality (only from drag handle)
    let isDragging = false;
    let offsetX, offsetY;

    $("#drag-popup-handle").on("mousedown", function (event) {
        isDragging = true;
        offsetX = event.clientX - $("#custom-field-popup").offset().left;
        offsetY = event.clientY - $("#custom-field-popup").offset().top;
        $("#drag-popup-handle").css("cursor", "grabbing");

        event.preventDefault(); // Prevents text selection while dragging
    });

    $(document).on("mousemove", function (event) {
        if (!isDragging) return;
        $("#custom-field-popup").css({
            left: event.clientX - offsetX + "px",
            top: event.clientY - offsetY + "px"
        });
    });

    $(document).on("mouseup", function () {
        isDragging = false;
        $("#drag-popup-handle").css("cursor", "grab");
    });
});

jQuery(document).ready(function ($) {
    $(".custom-toolbar-btn").on("click", async function () {
        var shortcodeType = $(this).attr("data-shortcode");
        var textarea = $("#custom-form-editor")[0]; // Get textarea element
        var selectedText = "";

        // Get selected text from the whole page
        if (window.getSelection) {
            selectedText = window.getSelection().toString().trim();
        }

        // If no text is selected, try clipboard
        if (!selectedText && navigator.clipboard && navigator.clipboard.readText) {
            try {
                selectedText = await navigator.clipboard.readText();
            } catch (err) {
                console.error("Clipboard access failed:", err);
            }
        }

        // Ensure shortcode format
        var shortcode = `[${shortcodeType} name=""${selectedText ? ` value="${selectedText}"` : ""}]`;

        // Get current textarea content
        var currentContent = textarea.value.trim();

        // Append the new shortcode at the end with a new line
        if (currentContent.length > 0) {
            textarea.value = currentContent + "\n" + shortcode;
        } else {
            textarea.value = shortcode; // If textarea is empty, just add the shortcode
        }

        // Move cursor **inside** `name=""` of the new shortcode
        var newCursorPos = textarea.value.lastIndexOf(`name=""`) + 6;
        textarea.setSelectionRange(newCursorPos, newCursorPos);
        textarea.focus();
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
            data: { action: "fetch_acf_location_data" },
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

    // Handle JSON creation button
    $("#create-json-btn").on("click", function () {
        var textareaContent = $("#custom-form-editor").val().trim();
        if (!textareaContent) {
            alert("No content available to create JSON.");
            return;
        }

        var fields = [];
        var lines = textareaContent.split("\n");

        lines.forEach(function (line) {
            var match = line.match(/\[([a-zA-Z0-9_-]+)\s+name="([^"]+)"(?:\s+value="([^"]*)")?\]/);
            if (match) {
                var type = match[1];
                var name = match[2];
                var value = match[3] || "";

                var slug = name.toLowerCase().replace(/\s+/g, "_").replace(/[^a-z0-9_]/g, "");

                fields.push({
                    key: "field_" + slug,
                    label: name,
                    name: slug,
                    type: type,
                    default_value: value
                });
            }
        });

        if (fields.length === 0) {
            alert("No valid fields found.");
            return;
        }

        // Get the selected location rule
        var selectedParam = $(".acf-location-param").val();
        var selectedValue = $(".acf-location-value").val();

        if (!selectedParam || !selectedValue) {
            alert("Please select location conditions.");
            return;
        }

        var locationData = [[{ param: selectedParam, operator: "==", value: selectedValue }]];

        var jsonData = {
            key: "group_" + Date.now(),
            title: "Custom Fields",
            fields: fields,
            location: locationData, // Now including the location
            style: "default",
            label_placement: "top",
            instruction_placement: "label",
            hide_on_screen: []
        };

        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                action: "save_acf_json",
                json_data: JSON.stringify(jsonData),
                location_data: JSON.stringify(locationData) // Send location rules
            },
            success: function (response) {
                alert(response.data);
            },
            error: function () {
                alert("Failed to save JSON.");
            }
        });
    });
});


jQuery(document).ready(function ($) {
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

});

















