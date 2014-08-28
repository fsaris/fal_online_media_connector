define('TYPO3/CMS/FalOnlineMediaConnector/DragUploader', ['TYPO3/CMS/Backend/DragUploader', 'jquery'], function(DragUploader, $) {
	$('.t3-drag-uploader').each(function(key, DragUploaderFrame) {
		var $dragUploaderFrame = $(DragUploaderFrame);
		if ($dragUploaderFrame.data('file-irre-object')) {
			var $container = $('<div />').addClass('online-media').css({display: 'inline-block'}).insertAfter(DragUploaderFrame);
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
			$('<label />').text('YouTube/Vimeo link: ').appendTo($container);
			var $input = $('<input type="name"/>').appendTo($container).on('keypress', function(e){if(e.which === 13) {addOnlineMedia(); return false;}});
			$('<span />').addClass('t3-button').text('Add').appendTo($container).on('click', addOnlineMedia);
		}
	});

});