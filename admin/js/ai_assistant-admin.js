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
