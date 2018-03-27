function globalNav(){
	//IE fix for rollovers
	$('#global-nav a').live('focusin mouseenter',function(e){
		$(this).addClass('hover');
	});
	$('#global-nav a').live('focusout mouseleave',function(e){
		$(this).removeClass('hover');
	});
}

function getUrlVars(){
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	for(var i = 0; i < hashes.length; i++) {
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = hash[1];
	}
	return vars;
}

function isPrintFriendly(){
	var printFriendly = getUrlVars()["p"];
	if(parseInt(printFriendly) == 1)
		return printFriendly;
}

function globalUtils(){
	var printText = '';
	var contrastText = '';
	var toggleSmallText = '';
	var toggleResetText = '';
	var toggleLargeText = '';
	
	if($('body').hasClass('en')){
		printText = 'Print';
		contrastText = 'Contrast';
		toggleSmallText = 'Decrease font size ';
		toggleResetText = 'Reset font size ';
		toggleLargeText = 'Increase font size';
	}
	if($('body').hasClass('fr')){
		printText = 'Imprimer';
		contrastText = 'Contraste';
		toggleSmallText = 'Diminuer la taille de la police ';
		toggleResetText = 'Taille de police normale ';
		toggleLargeText = 'Augmenter la taille de la police';
	}
	$('ul#util').append('<li id="print"><a href="#" title="'+printText+'">'+printText+'</a></li><li id="contrast"><a href="#" title="'+contrastText+'">'+contrastText+'</a></li><li id="toggleFont" class="clearfix"><a id="small" href="#" title="'+toggleSmallText+'"><img src="/images/btn-toggle-font-small.gif" alt="'+toggleSmallText+'" width="11" height="16" /></a><a id="medium" href="#" title="'+toggleResetText+'"><img src="/images/btn-toggle-font-med.gif" alt="'+toggleResetText+'" width="14" height="16" /></a><a id="large" href="#" title="'+toggleLargeText+'"><img src="/images/btn-toggle-font-large.gif" alt="'+toggleLargeText+'" width="16" height="16" /></a></li>');
}

function setFontCookie(s){
	var c = 0;
	if($.cookie('size').length){
		c = parseInt($.cookie('size'));
//		cookie exists
		if(c+s >=1 || c+s <=6){
			$.cookie('size',c+s,{expires:200,path:'/'});
			if($.cookie('size') == 1)
				$('body').removeClass('small large xlarge xxlarge').addClass('xsmall');
			if($.cookie('size') == 2)
				$('body').removeClass('xsmall large xlarge xxlarge').addClass('small');
			if($.cookie('size') == 3)
				$('body').removeClass('xsmall small large xlarge xxlarge');
			if($.cookie('size') == 4)
				$('body').removeClass('xsmall small xlarge xxlarge').addClass('large');
			if($.cookie('size') == 5)
				$('body').removeClass('xsmall small large xxlarge').addClass('xlarge');
			if($.cookie('size') == 6)
				$('body').removeClass('xsmall small large xlarge').addClass('xxlarge');
		}
//		cookie changed
	}
	else{
		c = 3;
//		no cookie exists, setting cookie
		if(c+s >= 1 && c+s <= 6){
			$.cookie('size',c+s,{expires:200,path:'/'});
			if($.cookie('size') == 1)
				$('body').removeClass('small large xlarge xxlarge').addClass('xsmall');
			if($.cookie('size') == 2)
				$('body').removeClass('xsmall large xlarge xxlarge').addClass('small');
			if($.cookie('size') == 3)
				$('body').removeClass('xsmall small large xlarge xxlarge');
			if($.cookie('size') == 4)
				$('body').removeClass('xsmall small xlarge xxlarge').addClass('large');
			if($.cookie('size') == 5)
				$('body').removeClass('xsmall small large xxlarge').addClass('xlarge');
			if($.cookie('size') == 6)
				$('body').removeClass('xsmall small large xlarge').addClass('xxlarge');
		}
		else 
//			cookie changed to default
			$.cookie('size',c,{expires:200,path:'/'});
	}
}

function toggleFontsize(){
	$.cookie('size');
	var section = 'body';
	// Reset Font Size
	$("#toggleFont a#medium").live('click',function(e){
		e.preventDefault();
		$.cookie('size',3,{expires:200,path:'/'});
		$(section).removeClass('xsmall small large xlarge xxlarge');
	});
	// Decrease Font Size
	$("#toggleFont a#small").live('click',function(e){
		e.preventDefault();
		if(!($('body').hasClass('xsmall'))){
			setFontCookie(-1);
		}
	});
	// Increase Font Size
	$("#toggleFont a#large").live('click',function(e){
		e.preventDefault();
		if(!($('body').hasClass('xxlarge'))){
			setFontCookie(1);
		}
	});
}

function setHighContrastInlineImages(){
	$('body.en #footer h2 img').each(function(){
		$(this).attr('src',function(index,attr){
			return attr.replace('images/en','images/en/contrast');
		});
	});
	$('body.fr #footer h2 img').each(function(){
		$(this).attr('src',function(index,attr){
			return attr.replace('images/fr','images/fr/contrast');
		});
	});
	$('body #toggleFont img, body img.inline_image').each(function(){
		$(this).attr('src',function(index,attr){
			return attr.replace('images','images/contrast');
		});
	});
}

