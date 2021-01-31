
var GB2EPub = {
	counter: 0,
	running: false,

	doIT: function(){
		if(GB2EPub.running){
			alert('Bitte warten Sie, bis der Vorgang abgeschlossen ist.');
			return;
		}


		multiCMS.callHander('CCGB2EPub', 'convert', 'spiegelURL='+encodeURIComponent($('spiegelURL').value), function(transport){
			if(!multiCMS.checkResponse(transport)) return;

			var div = Builder.node('div', {'style': 'clear:both;margin-top:13px;display:none;', 'id': 'resultContainer'+GB2EPub.counter});

			$('ePubDLs').appendChild(div);

			$('resultContainer'+GB2EPub.counter).update(transport.responseText);

			new Effect.Appear(div);

			if($('beta').style.display == 'none') new Effect.Appear('beta');
			+GB2EPub.counter++;
		});
	},

	hideReport: function(){
		new Effect.Fade('reportWindow', {duration: 0.3});
		new Effect.Appear('ePub', {duration: 0.3});
	},

	showReport: function(title, author){
		$('reportEPub').title.value = title;
		$('reportEPub').author.value = author;
		new Effect.Appear('reportWindow', {duration: 0.3});
		new Effect.Fade('ePub', {duration: 0.3});
		window.setTimeout('$(\'reportEPub\').description.focus();', 200);
	},

	showQueueInfo: function(){
		new Effect.Appear('queueInfoWindow', {duration: 0.3});
		new Effect.Fade('ePub', {duration: 0.3});
	},

	hideQueueInfo: function(){
		new Effect.Fade('queueInfoWindow', {duration: 0.3});
		new Effect.Appear('ePub', {duration: 0.3});
	}
}