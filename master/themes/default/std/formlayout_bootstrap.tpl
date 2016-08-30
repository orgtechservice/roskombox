<form action="{escape(form.url)}" method="post" enctype="multipart/form-data" class="form-horizontal">
	{if form.title}<h3>{form.title}</h3>{endif}

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

	<div class="form-group">
		<div class="col-sm-12" style="text-align: center;">
			{foreach form.submits as submit}
				<button type="submit" name="{ submit.name }" class="btn { if submit.name = 'full' } btn-info{ else }btn-primary{ endif }">{ escape(submit.title) }</button>
			{endeach}
		</div>
	</div>
</form>
