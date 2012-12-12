<div class="mission-poll">
	<h3>{{#question}} {{{text}}} {{/question}}</h3>
	<ul>
		{{#answers}}
		<li>
			<label>
				<input type="radio" name="mission-poll-answer" value="{{id}}" />
				{{{text}}}
			</label>
		</li>
		{{/answers}}
	</ul>
	<div class="mission-next-button"></div>
</div>