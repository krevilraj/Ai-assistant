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

    // Update the textarea with custom behavior for checkbox and radio buttons
    $(".custom-toolbar-btn").on("click", async function () {
        var shortcodeType = $(this).attr("data-shortcode");
        var textarea = $("#custom-form-editor")[0];
        var selectedText = "";

        if (window.getSelection) {
            selectedText = window.getSelection().toString().trim();
        }

        if (!selectedText && navigator.clipboard && navigator.clipboard.readText) {
            try {
                selectedText = await navigator.clipboard.readText();
            } catch (err) {
                console.error("Clipboard access failed:", err);
            }
        }

        var shortcode = "";

        if (shortcodeType === "checkbox" || shortcodeType === "radio") {
            shortcode = `[${shortcodeType} name="" option=""]`;
        } else {
            shortcode = `[${shortcodeType} name=""${selectedText ? ` value="${selectedText}"` : ""}]`;
        }

        var currentContent = textarea.value.trim();
        if (currentContent.length > 0) {
            textarea.value = currentContent + "\n" + shortcode;
        } else {
            textarea.value = shortcode;
        }

        lastCursorPos = textarea.value.lastIndexOf(`name=""`) + 6;
        textarea.setSelectionRange(lastCursorPos, lastCursorPos);
        textarea.focus();
    });

    // Handle option button click
    $("button[data-shortcode='options']").on("click", function () {
        var textarea = $("#custom-form-editor")[0];
        var content = textarea.value;
        var cursorPos = textarea.selectionStart;

        var selectedOption = window.getSelection().toString().trim();

        if (selectedOption) {
            var optionAttrIndex = content.lastIndexOf('option="', cursorPos);

            if (optionAttrIndex !== -1) {
                var quoteEndIndex = content.indexOf('"', optionAttrIndex + 8);
                if (quoteEndIndex !== -1) {
                    var existingOptions = content.substring(optionAttrIndex + 8, quoteEndIndex).trim();
                    if (existingOptions) {
                        // Append with "|" only if there is existing text
                        textarea.value =
                            content.slice(0, quoteEndIndex) + "|" + selectedOption + content.slice(quoteEndIndex);
                    } else {
                        // Directly add selected text if empty
                        textarea.value =
                            content.slice(0, quoteEndIndex) + selectedOption + content.slice(quoteEndIndex);
                    }
                    cursorPos = quoteEndIndex + selectedOption.length + 1;
                } else {
                    // If no closing quote found, fallback
                    textarea.value = content.slice(0, cursorPos) + selectedOption + content.slice(cursorPos);
                    cursorPos += selectedOption.length;
                }

                textarea.setSelectionRange(cursorPos, cursorPos);
                textarea.focus();
            }
        }
    });

    // Update last cursor position on keypress or click inside textarea
    $("#custom-form-editor").on("click keyup", function () {
        lastCursorPos = this.selectionStart;
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
            var match = line.match(/\[([a-zA-Z0-9_-]+)\s+name="([^"]+)"(?:\s+value="([^"]*)")?(?:\s+option="([^"]*)")?\]/);
            if (match) {
                var type = match[1];
                var name = match[2];
                var value = match[3] || "";
                var optionsRaw = match[4] || "";

                var slug = name.toLowerCase().replace(/\s+/g, "_").replace(/[^a-z0-9_]/g, "");

                var field = {
                    key: "field_" + slug,
                    label: name,
                    name: slug,
                    type: type,
                    default_value: value
                };

                // Handle options for checkbox and radio
                if ((type === "checkbox" || type === "radio") && optionsRaw) {
                    field.choices = optionsRaw.split("|").reduce(function (acc, option) {
                        var trimmedOption = option.trim();
                        if (trimmedOption) {
                            acc[trimmedOption] = trimmedOption;
                        }
                        return acc;
                    }, {});
                }

                fields.push(field);
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
            location: locationData,
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
                location_data: JSON.stringify(locationData)
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
            data: { action: "ai_assistant_get_theme_details" },
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
    $(document).on("click", "#change_setting", function () {
        var _this = $(this);
        var parentLi = _this.closest('li'); // ✅ Get parent <li> first
        //change default homepage
        if(_this.data('action')==="change_default_page"){

            var pageId = parentLi.find('[name="page_id"]').val().trim();
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
        }
        //create theme folder and copy basic file
        else if(_this.data('action')==="create_theme"){
            var statusElement = $('<span class="btn__status"><em>wait...</em></span>');
            _this.find('.icon-wrapper').append(statusElement);


            // Get values from inputs by 'name' attribute inside the parent <li>
            var themeName = parentLi.find('[name="theme_name"]').val().trim();
            var themeUri = parentLi.find('[name="theme_uri"]').val().trim();
            var author = parentLi.find('[name="author"]').val().trim();
            var authorUri = parentLi.find('[name="author_uri"]').val().trim();
            var textDomain = parentLi.find('[name="text_domain"]').val().trim();


            if (!themeName) {
                showAlert("❌ Theme Name is required.", "danger");
                statusElement.remove(); // ✅ Hide status if validation fails
                return;
            }

            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: {
                    action: "ai_assistant_create_theme",
                    theme_name: themeName,
                    theme_uri: themeUri,
                    author: author,
                    author_uri: authorUri,
                    text_domain: textDomain
                },
                success: function (response) {
                    statusElement.remove(); // ✅ Hide status after success or failure

                    if (response.success) {
                        console.log("Server Response:", response); // 🚀 Show full response in console
                        showAlert(`✅ ${response.data}`, "success"); // 🚀 Display server message in alert
                        populateThemeDetails(); // Refresh with updated details
                        _this.find('.icon-wrapper').addClass("anim");

                        setTimeout(function () {
                            _this.find('.icon-wrapper').removeClass("anim");
                        }, 1200); // ✅ Remove animation after some time
                    } else {
                        console.log("Server Error:", response); // 🚀 Show error details
                        showAlert(`❌ ${response.data}`, "danger"); // 🚀 Display server error in alert
                    }
                },
                error: function (xhr, status, error) {
                    statusElement.remove(); // ✅ Hide status on error
                    console.log("AJAX Error Details:", xhr.responseText); // 🚀 Log detailed error
                    showAlert(`❌ AJAX error occurred: ${error}`, "danger");
                }
            });
        }
        //create template file
        else if (_this.data('action') === "create_page_and_template_file") {
            var parentLi = _this.closest('li');
            var pagedom = parentLi.find('[name="page_name"]');
            var pageName = parentLi.find('[name="page_name"]').val().trim();
            var createTemplate = parentLi.find('[name="create_page_template"]').is(':checked') ? 1 : 0;

            if (!pageName) {
                showAlert("❌ Page name is required.", "danger");
                return;
            }

            var statusElement = $('<span class="btn__status"><em>wait...</em></span>');
            _this.find('.icon-wrapper').append(statusElement);

            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: {
                    action: "ai_assistant_create_page_and_template",
                    page_name: pageName,
                    create_template: createTemplate
                },
                success: function (response) {
                    pagedom.val("");
                    statusElement.remove();
                    if (response.success) {
                        showAlert(`✅ ${response.data}`, "success");
                        _this.find('.icon-wrapper').addClass("anim");
                        setTimeout(function () {
                            _this.find('.icon-wrapper').removeClass("anim");
                        }, 1200);
                    } else {
                        showAlert(`❌ ${response.data}`, "danger");
                    }
                },
                error: function (xhr, status, error) {
                    pagedom.val("");
                    statusElement.remove();
                    console.log("AJAX Error:", xhr.responseText);
                    showAlert(`❌ AJAX error occurred: ${error}`, "danger");
                }
            });
        }
        //create menu
        if (_this.data('action') === "create_menu") {
            var parentLi = _this.closest('li');
            var menuName = parentLi.find('input[name="menu_name"]').val().trim();

            if (!menuName) {
                showAlert("❌ Menu Name is required.", "danger");
                return;
            }

            var statusElement = $('<span class="btn__status"><em>wait...</em></span>');
            _this.find('.icon-wrapper').append(statusElement);

            $.ajax({
                url: ajax_object.ajax_url,
                type: "POST",
                data: {
                    action: "ai_assistant_create_menu",
                    menu_name: menuName
                },
                success: function (response) {
                    if (response.success) {
                        statusElement.remove();
                        _this.find('.icon-wrapper').addClass("anim");
                        showAlert(`✅ Menu '${menuName}' created successfully! Redirecting...`, "success");
                        setTimeout(function () {
                            window.location.href = `/wp-admin/nav-menus.php?action=edit&menu=${response.data.menu_id}`;
                        }, 1500); // Redirect after 1.5 seconds
                    } else {
                        showAlert(`❌ ${response.data}`, "danger");
                    }
                },
                error: function () {
                    showAlert("❌ AJAX error occurred while creating menu.", "danger");
                }
            });
        }


    });

});























