<div class="mission-video">
	<div class="mission-trailer">
		<h3>{{{title}}}</h3>
		<div id="mission-video-container"></div>
	</div>
	<div class="mission-image-poll mission-video-poll">
		<h3>{{#question}} {{{text}}} {{/question}}</h3>
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
	</div>
</div>