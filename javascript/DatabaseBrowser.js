var timeout;

// show ajaxy status text on the bottom of right
function msgbx(text,status) {
	var $ = jQuery;
	$('#ajax_msg').text(text);
	if(status == 'off') {
		$('#ajax_msg').fadeOut('slow');
	} else {
		$('#ajax_msg').removeClass('good');
		$('#ajax_msg').removeClass('bad');
		$('#ajax_msg').removeClass('waiting');
 		$('#ajax_msg').addClass(status);
 		$('#ajax_msg').fadeIn('fast');
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

		$("#tabs").tabs({
			select: function(e,u){
				if(u.index == 3) {
					if(!confirm('This action deletes all records in this table. Do you want to proceed?')) return false;
					msgbx('empty table', 'waiting');
					$('#right div.main').load($('#empty_form').attr('action'), function(){
						msgbx('done', 'good');
						initRight();
					});
				} else if(u.index == 4) {
					if(!confirm('This action deletes this table. Do you want to proceed?')) return false;
					msgbx('drop table', 'waiting');
					$('#right div.main').load($('#drop_form').attr('action'), function(){
						msgbx('done', 'good');
						initRight();
						document.location.href = document.location.href;
					});
				}
			}
		});
		
		$("#right table.kike").kiketable_colsizable(kikeoptions);
		
		$('#importformdiv form').ajaxForm({
			target: '#sql-tab',
			success: function() { 
				$("#tabs").tabs('select', 1);
				msgbx('executed', 'good');
			},
			beforeSubmit: function() { 
				msgbx('uploading...', 'waiting');
			}
			
		});
		
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
		$('#sql_form button').livequery('click', function() {
			msgbx('execute statement', 'waiting');
			$.post(
				'admin/dbplumber/database/execute',
				$('#sql_form').serialize(),
				function(data){
					$('#sql-tab').html(data);
					$("#right table.kike").kiketable_colsizable(kikeoptions);
				 	msgbx('executed', 'good');
				}
			);
			return false;
		});

		// autoexpand sql query field
		$("textarea[class*=expand]").livequery(function(){$(this).TextAreaExpander()});

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
			var redirect = $('#url').val() + '?start=' + $('#start').val() + '&orderby=' + $('#orderby').val() + '&orderdir=' + $('#orderdir').val();
			msgbx('deleting...', 'waiting');
			
			$.post($('a',this).attr('href'), {ids: ids, redirect: redirect}, function(){
				msgbx('deleted', 'good');
				$('#browse-tab').load(redirect, function(){
					msgbx('loaded', 'good');
					$("#right table.kike").kiketable_colsizable(kikeoptions);
				});
			});
			
			return false;
		});

		// load a record into the edit form
		$('#browse-tab tbody tr').live('dblclick',function(){
			msgbx('loading...', 'waiting');
			var recid = $(this).attr('id');
			$('#form-tab').load('admin/dbplumber/record/form/' + recid, { oldid: recid }, function(){
				$("#tabs").tabs('select', 2);
				msgbx('loaded', 'good');
				initRight();
			});
		});

		// save a record 
		$('button.saverecord').live('click',function(){
			msgbx('saving...', 'waiting');
			var recid = $('#oldid').val();
			var url = $('#recordform').attr('action');
			$.post(
				url,
				$('#recordform').serialize(),
				function(data){
					msgbx('saved, reloading...', 'waiting');
					var redirect = 'admin/dbplumber/table/show/' + $('#table').val() + '?start=' + $('#start').val() + '&orderby=' + $('#orderby').val() + '&orderdir=' + $('#orderdir').val() + '&record=' + data.id;
					$('#right div.main').load(redirect , function(){
						msgbx('reloaded', 'good');
						initRight();
						$("#tabs").tabs('select', 2);
					});
				},
				'json'
			);
			return false;
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
		
		if($.browser.msie){
			msgbx('IE is currently not supported by DB Plumber', 'bad');
		}

	});

})(jQuery);