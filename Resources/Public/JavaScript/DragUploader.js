define('TYPO3/CMS/FalOnlineMediaConnector/DragUploader', ['TYPO3/CMS/Backend/DragUploader', 'jquery'], function(DragUploader, $) {
	$('.t3-drag-uploader').each(function(key, DragUploaderFrame) {
		var $dragUploaderFrame = $(DragUploaderFrame);
		if ($dragUploaderFrame.data('file-irre-object')) {
			var $container = $('<div />').addClass('online-media').css({display: 'inline-block', width: '50%', marginLeft:'4px', verticalAlign:'bottom'}).insertAfter(DragUploaderFrame);
			function addOnlineMedia() {
				if ($input.val() !== '') {
					var value = $input.val();
					$input.val('');
					$.post(TYPO3.settings.ajaxUrls['FalOnlineMediaConnector::onlineMedia'],
						{
							url: value,
							targetFolder: $dragUploaderFrame.data('data-target-folder')
						},
						function(data) {
							if (data.file) {
								inline.delayedImportElement(
									$dragUploaderFrame.data('file-irre-object'),
									'sys_file',
									data.file,
									'file'
								);
							} else {
								alert("ERROR" + (data.error ? ": " + data.error : ''));
							}
						}
					);
				}
			}
			var $input = $('<input type="name"/>').attr('placeholder', 'Paste media link here...').appendTo($container).on('keypress', function(e){if(e.which === 13) {addOnlineMedia(); return false;}});
			$input.addClass('form-control');
			$input.wrap('<div class="input-group"/>');
			$('<span />').addClass('t3-button input-group-addon btn btn-default').text('Add').insertAfter($input).on('click', addOnlineMedia);
		}
	});

});