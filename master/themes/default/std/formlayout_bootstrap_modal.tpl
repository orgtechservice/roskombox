<form action="{escape(form.url)}" method="post" enctype="multipart/form-data" class="form-horizontal">
	{ if form.title }
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">{ escape(form.title) }</h4>
		</div>
	{ endif }

	<div class="modal-body">
		
		{if count(form.errors) > 0}
			<div class="alert alert-danger" role="alert">
				<ul style="padding: 0px;">
					{ foreach form.errors as error }
						<li style="list-style-type: none;">{error}</li>
					{ endeach }
				</ul>
			</div>
		{endif}

		{ foreach form.params as param }
			{ if param.widget = "widgets/hidden" }
				<input type="hidden" name="{param.name}" value="{param.value}" />
			{ else }
				{ include param.widget }
			{endif}
		{ endeach }

		<input type="hidden" name="action" value="{form.action}" />



	</div>
	<div class="modal-footer">
		{foreach form.submits as submit}
			<button type="submit" name="{ submit.name }" class="btn { if submit.name = 'full' } btn-success{ else }btn-primary{ endif }">{ escape(submit.title) }</button>
		{endeach}
	</div>
</form>