function setDefaultContrastInlineImages(){
	$('body.en #footer h2 img').each(function(){
		$(this).attr('src',function(index,attr){
			return attr.replace('images/en/contrast','images/en');
		});
	});
	$('body.fr #footer h2 img').each(function(){
		$(this).attr('src',function(index,attr){
			return attr.replace('images/fr/contrast','images/fr');
		});
	});
	$('body #toggleFont img, body img.inline_image').each(function(){
		$(this).attr('src',function(index,attr){
			return attr.replace('images/contrast','images');
		});
	});
}


function setContrastCookie(){
	if($.cookie('contrast').length){
		if($.cookie('contrast') == 1){
			$.cookie('contrast',0,{expires:200,path:'/'});
			setDefaultContrastInlineImages();
			$('body').removeClass('highContrast');
		}
		else{
			$.cookie('contrast',1,{expires:200,path:'/'});
			setHighContrastInlineImages();
			$('body').addClass('highContrast');
		}
	}
	else{
		$.cookie('contrast',1,{expires:200,path:'/'});
		setHighContrastInlineImages();
		$('body').addClass('highContrast');
	}
}

function toggleContrast(){
	$.cookie('contrast');
	var section = 'body';
	if($.cookie('contrast').length){
		if($.cookie('contrast') == 1){
			setHighContrastInlineImages();
			$(section).addClass('highContrast');
		}
		else{
			setDefaultContrastInlineImages();
			$(section).removeClass('highContrast');
		}
	}
	$("#contrast a").live('click',function(e){
		e.preventDefault();
		setContrastCookie();
	});
}

function accordionToggle(){
	if(!(isPrintFriendly())){
		var show = ' Read More';
		var hide = '';
		var closeBtn = 'close';
		var lang = (top.location.href.toLowerCase().indexOf("/fr/")>-1)? "fr":"en";
		if(lang == 'fr'){
			show = 'Pour en savoir plus';
			hide = '';
			closeBtn = 'Fermer';
		}
		var accHead = $('div.accordion div.heading');
		var accContent = $('div.accordion div.content');
		accHead.each(function(index){
			$(this).find('h3').wrapInner('<a class="toggle clearfix" href="#accordion'+index+'" title="'+$(this).find('h3').text()+'"><span class="title"></span></a>');
			$(this).not('.active').find('h3 a').append('<span class="button">'+show+'</span>');
			$(this).not('active').next().css('display','none');
			if($(this).hasClass('active')){
				$(this).find('h3 a').append('<span class="button">'+hide+'</span>');
			}
		});
		accContent.each(function(index){
			$(this).wrapInner('<div class="wrapper"/>');
		});
		
		accHead.find('a').live('click',function(e){
			e.preventDefault();
			var activeAccHead = $(this).parents('div.heading');
			if(activeAccHead.hasClass('active')){
				activeAccHead.find('span.button').removeClass('active').text(show);
				activeAccHead.removeClass('active');
				$(this).focus();
				activeAccHead.next('.visibleContent').remove();
			}
			else{
				activeAccHead.after('<div class="visibleContent"></div>');
				activeAccHead.find('span.button').addClass('active').text(hide);
				activeAccHead.next().next('.content').find('.wrapper').clone().appendTo(activeAccHead.next());
				activeAccHead.next('.visibleContent').attr('tabindex','-1');
				activeAccHead.next('.visibleContent').append('<p class="close"><a href="#" title="'+closeBtn+'">'+closeBtn+'</a></p>');
				activeAccHead.next('.visibleContent').focus();
				activeAccHead.addClass('active');
			}
		});
		
		//inline close button
		$('.visibleContent').find('p.close a').live('click',function(e){
			e.preventDefault();
			var activeAccHead = $(this).parents('div.visibleContent').prev('div.heading');
			
			if(activeAccHead.hasClass('active')){
				activeAccHead.find('span.button').removeClass('active').text(show);
				activeAccHead.removeClass('active');
				activeAccHead.nextAll('div.heading').first().find('h3 a').focus();
				activeAccHead.next('.visibleContent').remove();
			}
		});
	}
}

function printFriendly(){
	$('li#print a').live('click',function(e){
		$(this).attr('href',window.location.href+'?p=1');
		$(this).attr('target','_blank');
	});
	
	$('ul#util.district_vote li#print a').live('click',function(e){
		$(this).attr('href',window.location.href+'&p=1');
		$(this).attr('target','_blank');
		});
		
	if(isPrintFriendly()){
		$('link[media=screen]').remove();
		$('link[media=print]').attr('media','all');
	}
}

function calendar(){
	$('#calendar').text($('#countdown').text());
}

$(function(){
	globalNav();
	globalUtils();
	calendar();
	setFontCookie(0);
	toggleContrast();
	toggleFontsize();
	accordionToggle();
	printFriendly();
});
