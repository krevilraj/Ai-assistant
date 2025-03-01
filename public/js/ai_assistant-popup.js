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

        var locationData = [[{param: selectedParam, operator: "==", value: selectedValue}]];

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


    let selectedTextBeforeClick = "";
    let selectionStartIndex = 0;
    let selectionEndIndex = 0;

// 🌟 Track the text selection inside the textarea
//     document.getElementById('menu-editor').addEventListener('mouseup', function () {
//         selectionStartIndex = this.selectionStart;
//         selectionEndIndex = this.selectionEnd;
//         selectedTextBeforeClick = this.value.substring(selectionStartIndex, selectionEndIndex);
//         console.log("🔍 Selected text:", selectedTextBeforeClick);
//     });

// 🌟 Replace the selected text after AJAX response
    function replaceSelectedTextInTextarea(newText) {
        const textarea = document.getElementById('menu-editor');
        const currentContent = textarea.value;

        const updatedContent = currentContent.substring(0, selectionStartIndex) +
            newText +
            currentContent.substring(selectionEndIndex);

        textarea.value = updatedContent;
        textarea.focus();
        console.log("✅ Text replaced successfully!");
    }

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
    function handleCorrectMenu(_this, $) {
        const parentLi = _this.closest('li');
        const menuName = parentLi.find('[name="menu__name"]').val().trim();
        const editorContent = $("#menu-editor").val().trim();

        if (!menuName) return showAlert("❌ Please select a menu.", "danger");
        if (!selectedTextBeforeClick) return showAlert("❌ Please select the menu HTML in the textarea before clicking.", "danger");

        sendAjax({
            action: "ai_assistant_correct_menu",
            menu_name: menuName,
            menu_html: selectedTextBeforeClick
        }, _this, function (response) {
            replaceSelectedTextInTextarea(response.data.menu_code);
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

    function handleDeleteUserType(_this,$){
        const parentLi = _this.closest('li');
        const userType = parentLi.find('input[name="remove_user_type"]:text').val().trim();
        sendAjax({action: "ai_assistant_delete_user_role", role: userType}, _this);
    }


});























