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

    function showAlert(message, type) {
        // Remove existing alerts
        $(".custom-alert").remove();

        // Create alert element
        var alertBox = $('<div class="custom-alert ' + type + '">' + message + '</div>');

        // Append to body
        $("body").append(alertBox);

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

    // change the default homepage with page id
    $("#change_setting").on("click", function () {
        var pageId = $("#page_id").val().trim();
        var submitbtn = $(this).find(".icon-wrapper");

        if (!pageId) {
            showAlert("❌ Please enter a valid Page ID.", "danger");
            return;
        }

        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                action: "set_homepage",
                page_id: pageId,
            },
            success: function (response) {
                if (response.success) {
                    submitbtn.addClass("anim");
                    setTimeout(function () {
                        submitbtn.removeClass("anim");
                    }, 1200); // Same as animation duration
                    showAlert("✅ Homepage updated successfully!", "success");
                } else {
                    showAlert("❌ Failed to update homepage: " + response.data, "danger");
                }
            },
            error: function () {
                showAlert("❌ Error occurred while updating homepage.", "danger");
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

    $(".open__child").on("click",function (){
        $(this).siblings(".action__setting").slideDown();
    });


});






















