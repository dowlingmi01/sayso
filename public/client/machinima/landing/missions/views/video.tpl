<div class="mission-video">
	<div class="mission-trailer">
		<h3>{{{title}}}</h3>
		<div class="mission-video-container"><div id="mission-video-container"></div></div>
		<p class="mission-video-caption">{{{caption}}}</p>
	</div>
	<div class="mission-poll mission-video-poll">
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
</div>