<div id='jrs-forms_admin'>
	<div class='jrs-forms_inner_wrapper'> 
		<h1><?=PLUGIN_FAUX_NAME?></h1>

		<div class='jrs-forms_filter_form'>
		
			<div class='row jrs-forms_date_range'>
				<label>Submission between </label>
				<input id='jrs-forms_submission_date_start' name='jrs-forms_submission_date_start' type='date' value='<?=date('Y')?>-01-01'>
				<label>and </label>
				<input id='jrs-forms_submission_date_end' name='jrs-forms_submission_date_end' type='date' value=''> 
			</div>

			<div class='row jrs-forms_submiss'>
				<label>Form Name: </label>
				<select id='jrs-forms_submiss' name='jrs-forms_submiss'>
					<option value=''>Select a form</option>
					<?php echo $form_name_options; ?>
				</select>
			</div>

			<div class='row center'>
				<button class='jrs-forms_run_report'>Run Report</button>
			</div>
	
		</div>
		<div id='jrs-forms_message'></div>
		<div id='jrs-forms_report_results'></div>
	</div>
</div>
	
	