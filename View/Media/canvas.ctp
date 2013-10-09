<script>
	var $j = $;
</script>

<div class="row">
	<div class="span8">
		<canvas id="canvas" width="420" height="594">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</canvas>
	</div>
	<div class="span4">
		<div>
		
			<div id="backgroundControls" style="background: #fff; padding: 15px;">
				<label>
					Background color: 
					<select name="bgColorpicker">
						<option value="#000000">Black</option>
						<option value="#e1e1e1">Gray</option>
						<option value="#ffffff">White</option>
						<option value="#5484ed">Bold blue</option>
						<option value="#a4bdfc">Blue</option>
						<option value="#46d6db">Turquoise</option>
						<option value="#7ae7bf">Seafoam</option>
						<option value="#7bd148">Green</option>
						<option value="#51b749">Bold green</option>
						<option value="#fbd75b">Yellow</option>
						<option value="#ffb878">Orange</option>
						<option value="#ff887c">Red</option>
						<option value="#dc2127">Bold red</option>
						<option value="#663399">Royal Purple</option>
						<option value="#dbadff">Light Purple</option>
						<option value="#ff0080">Hot Pink</option>
					</select>
				</label>
			</div>
			
			<div>
				<?php echo $this->Element('Media.media_selector') ?>
			</div>

			<div class="btn" id="saveCanvas" data-saved="false">Save progress</div>
			
		</div>
	</div>
</div>


<link rel="stylesheet" type="text/css" href="/css/google-webfonts.css" />
<script type="text/javascript" src="/js/simplecolorpicker/simplecolorpicker.js"></script>
<link rel="stylesheet" type="text/css" href="/css/simplecolorpicker/simplecolorpicker.css" />

<script type="text/javascript" src="/js/underscore/underscore-1.5.1.js"></script>
<script type="text/javascript" src="/js/backbone/backbone-1.0.0.js"></script>

<script type="text/javascript" src="/media/js/canvasBuildrr/models/TextObject.js"></script>
<script type="text/javascript" src="/media/js/canvasBuildrr/models/ImageObject.js"></script>
<script type="text/javascript" src="/media/js/canvasBuildrr/canvasBuildrr.js"></script>
<script type="text/javascript" src="/media/js/canvasBuildrr/views/MainOverlay.js"></script>
<script type="text/javascript" src="/media/js/canvasBuildrr/views/TextEdit.js"></script>
<script type="text/javascript" src="/media/js/canvasBuildrr/views/ImageEdit.js"></script>

<link rel="stylesheet" type="text/css" href="/media/css/canvasBuildrr.css" />

<script type="text/javascript">
		var toggleList = $("#subject"),
			list = $(".subject ul"),
			item = list.find("li"),
			subject = $("#subject");

		toggleList.on("click", function(){
			list.fadeToggle();
		});

		item.on("click", function(){
			var itemVal = $(this).find(".val").text();
			subject.val(itemVal);
			list.fadeOut();
			item.removeClass("is-active");
			$(this).addClass("is-active");
		});

	$j('select[name="bgColorpicker"]').simplecolorpicker({picker: true});
</script>

<?php if (!(empty($this->request->data))) : ?>
<script>
//console.log(AppModel);
//console.log(AppModel.get('collection'));

AppModel.get('collection').reset();
AppModel.reload(<?php echo json_encode($this->request->data['Media']['data']); ?>);
</script>
<?php endif; ?>
