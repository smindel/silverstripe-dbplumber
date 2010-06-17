var timeout;

// show ajaxy status text on the bottom of right
function msgbx(text,status) {
	var $ = jQuery;
	$('#ajax_msg').text(text);
	if(status == 'off') {
		$('#ajax_msg').hide('slow');
	} else {
		$('#ajax_msg').removeClass('good');
		$('#ajax_msg').removeClass('bad');
		$('#ajax_msg').removeClass('waiting');
 		$('#ajax_msg').addClass(status);
 		$('#ajax_msg').show('fast');
 	}
	if(status == 'good' || status == 'bad')
		timeout = setTimeout('msgbx("' + text + '", "off")', 3000);
}

(function($) {

	var kikeoptions = { dragOpacity: 0 }

	function initRight() {
		$('.tabtabs').each(function(){
			$(this).attr('href', '#' + $(this).attr('title'));
		});
		$("#tabs").livequery(function(){$(this).tabs()});
		$("#right table.kike").livequery(function(){$(this).kiketable_colsizable(kikeoptions)});

		$("#tabs").tabs();
		$("#right table.kike").kiketable_colsizable(kikeoptions);
		setSizes();
	}

	// set the proper sizes for tabs and table list
	function setSizes() {
		$('#dbb_table_list').height($('#left').innerHeight() - $('#lefthead').height());
		$('#tabs').height($('#right').height() - $('#right .main > h1').height() - parseInt($('#right .main > h1').css('marginTop')) - 26);
		$('.tabbody').height($('#tabs').height() - $('ul.ui-tabs-nav').height() - 23);
		$('.tabbody').width($('#tabs').width() - 26);
	}

	$(document).ready(function() {

		// select a table on the left
		$('#dbb_table_list li').live('click', function() {
			$('#dbb_table_list li').removeClass('selected');
			$(this).addClass('selected');
			msgbx('loading table ' + $(this).text(), 'waiting');
			$('#right div.main').load($('a', this).attr('href'), function(){
				msgbx('loaded', 'good');
				initRight();
			});
			return false;
		});

		// select the database on the left
		$('#lefthead').live('click', function() {
			$('#dbb_table_list li').removeClass('selected');
			msgbx('loading database ' + $(this).text(), 'waiting');
			$('#right div.main').load($('a', this).attr('href'), function(){
				msgbx('loaded', 'good');
				initRight();
			});
			return false;
		});

		// submit a custom query on the right
		$('#sql_form').live('submit', function() {
			msgbx('execute statement', 'waiting');
			$('#sql-tab').load($(this).attr('action'), $(this).serialize(),function(){
				$("#right table.kike").kiketable_colsizable(kikeoptions);
				msgbx('executed', 'good');
			});
			return false;
		});
		
		// paginate through the records of a table
		$('#browse-tab .pagination').live('click',function(){
			msgbx('loading...', 'waiting');
			$('#browse-tab').load($('a',this).attr('href'), function(){
				$("#right table.kike").kiketable_colsizable(kikeoptions);
				msgbx('loaded', 'good');
			});
			return false;
		});

		// order the records of a table by the selected field
		$('#browse-tab .fieldname a').live('click',function(){
			msgbx('loading...', 'waiting');
			$('#browse-tab').load($(this).attr('href'), function(){
				$("#right table.kike").kiketable_colsizable(kikeoptions);
				msgbx('loaded', 'good');
			});
			return false;
		});

		// select a record
		$('#browse-tab tbody tr').live('click',function(){
			$(this).toggleClass('selected');
			$('#browse-tab .delete-records').attr("disabled", true);
			$('#browse-tab tbody tr.selected').each(function(){
				$('#browse-tab .delete-records').removeAttr("disabled");
			});
		});

		// delete a selected record
		$('#browse-tab .delete-records').live('click',function(){
			ids = new Array(); $('#browse-tab tbody tr.selected').each(function(){ ids.push($(this).attr('id'));	});
			msgbx('deleting...', 'waiting');
			
			
			$.post($('a',this).attr('href'), {delete: ids}, function(data, textStatus, XMLHttpRequest){
				msgbx(data.msg, data.status);
				$('#browse-tab').load(data.redirect, function(){
					$("#right table.kike").kiketable_colsizable(kikeoptions);
					msgbx('loaded', 'good');
				});
			}, 'json');
			
			return false;
		});

		// load a record into the edit form
		$('#browse-tab tbody tr').live('dblclick',function(){
			msgbx('loading...', 'waiting');
			$('#right div.main').load('admin/dbplumber/show/' + $('td', this).first().attr('id') + '#form-tab', function(){
				msgbx('loaded', 'good');
				initRight();
				$("#tabs").tabs('select', 2);
			});
		});

		// keep track of window resizing ...
		$(window).bind('resize', function () { 
			setSizes();
		});

		// ... and again.
		$('#separator').bind('mouseup', function () { 
			setSizes();
		});
		
		// trigger init
		initRight();

	});

})(jQuery);