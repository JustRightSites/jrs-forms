# jrs-forms
A lightweight, open-source, WordPress plugin to create custom forms for your WP website's frontend and an administration tool to view, print and export form submission reports from the backend.

Basic (beginner) HTML & CSS coding skills are required to use this plugin at a minimum to add and edit forms. 
Intermediate PHP, JS, jQuery and AJAX may be needed to change core features.

<h3>NOTES:</h3>
<ul>
	<li><strong>"JRS Forms" is intended</strong> as a super-lightweight form creation and management plugin for WordPress.</li>
	<li><strong>There is no GUI</strong> to create or manage the forms.  Instead, each form exists as a PHP file in the plugins /forms folder.</li>
	<li><strong>Two forms are included</strong> in the forms/ folder.  Rename and edit as needed or add new forms.</li>
	<li><strong>The code includes</strong> a simple "honeypot" using an input called "company_size."  If company size is a field your form requires, change the honeypot to something else.  In this case, you'll have to find all the spots in the code that mention "honeypot" to make any other necessary changes and keep the honeypot working as intended.</li>
	<li><strong>All the code is open for editing,</strong> but some areas are marked as #### CUSTOM #### to point out handy spots in the code for changes.</li>
	<li><strong>The forms can be displayed</strong> on a page using shortcode as defined in the main file, /jrs-forms.php</li>
	<li><strong>The wp-admin menu link</strong> used to access the reports is called "JRS Forms" and is located at or near the bottom of the menu.</li>
	<li><strong>Each form's id is</strong> used to identify the collected and stored data on the backend.</li>
	<li><strong>The resulting reports can be</strong> filtered by report name (id) and submission date ranges.</li>
	<li><strong>The filtered data can be</strong> viewed, printed, or exported.  The export is a tab-delimited text file.</li>
</ul>
