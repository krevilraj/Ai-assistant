jQuery(document).ready(function ($) {
    var editor = $("#theme-file-editor");

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
        const handler = snippetHandlers[command];

        if (typeof handler === "function") {
            handler(); // 🚀 Call the function dynamically
        } else {
            console.warn(`🚨 No handler found for command: ${command}`);
        }
    });


});
