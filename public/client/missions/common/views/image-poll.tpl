<div class="mission-image-poll">
	<h3>{{#question}} {{{text}}} {{/question}}</h3>
	<div class="mission-image-poll-answers">
		<ul>
			{{#answers}}
			<li>
				<label>
					<p><img src="{{{image}}}" /></p>
					<p><input type="radio" name="mission-poll-answer" value="{{id}}" /></p>
					<p>{{{text}}}</p>
				</label>
			</li>
			{{/answers}}
		</ul>
	</div><!-- /mission-image-poll-answers -->
</div>