<div class="challenge-top-bar">
	<a href="https://formidableforms.com/developer-applicant-challenge/" class="frm-header-logo">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 599.68 601.37" width="35" height="35">
			<path fill="#f05a24" d="M289.6 384h140v76h-140z"></path>
			<path fill="#4d4d4d" d="M400.2 147h-200c-17 0-30.6 12.2-30.6 29.3V218h260v-71zM397.9 264H169.6v196h75V340H398a32.2 32.2 0 0 0 30.1-21.4 24.3 24.3 0 0 0 1.7-8.7V264zM299.8 601.4A300.3 300.3 0 0 1 0 300.7a299.8 299.8 0 1 1 511.9 212.6 297.4 297.4 0 0 1-212 88zm0-563A262 262 0 0 0 38.3 300.7a261.6 261.6 0 1 0 446.5-185.5 259.5 259.5 0 0 0-185-76.8z"></path>
		</svg>
	</a>
	<h1>Applicant Challenge</h1>
</div>
<div class="challenge-wrapper">
	@if ($message)
		<div class="alert">{{ $message }}</div>
	@endif
	<h2>General Settings</h2>
	<section>
		<h3>Data</h3>
		<table class="widefat fixed striped">
			<thead>
				<tr>
				@foreach ($data->data['headers'] as $header)
					<td>{{ $header }}</td>
				@endforeach
				</tr>
			</thead>
			<tbody>
				@foreach ($data->data['rows'] as $row)
					<tr>
					@foreach ($row as $field)
						<td>{{ $field}}</td>
					@endforeach
					</tr>
				@endforeach
			</tbody>
		</table>
	</section>
	<section>
		<h3>Settings</h3>
		<p>Click below to refresh the challenge data.</p>
		<p><a href="/wp-admin/options-general.php?page=challenge&action=refresh" class="button">Refresh</a>
	</section>
</div>