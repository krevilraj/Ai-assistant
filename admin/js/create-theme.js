jQuery(function ($) {
    const $form = $("#ai-theme-generator-form");
    const $step = $("#ai_tg_step");

    // ---------------------------------
    // Helpers
    // ---------------------------------
    const isNewThemeMode = () =>
        $('input[name="ai_tg_target_mode"]:checked').val() === "new";

    const slugify = (str) =>
        (str || "")
            .toString()
            .trim()
            .toLowerCase()
            .replace(/['"]/g, "")
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/-+/g, "-")
            .replace(/^-|-$/g, "");

    const showError = ($field, msg) => {
        $field.addClass("ai-field-error");

        const $wrap = $field.closest(".ai-field");
        let $m = $wrap.find(".ai-field-error-msg");
        if (!$m.length) {
            $m = $('<div class="ai-field-error-msg"></div>').appendTo($wrap);
        }
        $m.text(msg).show();
    };

    const clearError = ($field) => {
        $field.removeClass("ai-field-error");
        $field.closest(".ai-field").find(".ai-field-error-msg").hide().text("");
    };

    const clearAllErrors = () => {
        $(".ai-field-error").removeClass("ai-field-error");
        $(".ai-field-error-msg").hide().text("");
    };

    // ---------------------------------
    // Toggle New Theme UI
    // ---------------------------------
    const toggleNewThemeFields = () => {
        $(".ai-tg-new-theme-options").toggle(isNewThemeMode());
        clearAllErrors();
    };

    toggleNewThemeFields();
    $(document).on("change", 'input[name="ai_tg_target_mode"]', toggleNewThemeFields);

    // ---------------------------------
    // Auto-fill theme slug + text-domain (until user edits)
    // ---------------------------------
    const $themeName = $("#ai_tg_theme_name");
    const $themeSlug = $("#ai_tg_theme_slug");
    const $textDomain = $("#ai_tg_text_domain");

    $themeSlug.data("touched", false);
    $textDomain.data("touched", false);

    $themeSlug.on("input", () => $themeSlug.data("touched", true));
    $textDomain.on("input", () => $textDomain.data("touched", true));

    $themeName.on("input", function () {
        const s = slugify($(this).val());
        if (!$themeSlug.data("touched")) $themeSlug.val(s);
        if (!$textDomain.data("touched")) $textDomain.val(s);
    });

    // ---------------------------------
    // Table: Auto-update slug from title (until user edits slug)
    // ---------------------------------
    $("#ai-tg-pages-table").on("input", ".ai-page-slug", function () {
        $(this).data("touched", true);
    });

    $("#ai-tg-pages-table").on("input", ".ai-page-title", function () {
        const $row = $(this).closest(".ai-page-row");
        const $slug = $row.find(".ai-page-slug");
        if ($slug.data("touched")) return;
        $slug.val(slugify($(this).val()));
    });

    // ---------------------------------
    // Buttons set step
    // ---------------------------------
    $(document).on("click", "#ai_tg_btn_scan", function () {
        $step.val("1");
    });

    $(document).on("click", "#ai_tg_btn_create_pages", function () {
        $step.val("2");
    });

    // ---------------------------------
    // Validation
    // Only validate ZIP on Step 1
    // ---------------------------------
    const validateZip = () => {
        const $zip = $("#ai_tg_zip");
        const files = $zip[0]?.files || [];

        if (!files.length) {
            showError($zip, "Please select a ZIP file.");
            return false;
        }

        const file = files[0];
        const ok = (file.name || "").toLowerCase().endsWith(".zip");
        if (!ok) {
            showError($zip, "Only .zip files are allowed.");
            return false;
        }

        clearError($zip);
        return true;
    };

    const validateNewTheme = () => {
        let ok = true;

        const nameVal = ($themeName.val() || "").trim();
        const domainVal = ($textDomain.val() || "").trim();
        const slugVal = ($themeSlug.val() || "").trim();

        if (!nameVal) {
            showError($themeName, "Theme name is required.");
            ok = false;
        } else {
            clearError($themeName);
        }

        if (!domainVal) {
            showError($textDomain, "Text domain is required.");
            ok = false;
        } else if (!/^[a-z0-9-]+$/.test(domainVal)) {
            showError($textDomain, "Use lowercase letters, numbers, and hyphens only.");
            ok = false;
        } else {
            clearError($textDomain);
        }

        if (slugVal && !/^[a-z0-9-]+$/.test(slugVal)) {
            showError($themeSlug, "Use lowercase letters, numbers, and hyphens only.");
            ok = false;
        } else {
            clearError($themeSlug);
        }

        return ok;
    };

    $form.on("submit", function (e) {
        clearAllErrors();

        const currentStep = ($step.val() || "1").toString();
        let ok = true;

        // Step 1 needs zip (and new theme fields if new theme selected)
        if (currentStep === "1") {
            ok = validateZip();
            if (isNewThemeMode()) ok = validateNewTheme() && ok;
        }

        // Step 2 does NOT need zip validation
        if (!ok) {
            e.preventDefault();

            const $first = $(".ai-field-error").first();
            if ($first.length) {
                $("html, body").animate({ scrollTop: $first.offset().top - 120 }, 200);
                $first.trigger("focus");
            }
        }
    });

    // clear error on typing
    $(document).on("input change", ".ai-field :input", function () {
        clearError($(this));
    });
});
