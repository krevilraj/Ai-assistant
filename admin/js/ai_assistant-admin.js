(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
document.addEventListener('DOMContentLoaded', function () {
	const tabs = document.querySelectorAll('.ai_assistant-settings-page .nav-tab');
	const tabContents = document.querySelectorAll('.ai_assistant-settings-page .tab-content');

	tabs.forEach(tab => {
		tab.addEventListener('click', function (e) {
			e.preventDefault();

			// Remove active class from all tabs and tab contents
			tabs.forEach(t => t.classList.remove('nav-tab-active'));
			tabContents.forEach(tc => tc.classList.remove('active'));

			// Add active class to the clicked tab and its content
			this.classList.add('nav-tab-active');
			const target = document.querySelector(this.getAttribute('href'));
			target.classList.add('active');
		});
	});
});



jQuery(document).ready(function ($) {
	// ✅ Toggle folders based on 'open' class
	$(document).on('click', '.folder', function (e) {
		e.stopPropagation();
		const nested = $(this).children('.nested');
		if ($(this).hasClass('open')) {
			// 🔒 If open, slide up and remove 'open' class
			nested.slideUp('fast');
			$(this).removeClass('open');
		} else {
			// 🔓 If not open, slide down and add 'open' class
			nested.slideDown('fast');
			$(this).addClass('open');
		}
	});

	// ✅ On page load, expand active file's parents
	const activeFile = $('.active-file');
	activeFile.parents('.nested').show();
	activeFile.parents('.folder').addClass('open');


	$('.postbox-header').on('click', function () {
		const $postbox = $(this).closest('.postbox');

		if ($postbox.hasClass('closed')) {
			// If 'closed' class exists, remove it and slide down
			$postbox.removeClass('closed');
			$postbox.find('.inside').slideDown('fast');
			$(this).find('.dashicons')
				.removeClass('dashicons-arrow-down')
				.addClass('dashicons-arrow-up');
		} else {
			// If 'closed' class does not exist, add it and slide up
			$postbox.addClass('closed');
			$postbox.find('.inside').slideUp('fast');
			$(this).find('.dashicons')
				.removeClass('dashicons-arrow-up')
				.addClass('dashicons-arrow-down');
		}
	});

});

jQuery(document).ready(function ($) {
	// ✅ Open Modal
	$('#open-dashicon-picker').on('click', function () {
		$('#dashicon-picker-modal').fadeIn('fast');
	});

	// ✅ Close Modal
	$('#close-dashicon-picker, #dashicon-picker-overlay').on('click', function () {
		$('#dashicon-picker-modal').fadeOut('fast');
	});

	// ✅ Handle Icon Selection
	$(document).on('click', '.dashicon-picker-list li', function () {
		const selectedIcon = $(this).data('icon');
		$('#dashi_icon_field').val(`dashicons-${selectedIcon}`); // Paste icon slug into text field
		$('#dashicon-picker-modal').fadeOut('fast');
	});

	// delete files and folders from theme editor
	$(document).on('click', '.delete-item', function (e) {
		e.preventDefault();
		const filePath = $(this).data('path');
		const confirmDelete = confirm(`Are you sure you want to delete "${filePath}"?`);

		if (confirmDelete) {
			$.ajax({
				url: ajax_object.ajax_url,
				type: "POST",
				data: {
					action: "ai_assistant_delete_file",
					file_path: filePath
				},
				success: function (response) {
					if (response.success) {
						showAlert(`✅ ${response.data}`, "success");
						location.reload(); // Refresh after deletion
					} else {
						showAlert(`❌ ${response.data}`, "danger");
					}
				},
				error: function () {
					showAlert("❌ Error occurred while deleting the file/folder.", "danger");
				}
			});
		}
	});
});


jQuery(document).ready(function ($) {
	let originalContent = $("#theme-file-editor").val().trim();

	// Detect changes in the textarea
	$("#theme-file-editor").on("input", function () {
		let currentContent = $(this).val().trim();
		if (currentContent !== originalContent) {
			$("#file_save").removeClass("button-disabled");
		} else {
			$("#file_save").addClass("button-disabled");
		}
	});

	$("#file_save").on("click", function () {
		var filePath = new URLSearchParams(window.location.search).get("file");
		var fileContent = $("#theme-file-editor").val().trim();
		var _this = $(this);
		if (!filePath) {
			showAlert("❌ No file selected!", "danger");
			return;
		}


		_this.text("Saving...");

		$.ajax({
			url: ajax_object.ajax_url,
			type: "POST",
			data: {
				action: "ai_assistant_save_file",
				file_path: filePath,
				file_content: fileContent
			},
			success: function (response) {
				if (response.success) {
					showAlert("✅ File saved successfully!", "success");
					_this.text("Saved");

				} else {
					showAlert("❌ Failed to save file: " + response.data, "danger");
					_this.text("Retry");
				}
			},
			error: function () {
				_this.text("Retry");
				showAlert("❌ AJAX error occurred while saving the file.", "danger");
			}
		});
	});
});








