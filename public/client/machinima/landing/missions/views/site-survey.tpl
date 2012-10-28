<div class="mission-visit">
	<iframe src="{{{url}}}"></iframe>
	<div id="mission-slide-layout">
		<div id="mission-slide">
			<div id="mission-slide-status"></div>
			<div id="mission-slide-activator"></div>
			<div id="mission-slide-content">
				<div class="mission-slide-sets">
					<div class="mission-slide-instructions">
						<h3 class="attention">Pop Quiz!</h3>
						<p>Check out {{{url}}} and answer the following questions</p>
						<p class="mission-slide-start">Question 1<i></i></p>
					</div>
					{{#questions}}{{#question}}
					<div class="mission-slide-set" data-id="{{id}}">
						<h4>{{{text}}}</h4>{{/question}}
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
					</div>
					{{/questions}}
				</div>
			</div>
		</div>
	</div>	
	<div class="mission-next-button"></div>	
</div>